<?
session_start();
require './include/startup.php';

if ( $g->au['roleid'] != 'ADMIN') {
	Alert::back('error!');
}

if ( !$_GET['ip'] ) {
	Alert::back('error!!');
}

// ip block
$sql = "INSERT htline.ignoreip SET
			ip = '{$_GET['ip']}',
			type = 'Y',
			createdate = NOW()";
$g->db->query($sql);

// delete
$sql = "DELETE FROM htline.hboard
		WHERE ip = '{$_GET['ip']}'";
$g->db->query($sql);

header('Location: '. $_SERVER['HTTP_REFERER']);
?>
