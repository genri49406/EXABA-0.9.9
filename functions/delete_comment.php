<?php
mb_internal_encoding("UTF-8");
include '../config.php';

$id = (int)$_POST['id'];

if (isset($_COOKIE['post_owner'])) {
	$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
	$post_unique_id = mysql_real_escape_string($post_unique_id);
} else {
	echo '<p>Ошибка удаления!</p>';
	mysql_close($link);
	exit;
}

$result = mysql_query("SELECT * FROM board WHERE post_id = '$id' AND post_sec = '0'", $link);
$row = mysql_fetch_assoc($result);

if (mysql_num_rows($result) > 0) {
$post_id = $row['post_id'];
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$post_num = $row['post_num'];
$post_of = $row['post_of'];
$post_bump = $row['post_bump'];
$post_cookies = $row['post_cookies'];
} else {
echo '<h2>Ошибка удаления!</h2><p>Комментарий, который вы пытаетесь удалить, не найден в базе данных.</p>';
mysql_close($link);
exit;
}

if ($post_cookies != $post_unique_id) {
	$result = mysql_query("SELECT * FROM board WHERE post_id = '$post_of'", $link);
	$row = mysql_fetch_assoc($result);
	$post_cookies = $row['post_cookies'];
}

if  ($post_cookies == $post_unique_id) {

if (!empty($post_img)) {
	$img_del = '../'.$img_dir.'/'.$post_img.'_small.'.$img_ext;

		if (file_exists($img_del)) {
			unlink($img_del);
		}

	$img_del = '../'.$img_dir.'/'.$post_img.'_big.'.$img_ext;

		if (file_exists($img_del)) {
			unlink($img_del);
		}

}

mysql_query("DELETE FROM board WHERE post_id = '$id' AND post_sec = '0'");
$result = mysql_query("SELECT * FROM board WHERE post_of = '$post_of'  ORDER BY post_id DESC LIMIT 1", $link);
$row = mysql_fetch_assoc($result);
$post_time = $row['post_time'];

if (mysql_num_rows($result) > 0) {
	mysql_query("UPDATE board SET post_num = post_num - 1, post_bump = '$post_time' WHERE post_id = '$post_of'");
} else {
	mysql_query("UPDATE board SET post_num = post_num - 1, post_bump = post_time WHERE post_id = '$post_of'");
}

} else {
echo '<h2>Ошибка удаления!</h2><p>Комментарий, который вы пытаетесь удалить, либо уже удален, либо он вам не принадлежит.<br>Если вы все же уверены, что он существует и желаете его удалить, обратитесь к автору со словами: «Хочу запилить твой коммент - какие подводные камни?»</p>';
}
mysql_close($link);
?>