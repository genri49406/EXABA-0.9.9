<?php
mb_internal_encoding("UTF-8");
include '../config.php';

if (isset($_GET['id'])) {
	$ban_id = (int)$_GET['id'];
}

if (isset($_GET['remove_ban'])) {
	$remove_ban = (int)$_GET['remove_ban'];
}

if (isset($_COOKIE['post_owner'])) {
$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
$post_unique_id = mysql_real_escape_string($post_unique_id);
} else {
echo 'Ошибка авторизации<br>Вы не опознаны как автор этого треда.';
exit;
}

$sql = ("SELECT post_of, post_ip, post_cookies FROM board WHERE post_id = '$ban_id'");
$result = mysql_query($sql, $link);
$row = mysql_fetch_assoc($result);
$post_of = $row['post_of'];
$ban_ip = $row['post_ip'];
$ban_cookies = $row['post_cookies'];

if ($post_unique_id == $ban_cookies && $remove_ban == 1) {
mysql_query("DELETE FROM black_list WHERE ban_id = '$ban_id'");
echo 'Все баны треда очищены';
mysql_close($link);
exit;
}

$sql = ("SELECT post_cookies FROM board WHERE post_id = '$post_of'");
$result = mysql_query($sql, $link);
$row = mysql_fetch_assoc($result);
$post_cookies = $row['post_cookies'];

if ($post_unique_id == $post_cookies) {
$result = mysql_query("SELECT * FROM black_list WHERE ban_id = '$post_of' AND ban_ip ='$ban_ip' OR ban_id = '$post_of' AND ban_cookies ='$ban_cookies'", $link);
$num_rows = mysql_num_rows($result);
$row = mysql_fetch_assoc($result);
$post_edit =' <span title="Удалить" class="delete_image" onclick="delete_comment(\''.$ban_id.'\')">&nbsp;</span>';

if (empty($num_rows)) {
mysql_query("INSERT INTO black_list ( ban_id, ban_ip, ban_cookies, ban_browser, moderator) VALUES ('$post_of', '$ban_ip', '$ban_cookies', '', '0')");
echo 'Забанен!'. $post_edit
;
} else {
echo 'Забанен!'. $post_edit
;
}
} else {
echo 'Ошибка авторизации<br>Вы не опознаны как автор этого треда.';
mysql_close($link);
exit;
}
mysql_close($link);
?>