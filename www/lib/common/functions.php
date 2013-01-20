<?php
/**
 * @brief 쿼리문에 페이징을 위한 LIMIT 구문을 추가해 처리한 결과 레코드 반환
 * @param string 쿼리문
 * @param int 가져올 개수
 * @param int 페이지
 * @param array 페이지 정보 참조변수
 * @return array
 */
function &sql_fetch_list($sql, $limit, $page, &$page_info) {
	global $g;

	// SELECT 쿼리 전용
	if ( !preg_match('/^SELECT/i', $sql) ) return false;
	// SQL_CALC_FOUND_ROWS 추가
	$sql = preg_replace('/^SELECT/i', 'SELECT SQL_CALC_FOUND_ROWS', $sql);

	settype($limit, 'int');
	settype($page, 'int');

	if ( $page < 1 ) $page = 1;

	// limit 시작 위치
	$start = ( $page - 1 ) * $limit;

	$sql .= " LIMIT {$start}, {$limit}";

	$result = $g->db->query($sql);
	if ( !$result ) return array();

	$list = array();
	while ( $row = @mysqli_fetch_assoc($result) ) {
		$list[] = $row;
	}

	require_once DIR_LIB.'/common/PageTool.php';

	$rows_count = $g->db->found_rows();
	$page_info = PageTool::cacl($rows_count, $page, $limit, 10);

	return $list;
}

/**
 * @brief 기본적인 동작은 require와 동일.
 * @details extract($GLOBALS) 코드 실행 후 파일을 require 한다.
 * @param $path string 파일 경로
 * @param $_a mixed $path에 제공할 인수
 * @return mixed
 */
function import($path, $_a = null) {
	unset($GLOBALS['_a']);
	extract($GLOBALS);
	return require $path;
}

/**
 * @brief import()가 출력하는 내용을 버퍼에 담아 반환.
 * @see import()
 * @param $path string 파일 경로
 * @param $_a mixed $path에 제공할 인수
 * @return string
 */
function import_ob($path, $_a = null) {
	ob_start();
	import($path, $_a);
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * @brief import_ob()의 반환값을 캐쉬하고 출력
 * @see import_ob()
 * @param $path string 파일 경로
 * @param $_a mixed $path에 제공할 인수
 */
function import_cache($path, $_a = null) {
	echo $GLOBALS['g']->fncache->call('import_ob', array($path, $_a));
}

/**
 * @brief scalar, array, object의 모든 값을 trim 처리
 * @param $var mixed
 * @return mixed
 */
function atrim($var) {
	if ( is_array($var) || is_object($var) ) {
		foreach ( $var as &$v ) {
			$v = atrim($v);
		}
	} else if ( is_scalar($var) ) {
		$var = trim($var);
	}

	return $var;
}

/**
 * @brief HTTP 상태 코드 페이지를 출력한다.
 * @param $code int 상태 코드
 */
function http_response_status_code($code) {
	$code_list = array(
		404 => 'Not Found',
	);

	$message = $code_list[$code];
	if ( !$message ) exit;

	header("HTTP/1.1 {$code} {$message}");
	$g->var['layout_head_title'] = "{$code} {$message} - 위드블로그";

	$args = array(
		'content' => DIR_WEB."/module/{$code}.php",
	);
	import(DIR_WEB.'/layout/response_status_code.php', $args);
	exit;
}

/**
 * @brief WndReceiver 자바스크립트 객체의 response() 호출
 * @param Warning $warning
 */
function windowreceiver_response(Warning $warning) {
	$url_static = URL_WEB_STATIC;
	$json = str_replace("'", "\\'", $warning->json());
	$json = str_replace('"', '\"', $json);
echo <<<CODE
<script type="text/javascript" src="{$url_static}/js/common.js"></script>
<script type="text/javascript" src="{$url_static}/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript">
require_once(URL_WEB_STATIC + '/js/WndReceiver.js');
$(function() {
	// {$warning->text}
	var warning = eval('({$json})');
	wndreceiver.response(warning);
});
</script>
CODE;
	exit;
}

function time_elapsed($date, $unit) {
	if ( !$unit ) {
		$unit = array('지금막', '초전', '분전', '시간전', '일전', '주전');
	}

	$diff = time() - strtotime($date);

	$str = '';
	if ( $diff < 5 ) {
		$str = $unit[0];
	} else if ( $diff < 60 ) {
		$str = $diff.' '.$unit[1];
	} else if ( $diff < 3600 ) {
		$str = floor($diff / 60).' '. $unit[2];
	} else if ( $diff < 86400 ) {
		$str = floor($diff / 3600).' '.$unit[3];
	} else if ( $diff < 86400 * 7 ) {
		// 1주일 이내
		$str = floor($diff / 86400).' '.$unit[4];
	} else {
		$str = floor($diff / (86400 * 7)).' '.$unit[5];
	}

	return $str;
}

function autolink($str){
	$homepage_pattern = "/([^\"\'\=\>])(mms|http|HTTP|ftp|FTP|telnet|TELNET)\:\/\/(.[^ \n\<\"\']+)/";
	$str = preg_replace($homepage_pattern,"\\1<a href=\\2://\\3 target=_blank>\\2://\\3</a>", " ".$str);

	// MAIL
	//$str=eregi_replace("([\xA1-\xFEa-z0-9_-]+@[\xA1-\xFEa-z0-9-]+\.[a-z0-9-]+)"," \\1",$str);
	//$str=str_replace("mailto:","mailto:",$str);
	//$str=eregi_replace(" ([\xA1-\xFEa-z0-9_-]+@[\xA1-\xFEa-z0-9-]+\.[a-z0-9-]+)","\\1",$str);

	return $str;
}
?>