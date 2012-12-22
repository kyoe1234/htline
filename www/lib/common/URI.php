<?php
/**
 * @brief $_SERVER['QUERY_STRING'] 데이터를 기반으로 URL 생성을 도와주는 클래스
 * @author A2
 * @date 2010-07-14
 */
class URI {
    /**
     * @brief 인수로 받은 URL에 $_SERVER['QUERY_STRING'] 데이터를 연결해준다.
     * @param $url string URL
     * @param $params mixed 파라메터 이름목록 (쉼표나 띄어쓰기로 구분된 문자열 또는 배열)
     * @param $flag boolean 처리방식 (false: 파라메터 목록의 파라메터는 제외, true: 파라메터 목록의 파라메터만 포함)
     * @return string
     */
    public static function url($url = '', $params = null, $flag = false) {
        $url = self::clean($url);
        // & 일괄적용
        $url = str_replace('&amp;', '&', $url);
        // ?를 기준으로 쿼리 분리
        list($url, $query) = explode('?', $url, 2);


        // 배열이 아니면 쉼표나 띄어쓰기로 구분
        if ( !is_array($params) ) {
            settype($params, 'string');
            $params = ( $params !== '' ) ? preg_split('/[\s,]+/', $params) : array();
        }

        // $params를 제외할 경우
        if ( !$flag ) {
            // url에 중복되지 않도록 제외시킬 파라메터 추출
            preg_match_all('/([^=&]+)(?:=[^=&]+)?/', $query, $except_params);
            $except_params = $except_params[1];
            // 제외할 파라메터 추가
            $params = array_merge($params, $except_params);
        }

        preg_match('/\?(.*)$/', $_SERVER['REQUEST_URI'], $match);
        $old_query_list = explode('&', $match[1]);
        $new_query = array();
        foreach ( $old_query_list as $old_query ) {
            list($name, $value) = explode('=', $old_query, 2);
            $name = urldecode($name);

            // 빈값 검사
            if ( is_null($value) || $value === '' ) continue;

            // 포함 또는 제외 검사
            $in = in_array($name, $params);
            if ( ($flag && !$in) || (!$flag && $in) ) continue;

            $new_query[] = $old_query;
        }
        // url에 있던 쿼리는 끝에 추가
        if ( $query ) $new_query[] = $query;

        // 쿼리 연결
        $new_query = implode('&', $new_query);

        return $new_query ? $url.'?'.$new_query : $url;
    }

    /**
     * @brief self::url()과 같지만 쿼리 구분자가 &amp;이다.
     * @param $url string URL
     * @param $params mixed 파라메터 이름목록 (쉼표나 띄어쓰기로 구분된 문자열 또는 배열)
     * @param $flag boolean 처리방식 (false: 파라메터 목록의 파라메터는 제외, true: 파라메터 목록의 파라메터만 포함)
     * @return string
     */
    public static function link($url = '', $params = null, $flag = false) {
        return str_replace('&', '&amp;', self::url($url, $params, $flag));
    }

    /**
     * @brief URL을 알아서 일관성 있게 정리해준다.
     * 예) &|&amp; 구분자 통일, foo.php?&v=1 => foo.php?v=1
     * @param $url string URL
     * @return string
     */
    public static function clean($url) {
        settype($url, 'string');

        // &amp; 확인
        $amp = strpos($url, '&amp;') !== false;

        // ?와 &amp;를 &로 일괄 변환
        $url = preg_replace('/(?:\?|&amp;)/', '&', $url);
        // & 연속 제거
        $url = preg_replace('/(?:(&)+)/', '\1', $url);
        // 첫번째 &는 ?로 변환
        $url = preg_replace('/&/', '?', $url, 1);

        return $amp ? str_replace('&', '&amp;', $url) : $url;
    }

    private function __construct() {
    }
}
?>