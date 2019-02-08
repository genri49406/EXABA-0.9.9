<?php
mb_internal_encoding("UTF-8");
include '../../config.php';

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

$ban_id = (int)$_POST['id'];
$total_ban = (int)$_POST['total_ban'];

if ($ban_id == 0) {
mysql_query("TRUNCATE TABLE black_list");
echo 'Все баны очищены';
mysql_close($link);
exit;
}

$result = mysql_query("SELECT * FROM board WHERE post_id = '$ban_id'", $link);
$row = mysql_fetch_assoc($result);

if (mysql_num_rows($result) == 0) {
echo '<h2>Уже удалили.</h2><p>Пост №'.$ban_id.' не найден в базе данных.</p>';
mysql_close($link);
exit;
}

$post_id = $row['post_id'];
$post_of = $row['post_of'];
$ban_ip = $row['post_ip'];
$ban_cookies = $row['post_cookies'];

if (empty($post_of)) {
$tred_id = $post_id;
} else {
$tred_id = $post_of;
}

if ($total_ban == 0) {

		if($post_of = 0) {
			$delete = '<span class="delete_image" onclick="delete_post_admin(\''.$post_id.'\')" title="Удалить тред без возможности восстановления"> </span> ';
		} else {
			$delete = '<span class="delete_image" onclick="delete_comment(\''.$post_id.'\')" title="Удалить комментарий без возможности восстановления"> </span>';
		}

	$total = mysql_result(mysql_query("SELECT COUNT(*) FROM black_list WHERE ban_id = '$tred_id' AND ban_ip ='$ban_ip' OR ban_id = '$tred_id' AND ban_cookies ='$ban_cookies'"),0);

		if (empty($total)) {
			mysql_query("INSERT INTO black_list ( ban_id, ban_ip, ban_cookies, ban_browser, moderator) VALUES ('$tred_id', '$ban_ip', '$ban_cookies', '', '1')");
			echo 'Забанен в треде!'. $delete;
		} else {
			echo 'Забанен в треде!'. $delete;
		}

} else {

		if($post_of == 0) {
			$delete = '<span class="delete_image" onclick="delete_post_admin(\''.$post_id.'\')" title="Удалить тред без возможности восстановления"> </span> ';
		} else {
			$delete = '<span class="delete_image" onclick="delete_comment(\''.$post_id.'\')" title="Удалить комментарий без возможности восстановления"> </span>';
		}

	$total = mysql_result(mysql_query("SELECT COUNT(*) FROM black_list WHERE ban_id = '0' AND ban_ip ='$ban_ip' OR ban_id = '0' AND ban_cookies ='$ban_cookies'"),0);

		if (empty($total)) {
			mysql_query("INSERT INTO black_list ( ban_id, ban_ip, ban_cookies, ban_browser, moderator) VALUES ('0', '$ban_ip', '$ban_cookies', '', '1')");
			echo 'Забанен на доске!'. $delete;
		} else {
			echo 'Забанен на доске!'. $delete;
		}

}

mysql_close($link);
?>