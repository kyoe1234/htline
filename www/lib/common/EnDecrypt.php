<?php
/**
 * @class
 * @brief 암호/복호화 클래스
 */
class EnDecrypt {
    // init vector's length
    const INIT_VECTOR = 16;
    // 비밀 암호키
    const SEC_KEY = 'sdfF0yh9';

    private function __construct() {
    }

    /**
     * @brief 단방향 암호화
     * @param string
     * @return string
     */
    public static function get1WayCrypt($str) {
        return crypt($str, CRYPT_MD5);
    }

    /**
     * @brief 비밀 암호키를 이용한 양방향 암호화
     * @param string
     * @return string
     */
    public static function getMd5Encode($plain_text) {
        $nIv = self::INIT_VECTOR;
        $plain_text .= "\x13";
        $n = strlen($plain_text);
        if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
        $i = 0;

        while ($nIv-- > 0) $enc_text .= chr(mt_rand() & 0xff);

        $iv = substr(self::SEC_KEY ^ $enc_text, 0, 512);
        while ( $i < $n ) {
            $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
            $enc_text .= $block;
            $iv = substr($block . $iv, 0, 512) ^ self::SEC_KEY;
            $i += 16;
        }

        return base64_encode($enc_text);
    }

    /**
     * @brief 비밀 암호키를 이용한 양방향 복호화
     * @param string
     * @return string
     */
    public static function getMd5Decode($enc_text) {
        $nIv = self::INIT_VECTOR;
        $enc_text = base64_decode($enc_text);
        $n = strlen($enc_text);
        $i = $nIv;
        $plain_text = '';
        $iv = substr(self::SEC_KEY ^ substr($enc_text, 0, $nIv), 0, 512);
        while ( $i < $n ) {
            $block = substr($enc_text, $i, 16);
            $plain_text .= $block ^ pack('H*', md5($iv));
            $iv = substr($block . $iv, 0, 512) ^ self::SEC_KEY;
            $i += 16;
        }
        return preg_replace('/\\x13\\x00*$/', '', $plain_text);
    }
}
?>
