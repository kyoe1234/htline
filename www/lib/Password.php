<?php
/**
 * @brief 사용자의 비밀번호 관련 기능 제공
 */
class Password {
    /**
     * @brief 암호의 보안레벨을 반환한다.
     * @details 레벨은 0~3이며 숫자가 높을수록 보안이 뛰어남.\n
     * 보안레벨 0은 사용하지 않는 것이 좋다.
     * @param $password string 암호
     * @param $point_data array 계산된 점수 정보
     * @return int 보안레벨
     */
    public static function level_test($password, &$point_data = null) {
        $point_data = array();

        ## 문자열 분석 및 점수계산 ##
        $char_point = 0; // 문자 점수
        $change_point = 0; // 변환 점수

        $unique_char = array(); // 고유문자
        $old_char = $password[0]; // 이전문자(시작은 첫문자와 똑같게)
        for ( $i = 0; $i < strlen($password); $i++ ) {
            $char = $password[$i];

            // 고유문자
            $unique_char[$char] = 0;

            // 문자 점수(6 ~ 16자 기준): 6 ~ 7.2
            if ( preg_match('/[0-9]/', $char) ) {
                $char_point += 1;
            } else if ( preg_match('/[a-zA-Z]/', $char) ) {
                $char_point += 1.1;
            } else if ( preg_match('/[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/', $char) ) {
                $char_point += 1.2;
            } else {
                return 0;
            }

            // 변환 점수(6 ~ 16자 기준): 0.05 ~ 1.5
            $gap = ord($char) - ord($old_char);
            if ( abs($gap) > 1 ) { // 연속성 없이 변환
                $change_point += 0.1;
            } else if ( $gap < 0 ) { // 역순서로 변환
                $change_point += 0.05;
            }
            $old_char = $char;
        }

        // 문자길이 점수(6 ~ 16자 기준): 0 ~ 2
        $length_point = floor(strlen($password) / 6);

        // 고유문자 점수(16자 기준): 1 ~ 16
        $unique_point = count($unique_char);

        // 전체 점수(6 ~ 16자 기준): 0 or 0.3 ~ 345.6
        $total_point = $char_point * $change_point * $length_point * $unique_point;

        // 점수정보 참조 배열
        $point_data = array(
            'char' => $char_point,
            'change' => $change_point,
            'length' => $length_point,
            'unique' => $unique_point,
            'total' => $total_point,
        );


        ## 보안 레벨 반환 ##
        // 전체 점수를 위한 4종류 점수를 각각 '(최대값 - 최소값) / 레벨'의 값으로,
        // 4종류 점수를 각각 레벨별로 계산하여 레벨별 점수범위를 구했음
        if ( $total_point > 0 && $total_point <= 16.96 ) {
            return 1;
        } else if ( $total_point > 16.96 && $total_point <= 137.36 ) {
            return 2;
        } else if ( $total_point > 137.36 ) {
            return 3;
        } else {
            return 0;
        }
    }

    /**
     * @brief 사용가능한 비밀번호인지 여부를 반환한다.
     * @param $pw string 비밀번호
     * @param $checkpw string 확인 비밀번호
     * @param $warning object Warning 객체 참조
     * @return boolean
     */
    public static function check($pw, $checkpw, &$warning = null) {
        // 확인 비밀번호
        if ( $pw != $checkpw ) {
            return Warning::make($warning, false, 'check_pw', '두개의 비밀번호가 같지 않습니다.');
        }

        // 레벨 확인
        if ( self::level_test($pw) < 1 ) {
            return Warning::make($warning, false, 'weak_pw', '보안에 취약한 비밀번호 입니다.');
        }

        return Warning::make($warning, true);
    }

    /**
     * @brief 비밀번호를 초기화 한다.
     * @param $email string 회원 이메일
     * @return string 성공: 초기화한 비밀번호, 실패: 빈문자열
     */
    public static function init($email) {
        global $g;

        $email = trim($g->db->escape($email));

        $user_id = $g->db->fetch_val("
            SELECT id FROM user
            WHERE email = '{$email}'
        ");
        if ( !$user_id ) return '';

        // 임의 비밀번호
        $password = substr(uniqid(), 0, 8);
        // 비밀번호 암호화
        $c_password = sha1($password);

        // 비밀번호 변경
        $g->db->query("
            UPDATE user SET
                pw = '{$c_password}'
            WHERE id = {$user_id}
        ");

        return $password;
    }

    /**
     * @brief 이메일과 비밀번호 인증. 인증된 회원의 id를 반환
     * @param $email string 이메일
     * @param $pw string 비밀번호
     * @return int user.id 오류시 0반환
     */
    public static function auth($email, $pw) {
        global $g;

        $email = trim($g->db->escape($email));
        $pw = sha1($pw);

        $user_id = $g->db->fetch_val("
            SELECT id FROM user
            WHERE email = '{$email}'
                AND pw = '{$pw}'
        ");

        return (int)$user_id;
    }

    /**
     * @brief 로그인암호 변경
     * @param $user_id int user.id
     * @param $oldpw string 기존 로그인암호
     * @param $newpw string 새 로그인암호
     * @param $checkpw string 확인 암호
     * @param @param $warning object Warning 객체 참조
     * @return boolean 성공여부
     */
    public static function change($user_id, $oldpw, $newpw, $checkpw, &$warning = null) {
        global $g;

        // 회원의 계정정보
        $user = Account::data($user_id);
        if ( !$user ) {
            return Warning::make($warning, false, 'user', '로그인이 필요한 서비스 입니다.');
        }

        // 기존 비밀번호 확인
        if ( !self::auth($user['email'], $oldpw) ) {
            return Warning::make($warning, false, 'wrong_pw', '현재 비밀번호가 맞지 않습니다.');
        }

        // 새 비밀번호 확인
        $result = self::check($newpw, $checkpw, $warning);
        if ( !$result ) {
            return $warning->remake(false);
        }

        // 로그인암호 암호화
        $pw = sha1($newpw);

        // 암호변경
        $g->db->query("
            UPDATE user SET
                pw = '{$pw}'
            WHERE id = {$user_id}

        ");
        return Warning::make($warning, true);
    }

    private function __construct() {}
}
?>