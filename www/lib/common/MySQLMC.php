<?php
require_once DIR_LIB.'/common/MySQL.php';
require_once DIR_LIB.'/common/Action.php';

/**
 * @brief MySQL 클래스를 상속받아 캐쉬 기능을 추가한 클래스
 */
class MySQLMC extends MySQL {
    const MC_MODE_NOCACHE = 0; /**< @brief 동작 모드 - 캐쉬를 안한다. */
    const MC_MODE_READ = 1; /**< @brief 동작 모드 - 캐쉬를 읽기만 한다. */
    const MC_MODE_WRITE = 2; /**< @brief 동작 모드 - 캐쉬를 쓰기만 한다. */
    const MC_MODE_CACHE = 3; /**< @brief 동작 모드 - 캐쉬를 한다. */
    const MC_EXPIRE_MIN = 1; /**< @brief 캐쉬 파기시간 - 최소 */
    const MC_EXPIRE_MAX = 3600; /**< @brief 캐쉬 파기시간 - 최대 */
    const MC_EXPIRE_DEFAULT = 180; /**< @brief 캐쉬 파기시간 - 기본 */

    private $mc;
    private $mc_expire = self::MC_EXPIRE_DEFAULT;
    private $mc_key_prefix = '';
    private $mc_key_transaction = array();
    private $mode = self::MC_MODE_CACHE;
    private $permit_read = true;
    private $permit_write = true;
    private $tablekey;
    private $changed_info = array();

    /**
     * @brief 기본 생성자
     * @param $host string 호스트
     * @param $user string 사용자
     * @param $pw string 비밀번호
     * @param $db string 데이터베이스 이름
     * @param $port int 포트 번호
     * @param $socket string 소켓
     * @param $mc_host string MemCache 호스트
     * @param $mc_port int MemCache 포트번호
     */
    public function __construct($host = MYSQL_HOST, $user = MYSQL_USER,
            $pw = MYSQL_PW, $db = MYSQL_DB, $port = MYSQL_PORT, $socket = MYSQL_SOCKET,
            $mc_host = MEMCACHE_HOST, $mc_port = MEMCACHE_PORT) {
        parent::__construct($host, $user, $pw, $db, $port, $socket);

        $this->mc = new MemCache();
        $this->mc->connect($mc_host, $mc_port);
    }

    /**
     * @brief 배열을 "키 = 값" 문자열로 만들어 연결하여 반환한다.
     * @param $data array 데이터
     * @param $glue string 연결 문자
     * @param $wrap_quot boolean 값을 따옴표로 둘러쌀지 여부
     * @return string
     */
    private function make_key_value_string($data, $glue, $wrap_quot = false) {
        if ( !is_array($data) ) return '';

        $code = array();
        foreach ( $data as $field => $value ) {
            if ( is_null($value) ) {
                $value = 'NULL';
            } else if ( ($wrap_quot && is_string($value)) || $value === '' ) {
                $value = $this->escape($value);
                $value = "'{$value}'";
            }
            $code[] = "`{$field}` = {$value}";
        }
        return implode($glue, $code);
    }

    /**
     * @brief 마지막 테이블 변경정보 설정
     * @details 변경정보 설정 후 mysqlmc_change 액션 실행
     * @param $type string {insert|update|delete}
     * @param $table string 테이블
     * @param $row array 레코드
     */
    private function set_changed_info($type, $table, $row) {
        $this->changed_info = array(
            'type' => $type,
            'table' => $table,
            'row' => $row,
        );
        // 액션
        Action::exec('mysqlmc_change');
    }

    /**
     * @brief 마지막 테이블 변경정보 반환
     * @return array
     */
    public function changed_info() {
        return $this->changed_info;
    }

    /**
     * @brief 테이블과 키 유효성
     * @param $table string 테이블
     * @param $key array 키
     * @return string
     */
    private function check_table_key($table, $key) {
        if ( !key_exists($table, $this->tablekey) ) return '';

        foreach ( $this->tablekey[$table] as $key_type => $key_list ) {
            if ( !preg_match('/^(unique|index)$/', $key_type) ) continue;

            foreach ( $key_list as $i => $k ) {
                if ( $key == $k ) {
                    return $key_type;
                }
            }
        }

        return '';
    }

    /**
     * @brief 테이블과 키값 유효성
     * @param $table string 테이블
     * @param $keyval array 키값
     * @return int 0:무효키, 1:고유키, 2:기본키
     */
    private function check_table_keyval($table, $keyval) {
        $key = array_keys($keyval);
        return $this->check_table_key($table, $key);
    }

