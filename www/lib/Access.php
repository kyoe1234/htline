<?php
require_once DIR_LIB.'/Password.php';
/**
 * @brief 사용자의 로그인/로그아웃 기능과 접속 데이터를 제공한다.
 */
class Access {
    /**
     * @brief 로그인을 한다.
     * @param $email_or_loginid string 로그인 아이디
     * @param $loginpw string 로그인비밀번호
     * @param $keep_email_or_loginid boolean 아이디 기억여부
     * @param $warning object Warning 객체 참조
     * @return boolean 성공여부
     */
    public static function login($email_or_loginid, $loginpw, $keep_email_or_loginid = false, &$warning = null) {
        global $g;
		
        if ( !$email_or_loginid ) {
            return Warning::make($warning, false, 'email_or_loginid', '이메일/아이디를 입력해주세요.');
        }
		
        if ( !$loginpw ) {
            return Warning::make($warning, false, 'loginpw', '비밀번호를 입력해주세요.');
        }
		
        ## 인증확인 ##
        $user_id = Password::auth($email_or_loginid, $loginpw);
        if ( !$user_id ) {
            return Warning::make($warning, false, 'auth', '이메일/아이디 또는 비밀번호를 잘못 입력 하셨습니다.');
        }
		
        ## 세션생성 ##
        $_SESSION['user_id'] = $user_id;
		
        ## 쿠키에 아이디 기억 ##
        /*
        header(P3P);
        if ( $keep_email_or_loginid ) {
            setcookie('email_or_loginid', $email_or_loginid, time()+3600*24*365, '/', COOKIE_DOMAIN);
        } else {
            setcookie('email_or_loginid', '', 0, '/', COOKIE_DOMAIN);
        }
        */

        return Warning::make($warning, true);
    }

    /**
     * @brief 로그아웃을 한다.
     */
    public static function logout() {
        session_destroy();
    }

    /**
     * @brief 로그인된 사용자를 반환한다.
     * @return object User
     */
    public static function login_user() {
        global $g;

        $user_id = $_SESSION['user_id'];
		
        if ( $user_id ) {
            $user = $user_data = $g->db->fetch_row("SELECT * FROM user WHERE id = '{$user_id}'");
        } else {
            $user = array();
        }

        return $user;
    }

    /**
     * @brief 생성자
     */
    function __construct() {
        if ( !isset($_SESSION) ) {
            session_start();
        }
    }
}
?>
