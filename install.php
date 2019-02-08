<?php
mb_internal_encoding("UTF-8");
include 'config.php';

if(isset($_GET['install'])) {

	if (chmod("img/tmp_file", 0700)) {
		$all_errors = 'Изменен режим доступа директории img/tmp_file на chmod 0700<br>'."\n\n";
	} else {
		$all_errors = 'Не удалось изменить режим доступа директории img/tmp_file<br>'."\n\n";
		$error = 1;
	}

	if (chmod("config.php", 0600)) {
		$all_errors .= 'Изменен режим доступа к файлу config.php на chmod 0600<br><br>'."\n\n";
	} else {
		$all_errors .= 'Не удалось изменить режим доступа к файу config.php<br><br>'."\n\n";
		$error = 1;
	}

$check_sec = mysql_result(mysql_query("SELECT COUNT(*) FROM board_config"),0);
	if ($check_sec == 0) {
		mysql_query("ALTER DATABASE $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

			if (!mysql_error()) {
				$all_errors .= 'Установка кодировки UTF-8 для базы данных по умолчанию: ОК<br><br>'."\n\n";
			} else {
				$all_errors .= 'Ошибка установки кодировки UTF-8 для базы данных по умолчанию: '.mysql_error().'<br><br>'."\n\n";
				$error = 1;
			}

	}


mysql_query("DROP TABLE IF EXISTS passwords");
mysql_query("ALTER TABLE board DROP post_tripcode");


$result_for = mysql_query("SELECT post_id, post_for FROM board WHERE post_for <> '0' AND post_of <> '0' ");
while ($row = mysql_fetch_assoc($result_for)) {
$for_id = $row['post_id'];
$post_for = $row['post_for'];
mysql_query("UPDATE board SET post_text = CONCAT('&gt;&gt;$post_for ', post_text) WHERE post_id = '$for_id'");
}

mysql_query("UPDATE board SET post_for = ''");
mysql_query("ALTER TABLE board DROP post_for");

mysql_query("CREATE TABLE IF NOT EXISTS admin (
  admin_name tinytext NOT NULL,
  admin_password tinytext NOT NULL,
  admin_cookies tinytext NOT NULL,
  admin_login_time int(11) NOT NULL,
  admin_login_attempt tinyint(1) NOT NULL,
  admin_ip tinytext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;admin&#187; ОК<br>'."\n\n";
	} else {
		$all_errors .= 'таблица admin: '.mysql_error().'<br>'."\n\n";
		$error = 1;
	}

// для апгрейта прошлых версий эксаб

	$result = mysql_query("SHOW columns from admin where field='admin_ip'");
		if (mysql_num_rows($result) == 0) {
			mysql_query("ALTER TABLE admin ADD `admin_ip` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;");
		}

$index_result = mysql_query("SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = '$dbname'
AND TABLE_NAME = 'board'
AND COLUMN_NAME = 'img_size'
AND COLUMN_KEY != ''");

$my_index = mysql_fetch_array($index_result);
if (mysql_num_rows($index_result) == 0) {
	mysql_query("ALTER TABLE `board` CHANGE `img_size` `img_size` SMALLINT(5) NOT NULL");
	mysql_query("ALTER TABLE `board` ADD INDEX(`img_size`)");
}

// конец апгрейта прошлых версий эксаб

mysql_query("CREATE TABLE IF NOT EXISTS black_list (
  ban_id int(11) NOT NULL,
  ban_ip tinytext NOT NULL,
  ban_cookies text NOT NULL,
  ban_browser text NOT NULL,
  moderator enum('0','1') NOT NULL,
  KEY ban_post_id (ban_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;black_list&#187; ОК<br>'."\n\n";
	} else {
		$all_errors .= 'таблица black_list: '.mysql_error().'<br>'."\n\n";
		$error = 1;
	}

mysql_query("CREATE TABLE IF NOT EXISTS board (
  post_id int(6) NOT NULL auto_increment,
  post_time int(11) NOT NULL,
  post_title tinytext NOT NULL,
  post_text text NOT NULL,
  img_dir tinytext NOT NULL,
  post_img tinytext NOT NULL,
  img_ext tinytext NOT NULL,
  img_height smallint(5) NOT NULL,
  img_width smallint(5) NOT NULL,
  img_size smallint(5) NOT NULL,
  thumb_w smallint(5) NOT NULL,
  thumb_h smallint(5) NOT NULL,
  post_sec tinyint(2) NOT NULL,
  post_num int(6) NOT NULL,
  post_of int(6) NOT NULL,
  post_bump int(11) NOT NULL,
  post_ip tinytext NOT NULL,
  post_browser text NOT NULL,
  post_cookies tinytext NOT NULL,
  post_op enum('0','1') NOT NULL,
  UNIQUE KEY post_id (post_id),
  KEY post_of (post_of),
  KEY post_sec (post_sec),
  KEY img_width (img_width,img_size)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;board&#187; ОК<br>'."\n\n";
	} else {
		$all_errors .= 'таблица board: '.mysql_error().'<br>'."\n\n";
		$error = 1;
	}

mysql_query("CREATE TABLE IF NOT EXISTS board_config (
  sec_id smallint(2) NOT NULL auto_increment,
  sec_title tinytext NOT NULL,
  sec_description mediumtext NOT NULL,
  sec_rows smallint(6) NOT NULL,
  sec_limit smallint(2) NOT NULL default '25',
  sec_total_limit smallint(1) NOT NULL default '4',
  sec_ip_limit smallint(1) NOT NULL default '2',
  file_name varchar(15) NOT NULL,
  sort_index tinyint(2) NOT NULL,
  PRIMARY KEY  (sec_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;board_config&#187; ОК<br>'."\n\n";
	} else {
		$all_errors .= 'таблица board_config: '.mysql_error().'<br>'."\n\n";
		$error = 1;
	}

mysql_query("CREATE TABLE IF NOT EXISTS img (
  img_hash char(32) NOT NULL,
  KEY img_hash (img_hash)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;img&#187; ОК<br>'."\n\n";
	} else {
		$all_errors .= 'таблица img: '.mysql_error().'<br>'."\n\n";
		$error = 1;
	}

mysql_query("CREATE TABLE IF NOT EXISTS post_config (
  config_post_id int(6) NOT NULL,
  comment_limit int(3) NOT NULL,
  show_op enum('0','1') NOT NULL,
  KEY config_post_id (config_post_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;post_config&#187; ОК<br>'."\n\n";
	} else {
		$all_errors .= 'таблица post_config: '.mysql_error().'<br>'."\n\n";
		$error = 1;
	}

mysql_query("CREATE TABLE IF NOT EXISTS statistics (
  user_cookies tinytext NOT NULL,
  user_time int(11) NOT NULL,
  user_ip tinytext NOT NULL,
  user_browser text NOT NULL,
  user_referer text NOT NULL,
  PRIMARY KEY  (user_time)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

	if (!mysql_error()) {
		$all_errors .= 'Coздание таблицы &#171;statistics&#187; ОК<br><br>'."\n\n";
	} else {
		$all_errors .= 'таблица statistics: '.mysql_error().'<br><br>'."\n\n";
		$error = 1;
	}

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board_config"),0);
	if (isset($_GET['b'])) {

		if ($total == 0) {
			mysql_query("INSERT INTO board_config VALUES(1, 'Бред', 'Бред', 0, 25, 4, 2, 'b', 1)");
			$all_errors .= 'Создание раздела &#171;Бред&#187; ОК<br><br>'."\n\n";
			$total = 1;
		}

	}

	if ($total > 0) {

		$result = mysql_query("SELECT * FROM board_config", $link);

			while ($row = mysql_fetch_assoc($result)) {
				$file_name = $row['file_name'];
				$sec_title = $row['sec_title'];

					if (!copy('admin/functions/sec_copy', $file_name.'.php')) {
						$error = 1;
						$all_errors .= 'Файл '.$file_name.'.php раздела &#171;'.$sec_title.'&#187; обновить неудалось<br>'."\n\n";
					} else {
						$all_errors .= 'Файл '.$file_name.'.php раздела &#171;'.$sec_title.'&#187; обновлен<br>'."\n\n";
					}

			}

	}

	if (isset($error)) {
		$error = $all_errors.'<br>Были замечены ошибки, пожалуйста скопируйте их и запостите на борде поддержки чтобы мы могли разрешить возможные проблемы.';
	} else {
		$error = $all_errors.'<br>Установка прошла успешно, <b>обязательно удалите файл install.php</b>';
	}

	$page_title = "Результат установки";
	include 'functions/404.php';

} else {

$check_install = mysql_query("SHOW columns from board_config");
	if ($check_install == 0) {

		$page_title = "Установка эксабы 0.9.9";
		$error ='Установка создаст все необходимые таблицы в базе данных, создаст нужные файлы и директории.
		<form action="install.php" method="get" enctype="multipart/form-data">
			<div>
			<label>Создать раздел &#171;Бред&#187;: <input type="checkbox" name="b" value="1"></label><br><br>
			<input type="submit" value="Установить">
			<input type="hidden" name="install" value="yes">
			</div>
		</form>';

	} else {

		$page_title = "Обновление эксабы на версию 0.9.9";
		$error ='Установка обновит структуру базы данных (данные не будут утеряны) и обновит существующие файлы разделов.
		<form action="install.php" method="get" enctype="multipart/form-data">
			<div>
			<input type="submit" value="Обновить">
			<input type="hidden" name="install" value="yes">
			</div>
		</form>';

	}

	include 'functions/404.php';
}
?>