    /**
     * @brief 메모리 캐쉬에 쓰일 키를 생성한다.
     * @param $table string 테이블명
     * @param $keyval array 키값
     * @return string
     */
    private function create_mc_key($table, $keyval) {
        // 숫자일 경우 1과 '1'은 키값이 달라지므로 무조건 문자열로 변환
        foreach ( $keyval as &$rkeyval ) {
            $rkeyval = (string)$rkeyval;
        }
        return $this->mc_key_prefix.sha1(serialize(array($table, $keyval)));
    }

    /**
     * @brief 데이터를 기반으로 테이블의 모든 고유(기본)키의 캐쉬키 목록을 반환한다.
     * @param $table string 테이블명
     * @param $data array 데이터
     * @param $select_key_type string [unique|index]
     * @return array
     */
    private function create_mc_key_list($table, $data, $select_key_type = '') {
        $mc_key_list = array();

        foreach ( $this->tablekey[$table] as $key_type => $key_list ) {
            if ( $select_key_type ) {
                if ( $select_key_type != $key_type ) continue;
            }

            foreach ( $key_list as $key ) {
                $keyval = array();
                foreach ( $key as $column ) {
                    if ( !array_key_exists($column, $data) ) {
                        trigger_error("{$table}.(".implode(',', $key).')의 캐쉬키 생성 오류', E_USER_ERROR);
                    }

                    $keyval[$column] = $data[$column];
                }

                $mc_key_list[] = $this->create_mc_key($table, $keyval);
            }
        }

        return $mc_key_list;
    }

    /**
     * @brief 캐쉬된 데이터를 반환한다.\n
     * 데이터가 없을 경우 false 반환
     * @param $table string 테이블명
     * @param $keyval array 키값
     * @return mixed
     */
    private function get_cache_data($table, $keyval) {
        $key = array_keys($keyval);
        $key_type = $this->check_table_key($table, $key);
        if ( !$key_type ) {
            trigger_error("{$table}.(".implode(',', $key).')는 등록되지 않은 키입니다.', E_USER_ERROR);
        }

        // auto-commit 상태가 아니면 무조건 false
        if ( !$this->is_autocommit() ) return false;

        $mc_key = $this->create_mc_key($table, $keyval);
        $data = $this->mc->get($mc_key);

        // 고유키면서 값이 배열이 아니라면 참조용 캐쉬키이므로 참조값을 가져온다.
        if ( $key_type == 'unique' && $data !== false && !is_array($data) ) {
            $data = $this->mc->get($data);
            // 참조 데이터가 없으면 캐쉬제거
            if ( $data === false ) {
                $this->mc->delete($mc_key);
            }
        }

        return $data;
    }

    /**
     * @brief 테이블키 구조를 설정한다.
     * @code
     * // 구조 코드 예제
     * $tablekey = array(
     *     '테이블1' => array(
     *         'unique' => array(
     *             array('컬럼1'),
     *         ),
     *         'index' => array(
     *             array('컬럼2'),
     *             array('컬럼3', '컬럼4'),
     *         ),
     *     ),
     * );
     * @endcode
     * @param $tablekey array 테이블키 구조
     */
    public function set_tablekey($tablekey) {
        $this->tablekey = $tablekey;
    }

    /**
     * @brief 동작 모드를 반환한다.
     * @see MC_MODE_NOCACHE, MC_MODE_READ, MC_MODE_WRITE, MC_MODE_CACHE
     * @return int
     */
    public function get_mc_mode() {
        return $this->mc_mode;
    }

    /**
     * @brief 동작 모드를 설정 한다.
     * @see MC_MODE_NOCACHE, MC_MODE_READ, MC_MODE_WRITE, MC_MODE_CACHE
     * @param $mode int
     * @return boolean 성공여부
     */
    public function set_mc_mode($mode) {
        switch ( $mode ) {
            case self::MC_MODE_NOCACHE:
                $this->permit_read = false;
                $this->permit_write = false;
                break;
            case self::MC_MODE_READ:
                $this->permit_read = true;
                $this->permit_write = false;
                break;
            case self::MC_MODE_WRITE:
                $this->permit_read = false;
                $this->permit_write = true;
                break;
            case self::MC_MODE_CACHE:
                $this->permit_read = true;
                $this->permit_write = true;
                break;
            default:
                return false;
        }

        $this->mc_mode = $mode;

        return true;
    }

