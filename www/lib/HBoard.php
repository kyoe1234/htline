<?php
/**
* @brief hidden board
*/
class HBoard {
	/**
	 * @brief 글을 반환한다.
	 * @param $post_id int htline.hboard.id
	 * @return array
	 */
	public static function get_post($post_id) {
		global $g;

		if ( !preg_match('/^[1-9][0-9]*$/', $post_id) ) return array();

		$sql = "SELECT * FROM htline.hboard
					WHERE id = {$post_id}";
		return $g->db->fetch_row($sql);
	}

	/**
	 * @brief 추가
	 * @param $hid string 익명을위한 해쉬코드
	 * @param $content string 글
	 * @param $warning object Warning 객체 참조
	 * @return int htline.hboard.id
	 */
	public static function add($hid, $content, &$warning = null) {
		global $g;

		if ( !$hid ) {
			return Warning::make($warning, 0, 'hid', '오류가 발생했습니다.');
		}

		if ( !preg_match('/[^\s]/', $content) ) {
			return Warning::make($warning, 0, 'content', '내용을 입력해 주세요');
		}

		$content = $g->db->escape($content);
		$g->db->query("
			INSERT htline.hboard SET
				hid = '{$hid}',
				content = '{$content}',
				blind = 'N',
				ip = '{$_SERVER['REMOTE_ADDR']}',
				modifydate = NOW(),
				createdate = NOW()");
		$insert_id = $g->db->insert_id();

		return $insert_id;
	}


	/**
	 * @brief 수정
	 * @param $post_id int htline.hboard.id
	 * @param $content string 글
	 * @param $warning object Warning 객체 참조
	 * @return boolean
	 */
	public static function modify($post_id, $content, &$warning = null) {
		global $g;

		if ( !preg_match('/^[1-9][0-9]*$/', $post_id) ) return array();

		if ( !preg_match('/[^\s]/', $content) ) {
			return Warning::make($warning, 0, 'content', '내용을 입력해 주세요');
		}

		$content = $g->db->escape($content);
		$g->db->query("
			UPDATE htline.hboard SET
				content = '{$content}',
				blind = 'N',
				ip = '{$_SERVER['REMOTE_ADDR']}',
				modifydate = NOW()
			WHERE id = '{$post_id}'");

		return Warning::make($warning, true);
	}

	/**
	 * @brief 삭제
	 * @param $post_id int htline.hboard.id
	 * @param $warning object Warning 객체 참조
	 * @return boolean
	 */
	public static function remove($post_id, $user_idx, &$warning = null) {
		global $g;

		$post = $this->get_post($post_id);
		// 삭제로 인정
		if ( !$post ) return true;

		$g->db->query("
			DELETE FROM htline.hboard
			WHERE id = '{$post_id}'");

		return Warning::make($warning, true);
	}

	/**
	 * @brief 댓글 블라인드 처리
	 * @param $hcomment_id int htline.hcomment.id
	 * @param $hid string 익명을위한 해쉬코드
	 * @param $warning object Warning 객체 참조
	 * @return boolean
	 */
	public static function set_blind($post_id, $value, &$warning = null) {
		global $g;

		$post = self::get_post($post_id);
		if ( !$post['id'] ) {
			return Warning::make($warning, false, 'not_found', '이미 지워진 글 입니다.');
		}

		if ( !$value || $value == 'n' || $value == 'N' ) {
			$value = 'N';
		} else {
			$value = 'Y';
		}

		$g->db->query("
			UPDATE htline.hboard SET
					blind = '{$value}'
			WHERE id = '{$post_id}'");

		return Warning::make($warning, true);
	}
}
?>