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
			echo 'Пожалуйста авторизируйтесь';
			exit;
		}

} else {
	mysql_close($link);
	echo 'Пожалуйста авторизируйтесь';
	exit;
}

	if (preg_match('/_all/', $_POST['id'])) {
		$delete = (int)$_POST['id'];
	} else {
		$id = (int)$_POST['id'];
	}

$to = (int)$_POST['to'];

if (isset($delete)) {
	$result = mysql_query("SELECT * FROM board_config WHERE sec_id = '$to'", $link);
	$row = mysql_fetch_assoc($result);
	$sec_title = $row['sec_title'];

	if($delete == $to) {
		echo 'Треды и так в разделе '. $sec_title;
		exit;
	}

	if (mysql_num_rows($result) > 0 && $to != -1) {
		mysql_query("UPDATE board SET post_sec = $to WHERE post_sec = '$delete' AND post_sec <> '0'", $link);
		mysql_query("UPDATE board_config SET sec_rows = '0' WHERE sec_id = '$delete'", $link);
		$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board WHERE post_sec = '$to'"),0);
		mysql_query("UPDATE board_config SET sec_rows = '$total' WHERE sec_id = '$to'", $link);
		echo 'Все треды перенесены в раздел '.$sec_title;
		exit;
	}

	if ($to == -1) {
		mysql_query("UPDATE board SET post_sec = '$to' WHERE post_sec = '$delete' AND post_sec <> '0'", $link);
		mysql_query("UPDATE board_config SET sec_rows = '0' WHERE sec_id = '$delete'", $link);
		echo 'Все треды перенесены в раздел Удаленные';
		exit;
	}

	exit;
}


$result = mysql_query("SELECT * FROM board WHERE post_id = '$id' AND post_sec <> '0'");
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
			mysql_query("UPDATE board SET post_sec = $to WHERE post_id = '$id' AND post_sec <> '0'", $link);

				if ($post_sec > 0) {
					mysql_query("UPDATE board_config SET sec_rows = sec_rows - 1 WHERE sec_id = '$post_sec'", $link);
				}

			mysql_query("UPDATE board_config SET sec_rows = sec_rows + 1 WHERE sec_id = '$to'", $link);
			echo 'Перенесен в раздел '.$sec_title;
			exit;
		}

		if ($to == -1) {
			mysql_query("UPDATE board SET post_sec = '$to' WHERE post_id = '$id' AND post_sec > '0'", $link);
			mysql_query("UPDATE board_config SET sec_rows = sec_rows - 1 WHERE sec_id = '$post_sec'", $link);
			echo 'Перенесен в раздел Удаленные';
			exit;
		}

}
?>