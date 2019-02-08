<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include '../config.php';

$page_title = "Создание, редактирование разделов борды";

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

include '../functions/html.php';

echo '
<div class="menu"><a href="index.php">На главную админки</a></div>

<form action="functions/sec_edit.php" method="post" enctype="multipart/form-data">
<div class="post">
<h2>Создать новый раздел:</h2>
<label>Название раздела:<br>
<input type="text" name="sec_title" maxlength="70"></label><br>
<label>Описание раздела:<br>
<input type="text" name="sec_description" maxlength="500"></label><br>
<label>Имя файла:<br>
<input type="text" name="file_name" maxlength="10" >.php</label><br>
<label>Индекс сортировки:<br>
<input type="text" name="sort_index" maxlength="10"></label><br>
<label>Тредов на странице:<br>
<input type="text" name="sec_limit" maxlength="3"></label><br>
<label>Общий лимит на постинг в 30 минут:<br>
<input type="text" name="sec_total_limit" maxlength="2"></label><br>
<label>Лимит на постинг с одного ip в 30 минут:<br>
<input type="text" name="sec_ip_limit" maxlength="1"></label><br>
<input type="submit" class="form_submit" value="Создать раздел">
<div class="info"></div>
</div>
</form>
';

$result = mysql_query("SELECT * FROM board_config", $link);
while ($row = mysql_fetch_assoc($result)) {
$sec_id = $row['sec_id'];
$sec_title = $row['sec_title'];
$sec_description = $row['sec_description'];
$sec_rows = $row['sec_rows'];
$sec_limit = $row['sec_limit'];
$sec_total_limit = $row['sec_total_limit'];
$sec_ip_limit = $row['sec_ip_limit'];
$sort_index = $row['sort_index'];
$file_name = $row['file_name'];

if ($sec_rows > 0 ) {
	$move_to = '';
	$move_to .= '
	<div class="info">
	<span id="move">
	<select id="edit_'.$sec_id.'_all" class="edit_select" onchange="move_select(\''.$sec_id.'_all\', this.options[selectedIndex].value);">
	<option value="0" selected="selected">Перенести все треды в раздел:</option>'."\r\n";

	$section_result = mysql_query("SELECT * FROM board_config ORDER BY sort_index ASC", $link);
		while ($row = mysql_fetch_assoc($section_result)) {
		$mysec_id = $row['sec_id'];
		$mysec_title = $row['sec_title'];
			if ($sec_id != $mysec_id) {
				$move_to .= '	<option value="'.$mysec_id.'">'.$mysec_title.'</option>'."\r\n";
			}
		}
	mysql_free_result($section_result);
	$move_to .= '	<option value="-1">Удаленные</option>
	</select>
	</span>
	<input type="submit" class="form_submit" value="Внести изменения">
	</div>
	';
} else {
	$move_to = '	<div class="info"><a href="functions/sec_edit.php?delete='.$sec_id.'" title="Удалить" class="delete_image"></a><input type="submit" class="form_submit" value="Внести изменения">
	</div>';
}

echo '
<form action="functions/sec_edit.php" method="post" enctype="multipart/form-data">
<div class="post">
<h2>Редактирование раздела '.$sec_title.':</h2>
<label>Название раздела:<br>
<input type="text" name="sec_title" maxlength="70" value="'.$sec_title.'"></label><br>
<label>Описание раздела:<br>
<input type="text" name="sec_description" maxlength="500" value="'.$sec_description.'"></label><br>
<label>Имя файла:<br>
<input type="text" name="file_name" maxlength="10" value="'.$file_name.'">.php</label><br>
<label>Индекс сортировки:<br>
<input type="text" name="sort_index" maxlength="10" value="'.$sort_index.'"></label><br>
<label>Тредов на странице:<br>
<input type="text" name="sec_limit" maxlength="3" value="'.$sec_limit.'"></label><br>
<label>Общий лимит на постинг в 30 минут:<br>
<input type="text" name="sec_total_limit" maxlength="2"  value="'.$sec_total_limit.'"></label><br>
<label>Лимит на постинг с одного ip в 30 минут:<br>
<input type="text" name="sec_ip_limit" maxlength="1"  value="'.$sec_ip_limit.'"></label><br>
<input type="hidden" name="sec_id" value="'.$sec_id.'">
'.$move_to.'
</div>
</form>
';
}

mysql_free_result($result);
mysql_close($link);
footer();
?>