<?php
/**
 * @brief 전역 객체
 */
class GlobalObject {
	private static $instance = null;

	/**
	 * @brief 싱글톤 객체 반환
	 */
	public static function singleton() {
		if ( !$instance ) {
			$instance = new self();
		}

		return $instance;
	}

	private function __construct() {}

	/**
	 * @brief __get
	 * @param $name string
	 * @return mixed
	 */
	public function __get($name) {
		if ( $name == 'var' ) {
			return $this->var = array();
		}

		if ( $name == 'db' ) {
			require_once DIR_LIB.'/common/MySQL.php';
			return $this->db = new MySQL();
		}

		if ( $name == 'access' ) {
			require_once DIR_LIB.'/Access.php';
			return $this->access = Access::singleton();
		}

		if ( $name == 'au' ) {
			require_once DIR_LIB.'/AccessUser.php';
			return $this->au = new AccessUser();
		}
	}
}
?>