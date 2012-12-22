<?php
/**
 * @brief 이메일
 */
class Email {
    /**
     * @brief 메일 제목 또는 사람 이름을 utf-8표시 base64_encode로 변환하여 반환한다.
     * @param $str string 변환할 문자열
     * @return string
     */
    private static function encode2047($str) {
        return $str ? '=?utf-8?b?' . base64_encode($str) . '?=' : $str;
    }

    /**
     * @brief '"인코딩된 이름" <주소>'를 만들어 반환
     * @param $addr string 주소
     * @param $name string 이름
     * @return string
     */
    private static function make_name_addr($addr, $name) {
        $addr = "<{$addr}>";

        if ( $name ) {
            $name = self::encode2047($name);
            return "\"{$name}\" {$addr}";
        } else {
            return $addr;
        }
    }

    private $subject;
    private $content;
    private $from_addr;
    private $from_name;
    private $replyto_name;
    private $replyto_addr;
    private $boundary;
    private $to_list = array();
    private $file_list = array();

    /**
     * @brief 생성자
     * @param $subject string 제목
     * @param $content string 내용
     * @param $from_addr string 보내는이 주소
     * @param $from_name string 보내는이 이름
     * @param $replyto_addr string 답신 주소
     * @param $replyto_name string 답신 이름
     */
    public function __construct($subject, $content,
        $from_addr, $from_name = '',
        $replyto_addr = '', $replyto_name = '') {
        $this->set_subject($subject);
        $this->set_content($content);
        $this->set_from($from_addr, $from_name);
        $this->set_replyto($replyto_addr, $replyto_name);
        $this->boundary = uniqid('_Part_').'_'.time();
    }

    /**
     * @brief 제목 설정
     * @param $subject string 제목
     */
    public function set_subject($subject) {
        $this->subject = $subject;
    }

    /**
     * @brief 내용 설정
     * @param $content string 내용
     */
    public function set_content($content) {
        $this->content = $content;
    }

    /**
     * @brief 보내는이 주소 반환
     * @return string
     */
    public function get_from_addr() {
        return $this->from_addr;
    }

    /**
     * @brief 보내는이 설정
     * @param $addr string 주소
     * @param $name string 이름
     */
    public function set_from($addr, $name = '') {
        $this->from_addr = $addr;
        $this->from_name = $name;
    }

    /**
     * @brief 답신 설정
     * @param $addr string 주소
     * @param $name string 이름
     */
    public function set_replyto($addr, $name = '') {
        $this->replyto_addr = $addr;
        $this->replyto_name = $name;
    }

    /**
     * @brief 받는이 주소 목록 반환
     * @return array
     */
    public function get_to_addr_list() {
        return array_keys($this->to_list);
    }

    /**
     * @brief 받는이 추가
     * @param $addr string 주소
     * @param $name string 이름
     */
    public function add_to($addr, $name = '') {
        $this->to_list[$addr] = $name;
    }

    /**
     * @brief 받는이 모두 지움
     */
    public function clean_to() {
        $this->to_list = array();
    }

    /**
     * @brief 받는이 한명 설정
     * @param $addr string 주소
     * @param $name string 이름
     */
    public function set_to($addr, $name = '') {
        $this->clean_to();
        $this->add_to($addr, $name);
    }

    /**
     * @brief 파일을 첨부한다.
     * @details 파일명을 지정하지 않으면 경로에 포함된 파일명을 이름으로 채택한다.\n
     * MIME를 지정하지 않으면 자동 지정된다.
     * @param $fpath string 파일 경로
     * @param $fname string 파일 이름
     * @param $fmime string 파일 MIME
     * @return boolean 파일 오류시 false 반환
     */
    public function attach_file($fpath, $fname = '', $fmime = '') {
        if ( !is_file($fpath) ) return false;

        if ( !$fname ) $fname = basename($fpath);
        if ( !$fmime ) $fmime = mime_content_type($fpath);

        $this->file_list[] = array(
            'path' => $fpath,
            'name' => $fname,
            'type' => $fmime,
        );

        return true;
    }

    /**
     * @brief 첨부 파일을 모두 지운다.
     */
    public function remove_all_files() {
        $this->file_list = array();
    }

    /**
     * @brief 헤더를 반환한다.
     * @return string
     */
    public function header() {
        // 제목
        $subject = self::encode2047($this->subject);

        // 보내는이
        $from = self::make_name_addr($this->from_addr, $this->from_name);

        if ( $this->replyto_addr ) {
            $replyto = self::make_name_addr($this->replyto_addr, $this->replyto_name);
        } else {
            $replyto = $from;
        }

        // 받는이
        $to_list = array();
        foreach ( $this->to_list as $addr => $name ) {
            $to_list[] = self::make_name_addr($addr, $name);
        }
        $to = implode(',', $to_list);

        // 컨텐츠 타입
        $content_type = empty($this->file_list) ? 'multipart/alternative' : 'multipart/mixed';

        // 헤더
        $header = array();
        $header[] = 'From: '.$from;
        $header[] = 'To: '.$to;
        $header[] = 'Reply-To: '.$replyto;
        $header[] = 'Subject: '.$subject;
        $header[] = 'MIME-Version: 1.0';
        $header[] = "Content-Type: {$content_type}; boundary=\"{$this->boundary}\"";

        return implode("\r\n", $header);
    }

    /**
     * @brief 바디를 반환한다.
     * @return string
     */
    public function body() {
        // BASE64 코드를 76크기로 자르고 마지막 \r\n은 제거한다.
        $content = substr(chunk_split(base64_encode($this->content)), 0, -2);

        // 내용
        $body = array();
        $body[] = '--'.$this->boundary;
        $body[] = 'Content-Type: text/html; charset=utf-8';
        $body[] = "Content-Transfer-Encoding: base64\r\n";
        $body[] = $content;

        // 첨부파일
        if ( !empty($this->file_list) ) {
            foreach ( $this->file_list as $file ) {
                $fp = fopen($file['path'], 'r'); // 파일을 읽어온다.
                if ( !$fp ) return false;

                $fdata = fread($fp, filesize($file['path']));
                fclose($fp);

                // BASE64 코드를 76크기로 자르고 마지막 \r\n은 제거한다.
                $fdata = substr(chunk_split(base64_encode($fdata)), 0, -2);

                $fname = self::encode2047($file['name']);

                // 파일
                $body[] = '--'.$this->boundary;
                $body[] = "Content-Type: {$file['type']}; name=\"{$fname}\"";
                $body[] = 'Content-Transfer-Encoding: base64';
                $body[] = "Content-Disposition: attachment; filename=\"{$fname}\"\r\n";
                $body[] = $fdata;
            }
        }

        // 멀티파트 종료
        $body[] = "--{$this->boundary}--";

        return implode("\r\n", $body);
    }
}
?>