<?php
/**
 * @brief 함수에 캐쉬기능을 더해주는 클래스
 */
class FnCache {
    const MODE_NOCACHE = 0; /**< @brief 동작 모드 - 캐쉬를 안한다. */
    const MODE_READ = 1; /**< @brief 동작 모드 - 캐쉬를 읽기만 한다. */
    const MODE_WRITE = 2; /**< @brief 동작 모드 - 캐쉬를 쓰기만 한다. */
    const MODE_CACHE = 3; /**< @brief 동작 모드 - 캐쉬를 한다. */
    const EXPIRE_MIN = 1; /**< @brief 캐쉬 파기시간 - 최소 */
    const EXPIRE_MAX = 3600; /**< @brief 캐쉬 파기시간 - 최대 */
    const EXPIRE_DEFAULT = 180; /**< @brief 캐쉬 파기시간 - 기본 */

    private $mc;
    private $last_key;
    private $expire = self::EXPIRE_DEFAULT;
    private $mode = self::MODE_CACHE;
    private $read_mode = true;
    private $write_mode = true;
    private $key_prefix = '';
    private $_call_runkey = array();
    private $_call_result;

    /**
     * @brief 기본 생성자
     * @param $host string MemCache 호스트
     * @param $port int MemCache 포트번호
     */
    public function __construct($host = MEMCACHE_HOST, $port = MEMCACHE_PORT) {
        $this->mc = new MemCache();
        $this->mc->connect($host, $port);
    }

    /**
     * @brief 동작 모드를 반환한다.
     * @see MODE_NOCACHE, MODE_READ, MODE_WRITE, MODE_CACHE
     * @return int
     */
    public function get_mode() {
        return $this->mode;
    }

    /**
     * @brief 동작 모드를 설정 한다.
     * @see MODE_NOCACHE, MODE_READ, MODE_WRITE, MODE_CACHE
     * @param $mode int
     * @return boolean 성공여부
     */
    public function set_mode($mode) {
        switch ( $mode ) {
            case self::MODE_NOCACHE:
                $this->read_mode = false;
                $this->write_mode = false;
                break;
            case self::MODE_READ:
                $this->read_mode = true;
                $this->write_mode = false;
                break;
            case self::MODE_WRITE:
                $this->read_mode = false;
                $this->write_mode = true;
                break;
            case self::MODE_CACHE:
                $this->read_mode = true;
                $this->write_mode = true;
                break;
            default:
                return false;
        }

        $this->mode = $mode;

        return true;
    }

    /**
     * @brief 해시키를 만들때 첨가할 문자열을 반환한다.
     * @return string
     */
    public function get_key_prefix() {
        return $this->key_prefix;
    }

    /**
     * @brief 해시키를 만들때 첨가할 문자열을 설정한다.
     * @warning 전체 캐시를 새롭게 갱신할때 유용하다.
     * @param $key_prefix string 첨가할 문자열
     * @return boolean
     */
    public function set_key_prefix($key_prefix) {
        if ( !is_string($key_prefix) ) return false;
        $this->key_prefix = $key_prefix;
        return true;
    }

    /**
     * @brief 해시키를 만든다.
     * @details 키의 앞에 문자열을 첨가할 수 있다.
     * @see get_key_prefix(), set_key_prefix()
     * @param $function mixed 호출할 함수
     * @param $param_arr mixed 파라메터 배열
     * @return string
     */
    public function create_key($function, $param_arr = null) {
        $this->last_key = $this->key_prefix.sha1(serialize(array($function, $param_arr)));
        return $this->last_key;
    }

    /**
     * @brief 메모리 캐쉬 기본 파기시간(초)을 반환한다.
     * @return int
     */
    public function get_expire() {
        return $this->expire;
    }

