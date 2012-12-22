<?php
/**
 * @addtogroup aes
 * @brief php의 mcrypt 모듈을 이용한 AES(RIJNDAEL-128) 암/복호화
 * @{
 */

/**
 * @brief 초기화 벡터를 생성한다.
 * @param $size int 벡터 길이
 * @return string
 */
function aes_create_iv($size) {
    $iv = '';
    for( $i = 0; $i < $size; $i++ ) {
        $iv .= chr(rand(0, 255));
    }

    return $iv;
}

/**
 * @brief AES(RIJNDAEL-128) 암호화
 * @param $plaintext string 평문
 * @param $password string 암호
 * @return string
 */
function aes_encrypt($plaintext, $password = AES_PASSWORD) {
    $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = aes_create_iv($size);

    return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $password, $plaintext, MCRYPT_MODE_ECB, $iv));
}

/**
 * @brief AES(RIJNDAEL-128) 복호화
 * @param $ciphertext string 암호문
 * @param $password string 암호
 * @return string
 */
function aes_decrypt($ciphertext, $password = AES_PASSWORD) {
    $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = aes_create_iv($size);

    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $password, pack("H*", $ciphertext), MCRYPT_MODE_ECB, $iv), "\0");
}

/*@}*/
?>