<?php
/**
 * @brief SMTP
 */
class SMTP {
    private $host;
    private $domain;
    private $logs = array();
    private $error_message;

    /**
     * @brief 생성자
     * @param $host string SMTP 서버 호스트명
     * @param $domain string 전송을 요구하는 측의 도메인명.\n
     * 빈값이면 기본으로 $_SERVER['HTTP_HOST']가 설정된다.\n
     * $_SERVER['HTTP_HOST']가 없으면 $_SERVER['HOSTNAME']이 설정된다.
     */
    public function __construct($host = 'localhost', $domain = '') {
        $this->host = $host;
        $this->domain = ( $domain ) ? $domain : $_SERVER['HTTP_HOST'];
        if ( !$this->domain ) $this->domain = $_SERVER['HOSTNAME'];
    }

    /**
     * @brief 최근 메일 전송시 발생한 로그를 배열로 반환한다.
     * @return array
     */
    public function logs() {
        return $this->logs;
    }

    /**
     * @brief 로그를 추가한다.
     * @param $log string 로그
     */
    private function add_log($log) {
        $this->logs[] = $log;
    }

    /**
     * @brief 로그를 비운다.
     */
    private function clear_log() {
        $this->logs = array();
    }

    /**
     * @brief 에러 메시지를 설정한다.
     * @param $msg string 메시지
     */
    private function set_error_message($msg) {
        $this->error_message = $msg;
    }

    /**
     * @brief 가장 최근의 에러 메시지를 반환한다.
     * @return string
     */
    public function error_message() {
        return $this->error_message;
    }

    /**
     * @brief SMTP 서버에서 전송된 값을 로그에 추가하고 에러 유무를 반환한다.
     * @param $fp resource 소켓 포인터
     * @param $error_reset boolean 오류가 발생했을때 메일전송 중단할지 여부
     * @return boolean 오류코드가 포함되어 있다면 false 반환
     */
    private function receive_smtp($fp, $error_reset = true) {
        $recv = fgets($fp, 128);
        $this->add_log($recv);

        // 4 또는 5가 맨처음에 발견되면 오류코드이다.
        if ( preg_match('/^(4|5)/', $recv) ) {
            // 최근 에러메시지 설정
            $this->set_error_message($recv);

            if ( $error_reset ) {
                // 중단 및 종료 메시지를 SMTP 서버에 보내고 소켓을 닫는다.
                fputs($fp, "RSET\r\n");
                $this->add_log( fgets($fp, 128) );
                fputs($fp, "QUIT\r\n");
                $this->add_log( fgets($fp, 128) );
                fclose($fp);
            }

            return false;
        }

        return true;
    }

    /**
     * @brief 메일을 전송한다.
     * @details 송신 과정에 발생한 메시지는 logs() 메소드를 통해 확인 할 수 있다.
     * @see logs()
     * @param $email Email 이메일 객체
     * @return boolean
     */
    public function send(Email $email) {
        $this->clear_log();

        // SMTP 서버에 소켓 연결
        $fp = fsockopen($this->host, 25, $errno, $errstr, 30);
        if ( !$fp ) {
            $this->set_error_message("SMTP 연결 오류: {$errno} - {$errstr}");
            return false;
        }

        $mail_from = $email->get_from_addr();

        ## SMTP 서버와의 기본 송수신 절차 ##
        // 접속여부 수신
        if ( !$this->receive_smtp($fp) ) return false;
        // 인사 송수신 (HELO는 오류가 발생해도 중단하지 않는다.)
        fputs($fp, "HELO {$this->domain}\r\n");
        $this->receive_smtp($fp, false);
        // 보내는 메일 정보 송수신
        fputs($fp, "MAIL FROM: <{$mail_from}>\r\n");
        if ( !$this->receive_smtp($fp) ) return false;
        // 받는 메일 정보 송수신
        foreach ( $email->get_to_addr_list() as $addr ) {
            fputs($fp, "RCPT TO: <{$addr}>\r\n");
            if ( !$this->receive_smtp($fp) ) return false;
        }
        // 데이터를 보내겠음 송수신
        fputs($fp, "DATA\r\n");
        if ( !$this->receive_smtp($fp) ) return false;

        // 메일 헤더
        fputs($fp, $email->header());
        fputs($fp, "\r\n\r\n");
        // 메일 몸체
        fputs($fp, $email->body());
        // 몸체 종료
        fputs($fp, "\r\n.\r\n");
        // 데이터 전송완료 확인 메시지
        if ( !$this->receive_smtp($fp) ) return false;

        // 종료 절차 송수신
        fputs($fp, "QUIT\r\n");
        if ( !$this->receive_smtp($fp) ) return false;

        // 소켓 종료
        fclose($fp);

        // 정상적으로 처리 됨을 뜻하는 true 반환
        return true;
    }
}
?>
