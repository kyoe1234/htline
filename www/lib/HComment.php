<?php
/**
 * @brief 통합 댓글
 */
class HComment {
	const MAX_CONTENT_LEN = 500; /**< @brief 내용 최대 길이 */

	/**
	 * @brief 소유자 유효여부
	 * @param $owner string 소유자
	 * @return boolean
	 */
	private static function valid_owner($owner) {
		static $owner_list = array(
			'hboard',
		);
		return in_array($owner, $owner_list);
	}

	/**
	 * @brief 내용 유효여부
	 * @param $content string 내용
	 * @return boolean
	 */
	private static function valid_content($content) {
		$len = self::MAX_CONTENT_LEN;
		// 공백이 아닌 문자가 하나 이상 있고 개수가 맞으면
		return preg_match("/[^\s]+/su", $content)
			&& preg_match("/^.{1,{$len}}$/su", $content);
	}

	/**
	 * @brief 추가
	 * @param $owner string 소유자
	 * @param $owner_id string 소유자의 id
	 * @param $hid string 익명을위한 해쉬코드
	 * @param $content string 내용
	 * @param $warning object Warning 객체 참조
	 * @return int hcomment.id
	 */
	public static function add($owner, $owner_id, $hid, $content, &$warning = null) {
		global $g;

		if ( !self::valid_owner($owner) ) {
			return Warning::make($warning, 0, 'owner', '오류가 발생했습니다.');
		}

		if ( !preg_match('/^[1-9][0-9]*$/', $owner_id) ) {
			return Warning::make($warning, 0, 'owner_id', '오류가 발생했습니다.');
		}

		if ( !$hid ) {
			return Warning::make($warning, 0, 'hid', '오류가 발생했습니다.');
		}

		if ( !self::valid_content($content) ) {
			return Warning::make($warning, 0, 'content', '내용을 '.self::MAX_CONTENT_LEN.'자 이내로 입력해주세요.');
		}

		$g->db->begin();

		// 줄바꿈 제한
		$content = preg_replace('/\n{3,}/', "\n\n", $content);
		$content = $g->db->escape($content);

		// 추가
		$g->db->query("
			INSERT hcomment SET
				owner = '{$owner}',
				ownerid = '{$owner_id}',
				hid = '{$hid}',
				content = '{$content}',
				blind = 'N',
				ip = '{$_SERVER['REMOTE_ADDR']}',
				createdate = NOW()
		");

		// 생성된 id
		$insert_id = $g->db->insert_id();

		// rootid 변경
		$g->db->query("
			UPDATE hcomment SET
				rootid = {$insert_id}
			WHERE id = {$insert_id}
		");

		$g->db->commit();

		return Warning::make($warning, $insert_id);
	}

	/**
	 * @brief 추가
	 * @param $hcomment_id int 답글의 대상 hcomment.id
	 * @param $hid string 익명을위한 해쉬코드
	 * @param $content string 내용
	 * @param $warning object Warning 객체 참조
	 * @return int hcomment.id
	 */
	public static function reply($hcomment_id, $hid, $content, &$warning = null) {
		global $g;

		$hcomment = new self($hcomment_id);
		if ( !$hcomment->id ) {
			return Warning::make($warning, 0, 'not_found', '지워진 댓글에는 답글을 달 수 없습니다.');
		}

		if ( !$hid ) {
			return Warning::make($warning, 0, 'hid', '오류가 발생했습니다.');
		}

		if ( !self::valid_content($content) ) {
			return Warning::make($warning, 0, 'content', '내용을 '.self::MAX_CONTENT_LEN.'자 이내로 입력해주세요.');
		}

		$g->db->begin();

		// 줄바꿈 제한
		$content = preg_replace('/\n{3,}/', "\n\n", $content);
		$content = $g->db->escape($content);

		// 추가
		$g->db->query("
			INSERT hcomment SET
				rootid = '{$hcomment_id}',
				owner = '{$hcomment->owner}',
				ownerid = '{$hcomment->ownerid}',
				hid = '{$hid}',
				content = '{$content}',
				blind = 'N',
				ip = '{$_SERVER['REMOTE_ADDR']}',
				createdate = NOW()
		");

		// 생성된 id
		$insert_id = $g->db->insert_id();
		$g->db->commit();

		return Warning::make($warning, $insert_id);
	}

	/**
	 * @brief 삭제
	 * @param $hcomment_id int hcomment.id
	 * @param $hid string 익명을위한 해쉬코드
	 * @param $warning object Warning 객체 참조
	 * @return boolean
	 */
	public static function remove($hcomment_id, $hid, &$warning = null) {
		global $g;

		$hcomment = new self($hcomment_id);
		if ( !$hcomment->id ) {
			return Warning::make($warning, false, 'not_found', '이미 지워진 댓글 입니다.');
		}

		/*
		// 작성자 본인 확인
		if ( $hcomment->hid != $hid ) {
			return Warning::make($warning, false, 'invalid_user', '작성자 본인이 아닙니다.');
		}
		*/

		// 삭제할 id에 해당하는 rootid 개수를 구한다.
		$sql = "SELECT COUNT(*) FROM hcomment
				WHERE rootid = {$hcomment_id}";
		$count = $g->db->fetch_val($sql);

		$g->db->begin();

		// 개수가 1이하면 삭제
		if ( $count <= 1 ) {
			$sql = "DELETE FROM hcomment
					WHERE id = {$hcomment_id}";
		} else {
			$sql = "UPDATE hcomment SET
						hid = NULL,
						content = NULL,
						createdate = NULL
					WHERE id = {$hcomment_id}";
		}

		$g->db->query($sql);

		// 자식일 경우
		if ( $hcomment->id != $hcomment->rootid ) {
			$rootcomment = new self($hcomment->rootid);
			// 삭제된 부모라면
			if ( !$rootcomment->hid ) {
				// 삭제 재호출
				$result = self::remove($rootcomment->id, $rootcomment->hid, $warning);
				if ( !$result ) {
					$g->db->rollback();
					return $warning->remake(false);
				}
			}
		}

		$g->db->commit();

		return Warning::make($warning, true);
	}

	/**
	 * @brief 댓글 개수를 반환한다.
	 * @param $owner string 소유자
	 * @param $owner_id string 소유자의 id
	 * @return int
	 */
	public static function totalcount($owner, $owner_id) {
		global $g;

		if ( !self::valid_owner($owner) ) {
			return 0;
		}

		$sql = "SELECT COUNT(*) FROM hcomment
				WHERE owner = '{$owner}'
					AND ownerid = {$owner_id}";
		return (int)$g->db->fetch_val($sql);
	}

	/**
	 * @brief 댓글 블라인드 처리
	 * @param $hcomment_id int hcomment.id
	 * @param $hid string 익명을위한 해쉬코드
	 * @param $warning object Warning 객체 참조
	 * @return boolean
	 */
	public static function set_blind($hcomment_id, $hid, $value, &$warning = null) {
		global $g;

		$hcomment = new self($hcomment_id);
		if ( !$hcomment->id ) {
			return Warning::make($warning, false, 'not_found', '이미 지워진 댓글 입니다.');
		}

		/*
		// 작성자 본인 확인
		if ( $hcomment->hid != $hid ) {
			return Warning::make($warning, false, 'invalid_user', '작성자 본인이 아닙니다.');
		}
		*/

		if ( !$value || $value == 'n' || $value == 'N' ) {
			$value = 'N';
		} else {
			$value = 'Y';
		}

		$g->db->query("
			UPDATE hcomment SET
					blind = '{$value}'
				WHERE id = '{$hcomment_id}'
		");

		return Warning::make($warning, true);
	}


	/**
	 * @brief 생성자
	 * @param $hcomment_id int hcomment.id
	 */
	public function __construct($hcomment_id) {
		global $g;

		if ( preg_match('/^[1-9][0-9]*$/', $hcomment_id) ) {
			$row = $g->db->fetch_row("
				SELECT * FROM hcomment
				WHERE id = {$hcomment_id}
			");

			foreach ( $row as $k => $v ) {
				$this->$k = $v;
			}
		}
	}
}
?>