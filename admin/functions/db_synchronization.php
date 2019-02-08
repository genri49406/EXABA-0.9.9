<?php
mb_internal_encoding("UTF-8");
include '../../config.php';

if (isset($_GET['last'])) {
$last = (int)$_GET['last'];
} else {
$last = 0;
}

$page_title = 'Синхронизация тредов и постов в базе данных';

if (isset($_COOKIE["admin"])) {
	$input_admin_cookies = htmlspecialchars($_COOKIE["admin"], ENT_QUOTES);
	$input_admin_cookies = mysql_real_escape_string($input_admin_cookies);

	$input_admin_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
	$input_admin_ip = mysql_real_escape_string($input_admin_ip);

	$total = mysql_result(mysql_query("SELECT COUNT(*) FROM admin WHERE admin_cookies = '$input_admin_cookies' AND admin_ip = '$input_admin_ip'"),0);

		if ($total == 0) {
			mysql_close($link);
			header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/login.php');
			exit;
		}

} else {
	mysql_close($link);
	header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/login.php');
	exit;
}

include $root_dir .'functions/html.php';

echo'<div class="menu"><a href="../">На главную админки</a></div>';

if (empty($last)) {
echo'
<div class="post">
<h2>Синхронизация количества тредов в разделах:</h2>';

$result = mysql_query("SELECT * FROM board_config", $link);
while ($row = mysql_fetch_assoc($result)) {
$sec_id = $row['sec_id'];
$sec_title = $row['sec_title'];
$sec_rows = $row['sec_rows'];

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board WHERE post_sec ='$sec_id'"),0);
mysql_query("update board_config SET sec_rows = '$total' WHERE sec_id ='$sec_id'", $link);

echo '<p>Тредов в разделе '.$sec_title.' по статистике базы данных: '.$sec_rows.'<br>
После синхронизации: '.$total.'</p>
';
}

echo '</div>';
}

echo '
<div class="post">
<h2>Синхронизация количества постов в тредах:</h2>
<form action="?last='.$last.'" method="get" enctype="multipart/form-data">
<div>Впишите количество последних тредов для синхронизации: <input type="text" name="last" value="'.$last.'"><input type="submit" value="OK">
</div>
</form>
';

$result = mysql_query("SELECT post_id, post_num FROM board WHERE post_sec > '0' ORDER BY post_id DESC LIMIT $last", $link);
while ($row = mysql_fetch_assoc($result)) {
$thread_id = $row['post_id'];
$post_num = $row['post_num'];

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board WHERE post_of = '$thread_id'"),0);
mysql_query("UPDATE board SET post_num = '$total' WHERE post_id = '$thread_id'", $link);

echo '<p>Постов в треде '.$thread_id.' по статистике базы данных: '.$post_num.'<br>
После синхронизации: '.$total.'</p>
';
}

echo '</div>
';

footer();
mysql_close($link);
?>