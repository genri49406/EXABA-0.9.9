<?php
mb_internal_encoding("UTF-8");
include '../../config.php';

$id = (int)$_POST['id'];

if (isset($_COOKIE["admin"])) {
	$input_admin_cookies = htmlspecialchars($_COOKIE["admin"], ENT_QUOTES);
	$input_admin_cookies = mysql_real_escape_string($input_admin_cookies);

	$input_admin_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
	$input_admin_ip = mysql_real_escape_string($input_admin_ip);

	$total = mysql_result(mysql_query("SELECT COUNT(*) FROM admin WHERE admin_cookies = '$input_admin_cookies' AND admin_ip = '$input_admin_ip'"),0);

		if ($total == 0) {
			mysql_close($link);
			echo '<a href="login.php">Пожалуйста авторизируйтесь</a>';
			exit;
		}

} else {
	mysql_close($link);
	echo '<a href="login.php">Пожалуйста авторизируйтесь</a>';
	exit;
}

$result = mysql_query("SELECT * FROM board WHERE post_id = '$id' AND post_sec = '0' LIMIT 1", $link);
$row = mysql_fetch_assoc($result);

if(mysql_num_rows($result) > 0) {
$post_id = $row['post_id'];
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$post_of = $row['post_of'];

if (!empty($post_img)) {

$img_del = '../../'.$img_dir.'/'.$post_img.'_small.'.$img_ext;
if (file_exists($img_del)) {
unlink($img_del);
}

$img_del = '../../'.$img_dir.'/'.$post_img.'_big.'.$img_ext;
if (file_exists($img_del)) {
unlink($img_del);
}

}

mysql_query("DELETE FROM board WHERE post_id = '$id' AND post_sec = '0'", $link);

$result = mysql_query("SELECT post_time FROM board WHERE post_of = '$post_of' ORDER BY post_id DESC LIMIT 1", $link);
$row = mysql_fetch_assoc($result);
$post_time = $row['post_time'];

if(mysql_num_rows($result) > 0) {
mysql_query("UPDATE board SET post_num = post_num - 1, post_bump = '$post_time' WHERE post_id = '$post_of'", $link);
} else {
mysql_query("UPDATE board SET post_num = '0', post_bump = post_time WHERE post_id = '$post_of'", $link);
}

mysql_close($link);
}
?>