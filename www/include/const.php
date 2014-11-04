<?php
## ETC ##
define('DOMAIN_WEB', 'hoitalk.com');
define('DOMAIN_MOBILE', 'm.hoitalk.com');
define('COOKIE_DOMAIN', '.'.DOMAIN_WEB);

// P3P 참고문서 http://support.microsoft.com/kb/290333/ko
define('P3P', 'P3P: CP="NOI CURa ADMa DEVa TAIa OUR DELa BUS IND PHY ONL UNI COM NAV INT DEM PRE"');


## DIR ##
// 기본
$ROOTPATH = dirname(__FILE__).'/..';
define('DIR_ROOT', "{$ROOTPATH}"); // 루트
define('DIR_INCLUDE', DIR_ROOT.'/include'); // include
define('DIR_LIB', DIR_ROOT.'/lib'); // lib
define('DIR_BATCH', DIR_ROOT.'/batch'); // batch

// 웹
define('DIR_WEB', DIR_ROOT.'/web'); // 웹 루트
define('DIR_WEB_FILE', DIR_WEB.'/file'); // 웹접근 가능한 업로드 및 서비스가 생성한 파일
define('DIR_WEB_TMP', DIR_WEB_FILE.'/tmp'); // 웹접근 가능한 업로드 및 서비스가 생성한 임시파일
// 모바일
define('DIR_MOBILE', DIR_ROOT.'/mobile'); // 모바일 루트
// 확장
//define('DIR_AVATAR', DIR_WEB_FILE.'/avatar'); // 아바타
//define('DIR_BANNER', DIR_WEB_FILE.'/banner'); // 배너광고


## URL ##
// 웹
define('URL_WEB', 'http://'.DOMAIN_WEB.'/web');
define('URL_WEB_STATIC', 'http://'.DOMAIN_WEB.'/web'); // 웹 정적파일
define('URL_WEB_FILE', 'http://'.DOMAIN_WEB.'/web/file');
define('URL_WEB_TMP', URL_WEB_FILE.'/tmp');
// 모바일
define('URL_MOBILE', 'http://'.DOMAIN_MOBILE);
define('URL_MOBILE_STATIC', 'http://'.DOMAIN_MOBILE); // 모바일 정적파일
// 확장
//define('URL_AVATAR', URL_WEB_FILE.'/avatar'); // 아바타
//define('URL_BANNER', URL_WEB_FILE.'/banner'); // 배너광고


## DB ##
/*
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'momohoi');
define('MYSQL_PW', 'momohoi1008');
define('MYSQL_DB', 'htline');
define('MYSQL_PORT', 3306);
define('MYSQL_SOCKET', null);
*/
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'u839082462_kyoe');
define('MYSQL_PW', 'lkhoeao1008');
define('MYSQL_DB', 'u839082462_hoi');
define('MYSQL_PORT', 3306);
define('MYSQL_SOCKET', null);


## MemCache ##
/*
define('MEMCACHE_HOST', DEV_SERVER ? 'localhost' : 'localhost');
define('MEMCACHE_PORT', 11211);
*/

## AES ##
define('AES_PASSWORD', 'b!9a$D<|6R*Sb*Ex');
?>
