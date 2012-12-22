<?php
/**
 * @class
 * @brief RSS 2.0 XML을 만드는데 사용하는 단순한 기능의 클래스
 */
class Rss2 {
    private function __construct() {
    }

    /**
     * RSS2.0 XML을 만들어 반환한다.
     *
     * @param array 데이터
     * @return string
     */
    public static function make($data) {
        $rss = array();
        $rss[] = '<?xml version="1.0" encoding="UTF-8"?>
            <rss version="2.0"><channel>
                <title>'.htmlspecialchars($data['title']).'</title>
                <link>'.$data['link'].'</link>
                <description>
                    <![CDATA['.$data['description'].']]>
                </description>
                <language>ko</language>
                <pubDate>'.self::gmtRFC822($data['pubDate']).'</pubDate>';

        if ( is_array( $data['items'] ) ) {
            foreach ( $data['items'] as $item ) {
                $rss[] = '
                    <item>
                        <title>'.htmlspecialchars($item['title']).'</title>
                        <link>'.$item['link'].'</link>
                        <description>
                            <![CDATA['.$item['description'].']]>
                        </description>
                        <pubDate>'.self::gmtRFC822($item['pubDate']).'</pubDate>
                    </item>';
            }
        }

        $rss[] = '</channel></rss>';

        return implode('', $rss);
    }

    /**
     * @brief 유닉스 타임을 RFC822 논고에 따른 GMT 표준시로 변환한다.
     *
     * @param int unixtime
     * @return string
     */
    public static function gmtRFC822( $unixtime ) {
        return gmdate("D, d M Y H:i:s", $unixtime) . ' GMT';
    }
}
?>