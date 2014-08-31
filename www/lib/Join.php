<?php
require_once DIR_LIB.'/Account.php';
require_once DIR_LIB.'/Password.php';
//require_once DIR_LIB.'/UserPrivacy.php';

/**
 * @brief 회원가입
 */
class Join {
    /**
     * @brief 회원 가입
     * @param $email string 이메일
     * @param $pw string 비밀번호
     * @param $checkpw string 비빌번호 확인
	 * @param $nick_name string 닉네임
     * @param $warning object Warning 객체 참조
     * @return int user.id
     */
    public static function signup($email, $pw, $checkpw, $nick_name = null, &$warning = null) {
        global $g;

        $g->db->begin();

        // 계정 생성
        $user_id = Account::add($email, $nick_name, $warning);
        if ( !$user_id ) {
            $g->db->rollback();
            return $warning->remake(0);
        }

        // 비밀번호 초기화
        $tmp_pw = Password::init($email);
        if ( !$tmp_pw ) {
            $g->db->rollback();
            return Warning::make($warning, 0, 'password_init', '오류가 발생했습니다.');
        }

        // 사용자의 비밀번호로 변경
        $result = Password::change($user_id, $tmp_pw, $pw, $checkpw, $warning);
        if ( !$result ) {
            $g->db->rollback();
            return $warning->remake(0);
        }

		/*
        // 개인정보 수정 (초기화)        
        $result = UserPrivacy::init($user_id, $warning);
        if ( !$result ) {
            $g->db->rollback();
            return $warning->remake(0);
        }
		*/
		
        $g->db->commit();

        return Warning::make($warning, $user_id);
    }
}
