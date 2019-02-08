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
			header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/login.php');
			exit;
		}

} else {
	mysql_close($link);
	header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/login.php');
	exit;
}

if(isset($_GET['delete'])) {
	$delete = (int)$_GET['delete'];
	$result = mysql_query("SELECT * FROM board WHERE post_sec = '$delete'", $link);
	$row = mysql_fetch_assoc($result);
	$old_file_name = $row['file_name'];

		if (mysql_num_rows($result) > 0) {
			$page_title = "Невозможно удалить раздел.";
			$error = "Только пустой раздел можно удалить, сначала перенесите все треды в другие разделы";
			include '../../functions/404.php';
			exit;
		} else {
			$result = mysql_query("SELECT * FROM board_config WHERE sec_id = '$delete'", $link);
			$row = mysql_fetch_assoc($result);
			$old_file_name = $row['file_name'];

				if (file_exists('../../'.$old_file_name.'.php')) {
					unlink('../../'.$old_file_name.'.php');
				}

			mysql_query("DELETE FROM board_config WHERE sec_id = '$delete'");
		}

	mysql_close($link);
	header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/sec.php');
	exit;
}

if (isset($_POST['sec_title']) && !empty($_POST['sec_title'])) {
	$sec_title = trim($_POST['sec_title']);
	$sec_title = htmlspecialchars($sec_title, ENT_QUOTES);
	$sec_title = mysql_real_escape_string($sec_title);
} else {
	$sec_title = "Новый";
}

if (isset($_POST['sec_description']) && !empty($_POST['sec_description'])) {
	$sec_description = trim($_POST['sec_description']);
	$sec_description = htmlspecialchars($sec_description, ENT_QUOTES);
	$sec_description = mysql_real_escape_string($sec_description);
} else {
	$sec_description = "Очень важный раздел";
}

if (isset($_POST['sec_limit']) && !empty($_POST['sec_limit'])) {
	$sec_limit = (int)$_POST['sec_limit'];
} else {
	$sec_limit = 25;
}

if (isset($_POST['sec_ip_limit']) && !empty($_POST['sec_ip_limit'])) {
	$sec_ip_limit = (int)$_POST['sec_ip_limit'];
} else {
	$sec_ip_limit = 2;
}

if (isset($_POST['sec_total_limit']) && !empty($_POST['sec_total_limit'])) {
	$sec_total_limit = (int)$_POST['sec_total_limit'];
} else {
	$sec_total_limit = 4;
}

if (isset($_POST['sort_index']) && !empty($_POST['sort_index'])) {
	$sort_index = (int)$_POST['sort_index'];
} else {
	$sort_index = 15;
}

if (isset($_POST['file_name']) && preg_match('/^[a-z0-9_]+$/i', $_POST['file_name'])) {
	$file_name = trim($_POST['file_name']);
	$file_name = htmlspecialchars($file_name, ENT_QUOTES);
	$file_name = mysql_real_escape_string($file_name);
} else {
	$page_title = "Ошибка в имени для файла.";
	$error = "В имени файла присутствуют не подходящие знаки или вы забыли указать имя.";
	include '../../functions/404.php';
	exit;
}

$sec_id = (int)$_POST['sec_id'];

if ($sec_id > 0){
	$result = mysql_query("SELECT * FROM board_config WHERE sec_id = '$sec_id'", $link);
	$row = mysql_fetch_assoc($result);
	$old_file_name = $row['file_name'];

		if (!file_exists('../../'.$old_file_name.'.php')) {
			copy('sec_copy', '../../'.$old_file_name.'.php');
		}

		if ($old_file_name != $file_name) {

			$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board_config WHERE file_name = '$file_name'"),0);

			if ($total == 0 && !file_exists('../../'.$file_name.'.php')) {
				rename('../../'.$old_file_name.'.php', '../../'.$file_name.'.php');
			} else {

				if ($total > 0) {
				$page_title = 'Ошибка переименования.';
				$error = 'Такой раздел существует.';
				include '../../functions/404.php';
				exit;
				} else {
				$page_title = 'Ошибка переименования.';
				$error = 'Файл с именем '.$file_name.'.php уже существует, переименуйте или удалите его.';
				include '../../functions/404.php';
				exit;
				}

			}

		}

	mysql_query("UPDATE board_config SET sort_index = '$sort_index', file_name = '$file_name', sec_title = '$sec_title', sec_description = '$sec_description', sec_limit = '$sec_limit', sec_ip_limit = '$sec_ip_limit', sec_total_limit = '$sec_total_limit' WHERE sec_id = '$sec_id'", $link);
} else {

	$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board_config WHERE file_name = '$file_name'"),0);

	if ($total == 0 && !file_exists('../../'.$file_name.'.php')) {
		copy('sec_copy', '../../'.$file_name.'.php');
	} else {

		if ($total > 0) {
		$page_title = 'Ошибка cоздания раздела.';
		$error = 'Такой раздел существует.';
		include '../../functions/404.php';
		exit;
		} else {
		$page_title = 'Ошибка cоздания раздела.';
		$error = 'Файл с именем '.$file_name.'.php уже существует, переименуйте или удалите его.';
		include '../../functions/404.php';
		exit;
		}

	}

	mysql_query("INSERT INTO board_config (sec_title, sec_description, sec_rows, sec_limit, sec_total_limit, sec_ip_limit, file_name, sort_index) VALUES ('$sec_title', '$sec_description', '0', '$sec_limit', '$sec_total_limit', '$sec_ip_limit', '$file_name', '$sort_index')",$link);
}

mysql_close($link);
header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/sec.php');
?>