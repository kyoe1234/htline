<?php
/**
 * @brief 롤 관련
 */
class Role {
    const ID_USER = 'USER'; /**< @brief id - 일반 */
    const ID_ADMIN = 'ADMIN'; /**< @brief id - 관리 */
    const ID_BLACK = 'BLACK'; /**< @brief id - 블랙 */
    const ID_LEAVE = 'LEAVE'; /**< @brief id - 탈퇴 */

    /**
     * @brief id 테이블 반환
     * @return array
     */
    private static function id_table() {
        static $table = array(
            'USER' => '일반',
            'ADMIN' => '관리',
            'BLACK' => '블랙',
            'LEAVE' => '탈퇴',
        );
        return $table;
    }

    /**
     * @brief 존재하는 id인지 여부를 반환한다.
     * @param $role_id string 롤id
     */
    private static function id_exists($role_id) {
        $table = self::id_table();
        return array_key_exists($role_id, $table);
    }

    /**
     * @brief id 목록을 반환한다.
     * @return array
     */
    public static function id_list() {
        $table = self::id_table();
        return array_keys($table);
    }

    /**
     * @brief id에 해당하는 라벨을 반환한다.
     * @param $role_id string 롤id
     * @return string
     */
    public static function id_label($role_id) {
        $table = self::id_table();
        return $table[$role_id];
    }

    /**
     * @brief 롤에 따른 권한을 반환한다.
     * @param $role_id string 롤id
     * @return object
     */
    public static function permit($role_id) {
        static $data = array(
            'USER' => array(
                'login' => 1,
                'admin' => 0,
            ),
            'ADMIN' => array(
                'login' => 1,
                'admin' => 1,
            ),
            'BLACK' => array(
                'login' => 1,
                'admin' => 0,
            ),
            'LEAVE' => array(
                'login' => 0,
                'admin' => 0,
            ),
            'ADVERTISER' => array(
                'login' => 1,
                'admin' => 0,
            ),
        );

        if ( key_exists($role_id, $data) ) {
            return $data[$role_id];
        } else {
            return $data['LEAVE'];
        }
    }

    /**
     * @brief 회원의 롤을 적용한다. 관리자롤은 적용/변경 모두 불가능
     * @param $user_id int user.id
     * @param $role_id string 롤id
     * @return boolean
     */
    public static function apply($user_id, $role_id) {
        global $g;

        if ( !preg_match('/^[1-9][0-9]*$/', $user_id) ) return false;
        if ( !self::id_exists($role_id)
            || $role_id == self::ID_ADMIN ) return false;

        $g->db->query("
            UPDATE user SET
                roleid = '{$role_id}'
            WHERE id = {$user_id}
        ");

        return true;
    }

    private function __construct() {}
}
?>
