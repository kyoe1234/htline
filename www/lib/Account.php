<?php
/**
 * @brief 회원 계정
 */
class Account {
    /**
     * @brief 예약된 단어 목록을 반환한다.
     * @return array
     */
    public static function reserved_word_list() {
        return array(
            'admin', 'administrator',
            'hoitalk', '운영자', '관리자',
        );
    }

    /**
     * @brief 로그인아이디 또는 별명이 예약된 단어인지 여부를 반환한다.
     * @details reserved_word_list()가 반환하는 목록에 포함되면 true를 반환한다.
     * @see reserved_word_list()
     * @param $word string 단어
     * @return boolean
     */
    public static function is_reserved_word($word) {
        $word = strtolower($word);
        if ( self::valid_email($word) ) {
            $word = explode('@', $word);
            $word = $word[0];
        }

        return in_array($word, self::reserved_word_list());
    }

    /**
     * @brief 사용자 id의 존재 여부를 반환한다.
     * @param $user_id int user.id
     * @return boolean
     */
    public static function exists($user_id) {
        return self::data($user_id) == true;
    }

    /**
     * @brief 이메일의 존재 여부를 반환한다.
     * @param $email string 이메일
     * @return boolean
     */
    public static function email_exists($email) {
        global $g;
		
        $result = $g->db->fetch_val("
            SELECT email FROM user
            WHERE email = '{$email}'
        ");
		print_r($result);
        return $result ? true : false;
    }

    /**
     * @brief 이메일의 유효성 여부를 반환한다.
     * @param $email string 이메일
     * @return boolean
     */
    public static function valid_email($email) {
        $email_regexp = "/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/";
        return preg_match($email_regexp, $email) == true;
    }

    /**
     * @brief 사용가능한 이메일인지 여부 확인
     * @param $email string 이메일
     * @param $user_id int user.id
     * @param $warning object Warning 객체 참조
     * @return boolean
     */
    public static function check_email($email, $user_id = 0, &$warning = null) {
        // 이메일 유효성 여부
        if ( !self::valid_email($email) ) {
            return Warning::make($warning, false, 'invalid_email', '형식에 맞지 않는 이메일 입니다.');
        }
		
        $data = self::data($user_id);
        if ( strcasecmp($email, $data['email']) ) {
            // 이메일 중복 확인
            if ( self::email_exists($email) ) {
                return Warning::make($warning, false, 'duplicate_email', '이미 사용중인 이메일 입니다.');
            }
        }
		
        return Warning::make($warning, true);
    }

    /**
     * @brief 계정 데이터를 반환한다.
     * @param $user_id int user.id
     * @return array
     */
    public static function data($user_id) {
        global $g;

        if ( !preg_match('/^[1-9][0-9]*$/', $user_id) ) return array();

        return $g->db->fetch_row("
            SELECT * FROM user
            WHERE id = {$user_id}
        ");
    }

    /**
     * @brief 새로운 계정 추가
     * @param $email string 이메일
	 * @param $nick_name string 닉네임
     * @param $warning object Warning 객체 참조
     * @return int 추가된 계정의 user.id 오류시 0
     */
    public static function add($email, $nick_name, &$warning = null) {
        global $g;
		
        // 이메일 검사
        $result = self::check_email($email, 0, $warning);
        if ( !$result ) {
            return $warning->remake(0);
        }
		
        $g->db->query("
            INSERT user SET
                email = '{$email}',
                nickname = '{$nick_name}',
            	createdate = NOW()
        ");
		
        // 등록된 회원의 인덱스
        $user_id = (int)$g->db->insert_id();

        return Warning::make($warning, $user_id);
    }

    /**
     * @brief 계정 정보 수정
     * @param $user_id int user.id
     * @param $email string 이메일
	 * @param $nick_name string 닉네임
     * @param $warning object Warning 객체 참조
     * @return boolean 성공여부
     */
    public static function modify($user_id, $email, $nick_name = null, &$warning = null) {
        global $g;

        if ( !self::exists($user_id) ) {
            return Warning::make($warning, false, 'user', '로그인이 필요한 서비스 입니다.');
        }

        // 데이터 오류 검사
        $result = self::check_email($email, $user_id, $warning);
        if ( !$result ) {
            return $warning->remake(false);
        }

        $g->db->query("
            UPDATE user SET
                email = '{$email}'
                nickname = {$nick_name},
            WHERE id = {$user_id}
        ");

        return Warning::make($warning, true);
    }

   /**
    * @brief 계정정보를 지운다.(삭제가 아님)
    * @param $user_id int user.id
    * @return boolean
    */
    public static function erase($user_id) {
        global $g;

        if ( !preg_match('/^[1-9][0-9]*$/', $user_id) ) return false;

        $email = $g->db->fetch_val("
            SELECT email FROM user
            WHERE id = {$user_id}
        ");

        $g->db->query("
            UPDATE user SET
                email = null,
                emailleave = '{$email}',
                pw = null
            WHERE id = {$user_id}
        ");

        return true;
    }

    private function __construct() {}
}
?>