    /**
     * @brief 메모리 캐쉬 기본 파기시간(초)을 설정한다.
     * @param $sec int 초
     */
    public function set_expire($sec) {
        if ( $sec < self::EXPIRE_MIN ) {
            $this->expire = self::EXPIRE_MIN;
        } else if ( $sec > self::EXPIRE_MAX ) {
            $this->expire = self::EXPIRE_MAX;
        } else {
            $this->expire = (int)$sec;
        }
    }

    /**
     * @brief 캐쉬를 적용하여 함수를 호출한다.
     * @details 함수 호출 방법은 call_user_func_array()와 같다.
     * @param $function mixed 호출할 함수
     * @param $param_arr mixed 파라메터 배열
     * @param $expire int 만기시간(초)
     * @return mixed 호출한 함수가 반환한 값
     */
    public function call($function, $param_arr = null, $expire = self::EXPIRE_DEFAULT) {
        if ( is_array($function) ) {
            if ( !method_exists($function[0], $function[1]) ) {
                trigger_error("{$function[0]}::{$function[1]} 메소드를 찾을 수 없습니다.", E_USER_ERROR);
            }
        } else {
            if ( !function_exists($function) ) {
                trigger_error("{$function} 함수를 찾을 수 없습니다.", E_USER_ERROR);
            }
        }

        if ( !$expire ) {
            $expire = $this->expire;
        } else if ( $expire < self::EXPIRE_MIN ) {
            $expire = self::EXPIRE_MIN;
        } else if ( $expire > self::EXPIRE_MAX ) {
            $expire = self::EXPIRE_MAX;
        }

        $key = $this->create_key($function, $param_arr);

        // read cache
        if ( $this->read_mode ) {
            $data = $this->mc->get($key);
            if ( $data !== false ) return $data;
        }

        $data = call_user_func_array($function, $param_arr);

        // write cache
        if ( $this->write_mode ) {
            $this->mc->set($key, $data, 0, $expire);
        }

        return $data;
    }

    /**
     * @brief 캐쉬가 적용된 함수의 캐쉬 값을 지운다.
     * @details 함수 호출 방법은 call_user_func_array()와 같다.
     * @param $function mixed 호출할 함수
     * @param $param_arr mixed 파라메터 배열
     */
    public function delete($function, $param_arr = null) {
        $key = $this->create_key($function, $param_arr);
        $this->mc->delete($key);
    }

    /**
     * @brief 함수나 메소드 내부 코드에 삽입되어 캐쉬 기능을 한다.\n
     * 캐쉬된 결과는 _call_result()를 호출하여 구한다.
     * @see _call_result()
     * @code
     * $GLOBALS['g']->fncache = new FnCache();
     *
     * function foo($bar) {
     *     # 캐쉬코드 시작 #
     *     if ( $GLOBALS['g']->fncache->_call() ) {
     *         return $GLOBALS['g']->fncache->_call_result();
     *     }
     *     # 캐쉬코드 끝 #
     *
     *     // 실제 로직과 데이터 반환
     *     return $bar + 1;
     * }
     * @endcode
     * @param $expire int 만기시간(초)
     * @return mixed 호출한 함수가 반환한 값
     */
    public function _call($expire = self::EXPIRE_DEFAULT) {
        list(, $caller) = debug_backtrace();

        if ( $caller['type'] == '->' ) {
            $function = array($caller['object'], $caller['function']);
        } else if ( $caller['type'] == '::' ) {
            $function = array($caller['class'], $caller['function']);
        } else {
            $function = $caller['function'];
        }

        $param_arr = $caller['args'];

        $key = $this->create_key($function, $param_arr);

        if ( $this->_call_runkey[$key] ) {
            return false;
        } else {
            $this->_call_runkey[$key] = true;
            $this->_call_result = $this->call($function, $param_arr, $expire);
            $this->_call_runkey[$key] = false;
            return true;
        }
    }

    /**
     * @brief _call() 메소드 호출로 인해 만들어진 캐쉬 결과를 반환한다.
     * @return mixed
     */
    public function _call_result() {
        return $this->_call_result;
    }
}
?>