    /**
     * @brief 메모리 캐쉬 기본 파기시간(초)을 반환한다.
     * @return int
     */
    public function get_mc_expire() {
        return $this->mc_expire;
    }

    /**
     * @brief 메모리 캐쉬 기본 파기시간(초)을 설정한다.
     * @param $sec int 초
     */
    public function set_mc_expire($sec) {
        if ( $sec < self::MC_EXPIRE_MIN ) {
            $this->mc_expire = self::MC_EXPIRE_MIN;
        } else if ( $sec > self::MC_EXPIRE_MAX ) {
            $this->mc_expire = self::MC_EXPIRE_MAX;
        } else {
            $this->mc_expire = (int)$sec;
        }
    }

    /**
     * @brief 메모리 캐쉬키를 만들때 앞에 첨가할 문자열을 반환한다.
     * @return string
     */
    public function get_mc_key_prefix() {
        return $this->mc_key_prefix;
    }

    /**
     * @brief 메모리 캐쉬키를 만들때 앞에 첨가할 문자열을 설정한다.
     * @warning 전체 캐쉬를 새롭게 갱신할때 유용하다.
     * @param $mc_key_prefix string 첨가할 문자열
     * @return boolean
     */
    public function set_mc_key_prefix($mc_key_prefix) {
        if ( !is_string($mc_key_prefix) ) return false;
        $this->mc_key_prefix = $mc_key_prefix;
        return true;
    }

    /**
     * @brief 캐쉬된 DB 테이블 레코드의 캐쉬를 지운다.
     * @param $table string 테이블명
     * @param $keyval array 키값
     */
    private function uncache_row($table, $data) {
        # 이 메소드는 {insert|update|delete}_row() 메소드와 함께 호출된다.
        # 즉, DB 내용의 변경이 일어나 기존 캐쉬를 제거하기 위함이다.
        #
        # auto-commit 상태가 아닌 것은 트랜젝션 진행중이며 데이터의 수정이 확정된 것이 아니다.
        # 이때 캐쉬를 건드리면 불확정된 데이터가 다른 접속자에게도 영향을 미치기 때문이다.
        # 그래서 트랙젝션 진행중 일때는 캐쉬를 읽거나 변경하는 행위를 중단한다.
        #
        # 하지만 다른 접속자에 의해서 캐쉬는 계속 변경된다.
        # 그래서 트랜젝션을 진행하는 동안에 호출된 캐쉬키를 기록해 놓고,
        # 커밋 또는 롤백이 호출되어 트랜젝션이 끝나면 모아놓은 캐쉬키를 일괄 제거한다.

        // 데이터를 기반으로 캐쉬키목록을 구한다.
        $mc_key_list = $this->create_mc_key_list($table, $data);

        // auto-commit 여부
        $autocommit = $this->is_autocommit();

        // 모든 캐쉬 제거
        foreach ( $mc_key_list as $mc_key ) {
            if ( $autocommit ) {
                $this->mc->delete($mc_key);
            } else {
                // 캐쉬키를 해쉬키로 중복안되게 기록
                $this->mc_key_transaction[$mc_key] = null;
            }
        }
    }

