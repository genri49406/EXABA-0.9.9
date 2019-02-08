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

$result = mysql_query("SELECT * FROM board WHERE post_id = '$id' AND post_sec <> '0'");
$row = mysql_fetch_assoc($result);
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$post_sec = $row['post_sec'];
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

mysql_query("DELETE FROM board WHERE post_id = '$id'");

$sql = ("SELECT * FROM board WHERE post_of = '$id'");
$result = mysql_query($sql, $link);

while ($row = mysql_fetch_assoc($result)) {
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];

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

mysql_query("DELETE FROM board WHERE post_of = '$id'");

}

	if ($post_sec > 0) {
		mysql_query("UPDATE board_config SET sec_rows = sec_rows - 1 WHERE sec_id = $post_sec", $link);
	}

mysql_close($link);
?>