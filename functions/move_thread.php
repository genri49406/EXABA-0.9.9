<?php
mb_internal_encoding("UTF-8");
include '../config.php';

$id = (int)$_POST['id'];
$to = (int)$_POST['to'];
if (isset($_COOKIE['post_owner'])) {
$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
$post_unique_id = mysql_real_escape_string($post_unique_id);
} else {
echo 'Ошибка удаления! - Тред, который вы пытаетесь удалить, либо уже удален, либо он вам не принадлежит.<br>Если вы все же уверены, что он существует и желаете его удалить, обратитесь к автору со словами: «Хочу запилить этот тред - какие подводные камни?»';
exit;
}

$result = mysql_query("SELECT * FROM board WHERE post_id = '$id' AND post_sec <> '0' AND post_cookies = '$post_unique_id'");
$row = mysql_fetch_assoc($result);
$post_sec = $row['post_sec'];

if (mysql_num_rows($result) > 0) {

$result = mysql_query("SELECT * FROM board_config WHERE sec_id = '$to'", $link);
$row = mysql_fetch_assoc($result);
$sec_id = $row['sec_id'];
$sec_title = $row['sec_title'];

	if($post_sec == $to) {
		echo 'Тред уже в том разделе';
		exit;
	}

	if (mysql_num_rows($result) > 0 && $to != -1) {
		mysql_query("UPDATE board SET post_sec = '$to' WHERE post_id = '$id' AND post_sec <> '0'", $link);

			if ($post_sec > 0) {
				mysql_query("UPDATE board_config SET sec_rows = sec_rows - 1 WHERE sec_id = '$post_sec'", $link);
			}

		mysql_query("UPDATE board_config SET sec_rows = sec_rows + 1 WHERE sec_id = '$to'", $link);
		echo 'Тред перенесен в '.$sec_title.'.';
	}

	if ($to == -1) {
		mysql_query("UPDATE board SET post_sec = '$to', post_text = CONCAT('<h2>Этот тред удален ОПом</h2>', post_text) WHERE post_id = '$id' AND post_sec > '0'");
		mysql_query("UPDATE board_config SET sec_rows = sec_rows - 1 WHERE sec_id = '$post_sec'", $link);
		echo 'Тред перенесен в удаленные.';
	}

}

mysql_close($link);
?>