    /**
     * @brief DB 테이블에 레코드를 삽입하고 캐쉬를 지운다.
     * @details mysqlmc_change 액션 실행.
     * @see changed_info()
     * @param $table string 테이블명
     * @param $keyval array 키값: 자동증가키 array('id' => null), 직접지정 array('nick' => 'foo')
     * @param $data array 데이터: array('age' => '20', nick => 'foo')
     * @param $expr array 표현식: array('date' => 'NOW()', 'hit' => 'hit + 1')
     */
    public function insert_row($table, $keyval, $data = null, $expr = null) {
        $key = array_keys($keyval);
        $key_type = $this->check_table_key($table, $key);
        if ( !$key_type ) {
            trigger_error("{$table}.(".implode(',', $key).')는 등록되지 않은 키입니다.', E_USER_ERROR);
        }


        ## 데이터 INSERT ##
        $set_code = array();
        if ( count($keyval) > 1 || !is_null(reset($keyval)) ) {
            if ( is_array($data) ) {
                $data = array_merge($data, $keyval);
            } else {
                $data = $keyval;
            }
        }
        if ( $data ) $set_code[] = $this->make_key_value_string($data, ',', true);
        if ( $expr ) $set_code[] = $this->make_key_value_string($expr, ',', false);
        $set_code = implode(',', $set_code);

        try {
            $this->query("
                INSERT {$table} SET
                    {$set_code}
            ");
        } catch ( Exception $e ) {
            throw $e;
        }


        ## 삽입한 레코드 가져오기 ##
        // 값이 null이면 auto_increment로 인식
        foreach ( $keyval as &$rkeyval ) {
            if ( is_null($rkeyval) ) {
                $rkeyval = $this->insert_id();
                break;
            }
        }

        $where_code = $this->make_key_value_string($keyval, ' AND ', true);
        try {
            $insert_row = $this->fetch_row("
                SELECT * FROM {$table}
                WHERE {$where_code}
                LIMIT 1
            ");
        } catch ( Exception $e ) {
            throw $e;
        }


        ## 캐쉬 제거 ##
        // 신규 데이터의 값이 이미 캐쉬되어 있을 수 있으므로 캐쉬 제거
        $this->uncache_row($table, $insert_row);


        ## 변경정보 설정 ##
        $this->set_changed_info('insert', $table, $insert_row);
    }

    /**
     * @brief DB 테이블 레코드를 갱신하고 캐쉬를 지운다.
     * @details mysqlmc_change 액션 실행.
     * @see changed_info()
     * @param $table string 테이블명
     * @param $keyval array 키값
     * @param $data array 데이터: array('age' => '20', nick => 'foo')
     * @param $expr array 표현식: array('date' => 'NOW()', 'hit' => 'hit + 1')
     */
    public function update_row($table, $keyval, $data, $expr = null) {
        $key = array_keys($keyval);
        $key_type = $this->check_table_key($table, $key);
        if ( !$key_type ) {
            trigger_error("{$table}.(".implode(',', $key).')는 등록되지 않은 키입니다.', E_USER_ERROR);
        }

        if ( !$data && !$expr ) return;

        // 공통 WHERE문
        $where_code = $this->make_key_value_string($keyval, ' AND ', true);


        ## 이전 데이터 ##
        try {
            $old_data = $this->fetch_row("
                SELECT * FROM {$table}
                WHERE {$where_code}
                LIMIT 1
            ");
        } catch ( Exception $e ) {
            throw $e;
        }
        // 이전 데이터가 없으면 더이상 진행할 필요가 없음
        if ( !$old_data ) return;


        ## DB 업데이트 ##
        $set_code = array();
        if ( $data ) $set_code[] = $this->make_key_value_string($data, ',', true);
        if ( $expr ) $set_code[] = $this->make_key_value_string($expr, ',', false);
        $set_code = implode(',', $set_code);

        try {
            $this->query("
                UPDATE {$table} SET
                    {$set_code}
                WHERE {$where_code}
            ");
        } catch ( Exception $e ) {
            throw $e;
        }


        ## 신규 데이터 ##
        try {
            $new_data = $this->fetch_row("
                SELECT * FROM {$table}
                WHERE {$where_code}
                LIMIT 1
            ");
        } catch ( Exception $e ) {
            throw $e;
        }


        ## 캐쉬 지우기 ##
        // 이전 데이터를 기반으로 지우기
        $this->uncache_row($table, $old_data);
        // 신규 데이터를 기반으로 지우기
        $this->uncache_row($table, $new_data);


        ## 변경정보 설정 ##
        $this->set_changed_info('update', $table, $old_data);
    }

    /**
     * @brief DB 테이블 레코드를 삭제하고 캐쉬를 지운다.
     * @details mysqlmc_change 액션 실행.
     * @see changed_info()
     * @param $table string 테이블명
     * @param $keyval array 키값
     */
    public function delete_row($table, $keyval) {
        $where_code = $this->make_key_value_string($keyval, ' AND ', true);

        ## 삭제전 데이터 ##
        try {
            $data = $this->fetch_row("
                SELECT * FROM {$table}
                WHERE {$where_code}
                LIMIT 1
            ");
        } catch ( Exception $e ) {
            throw $e;
        }
        if ( !$data ) return;


        ## 삭제 ##
        try {
            $this->query("
                DELETE FROM {$table}
                WHERE {$where_code}
            ");
        } catch ( Exception $e ) {
            throw $e;
        }


        ## 캐쉬 지우기 ##
        $this->uncache_row($table, $data);


        ## 변경정보 설정 ##
        $this->set_changed_info('delete', $table, $data);
    }

    /**
     * @brief DB 테이블 레코드를 읽어들여 캐쉬하고 반환한다.
     * @param $table string 테이블명
     * @param $keyval array 키값
     * @param $expire int 캐쉬 파기시간
     * @return array
     */
    public function select_rows($table, $keyval, $expire = 0) {
        $key = array_keys($keyval);
        $key_type = $this->check_table_key($table, $key);
        if ( !$key_type ) {
            trigger_error("{$table}.(".implode(',', $key).')는 등록되지 않은 키입니다.', E_USER_ERROR);
        }

        if ( !$expire ) {
            $expire = $this->mc_expire;
        } else if ( $expire < self::MC_EXPIRE_MIN ) {
            $expire = self::MC_EXPIRE_MIN;
        } else if ( $expire > self::MC_EXPIRE_MAX ) {
            $expire = self::MC_EXPIRE_MAX;
        }


        # read cache #
        if ( $this->permit_read ) {
            // 캐쉬된 데이터
            $data = $this->get_cache_data($table, $keyval);

            // 캐쉬가 있다면
            if ( $data !== false ) {
                return $data;
            }
        }


        # select db #
        $where_code = $this->make_key_value_string($keyval, ' AND ', true);
        try {
            $row_list = $this->fetch_all("
                SELECT * FROM {$table}
                WHERE {$where_code}
            ");
        } catch ( Exception $e ) {
            throw $e;
        }


        # write cache #
        if ( $this->permit_write && $this->is_autocommit() ) {
            if ( $key_type == 'unique' && $row_list ) {
                # 고유키들은 단일 레코드이기 때문에 메모리 캐쉬에 중복되어 저장되지 않도록
                # 첫번째 고유키의 캐쉬키를 나머지 고유키들이 참조하도록 한다.

                // 고유키의 단일 레코드를 기반으로 캐쉬키목록을 구한다.
                $mc_key_list = $this->create_mc_key_list($table, $row_list[0], 'unique');

                // 첫번째 키는 기본키의 캐쉬키
                $pk_mc_key = array_shift($mc_key_list);
                $this->mc->set($pk_mc_key, $row_list, 0, $expire);

                // 나머지는 기본키의 캐쉬키를 참조
                foreach ( $mc_key_list as $mc_key ) {
                    $this->mc->set($mc_key, $pk_mc_key, 0, $expire);
                }
            } else {
                $mc_key = $this->create_mc_key($table, $keyval);
                $this->mc->set($mc_key, $row_list, 0, $expire);
            }
        }

        return $row_list;
    }

    /**
     * @brief DB 테이블 레코드를 읽어들여 캐쉬하고 반환한다.
     * @param $table string 테이블명
     * @param $keyval array 키값
     * @param $expire int 캐쉬 파기시간
     * @return array
     */
    public function select_row($table, $keyval, $expire = 0) {
        $row_list = $this->select_rows($table, $keyval, $expire);
        return $row_list ? array_shift($row_list) : $row_list;
    }

    /**
     * @brief 키에 해당하는 데이터가 없으면 insert_row(), 있으면 update_row()가 실행된다.
     * @param $table string 테이블명
     * @param $keyval array 키값: 자동증가키 array('id' => null), 직접지정 array('nick' => 'foo')
     * @param $data array 데이터: array('age' => '20', nick => 'foo')
     * @param $expr array 표현식: array('date' => 'NOW()', 'hit' => 'hit + 1')
     */
    public function replace_row($table, $keyval, $data = null, $expr = null) {
        try {
            $row = $this->select_row($table, $keyval);

            if ( $row ) {
                $this->update_row($table, $keyval, $data, $expr);
            } else {
                $this->insert_row($table, $keyval, $data, $expr);
            }
        } catch ( Exception $e ) {
            throw $e;
        }
    }

    /**
     * @brief 트랜젝션이 진행중인 동안 호출된 캐쉬키에 대한 처리
     */
    private function clear_mc_key_transaction() {
        // 트랜젝션에 등록된 캐쉬키 제거
        $mc_key_list = array_keys($this->mc_key_transaction);
        foreach ( $mc_key_list as $mc_key ) {
            $this->mc->delete($mc_key);
        }

        // 캐쉬키 트랜젝션 기록 비우기
        $this->mc_key_transaction = array();
    }

    /**
     * @brief 트랜잭션 커밋
     */
    public function commit() {
        parent::commit();
        self::clear_mc_key_transaction();
    }

    /**
     * @brief 트랜잭션 롤백
     */
    public function rollback() {
        parent::rollback();
        self::clear_mc_key_transaction();
    }
}
?>