<?php
require_once DIR_LIB.'/Role.php';

/**
 * @brief 로그인된 회원의 정보 및 기능 제공
 */
class User extends ArrayObject {
    /**
     * @brief 생성자
     * @param $user_id int user.id
     */
    public function __construct($user_id) {
        global $g;

        if ( !preg_match('/^[1-9][0-9]*$/', $user_id) ) $user_id = 0;

        // 개인정보
        $this['id'] = 0;
        $account_data = Account::data($user_id);
        if ( $account_data ) {
            foreach ( $account_data as $key => $value ) {
                $this[$key] = $value;
            }
        }
		
		/*
        $user_privacy_data = UserPrivacy::data($user_id);
        if ( $account_data ) {
            foreach ( $user_privacy_data as $key => $value ) {
                $this[$key] = $value;
            }
        }
		*/
		
        // 권한
        $this->permit = Role::permit($this['roleid']);
    }

    /**
     * @brief __get
     * @param $name string
     * @return mixed
     */
    public function __get($name) {
        global $g;
		
		/*
        // 나이
        if ( $name == 'age' ) {
            return $this['birthyear'] ? date('Y') - $this['birthyear'] + 1 : 0;
        }

        // 연령대
        if ( $name == 'ages' ) {
            return (int)($this->age / 10) * 10;
        }
		*/
    }
}
?>
