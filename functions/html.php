<?php
if(count(get_included_files()) == 1) {
header("Location: http://".$_SERVER['SERVER_NAME']);
exit;
}

if (!isset($_COOKIE['extra_style']) || !preg_match('/^[a-z0-9_]+$/i', $_COOKIE['extra_style'])) {
	$css = "default";
} else {
	$css = htmlspecialchars($_COOKIE['extra_style'], ENT_QUOTES);
}

if (isset($_GET['page']) && (int)$_GET['page'] > 0) {
	$page = (int)$_GET['page'];
} else {
	$page = 0;
}

if (!isset($order_comments) && !isset($sec) && isset($limit)) {
	if ($page == 0 && $num_rows <= $limit) {
		$active_page = 1;
		$page = 0;
	} else if ($page == 0 && $num_rows > $limit) {
		$active_page = ceil($num_rows / $limit);
		$page = $num_rows - $limit;
	} else {
		$active_page = $page;
		$page = ($page * $limit) - $limit;
	}
} else {
	if ($page <= 1) {
		$active_page = 1;
		$page = 0;
	} else {
		$active_page = $page;
		$page = ($page * $limit) - $limit;
	}
}

if (!empty($page_description)) {
$description = '
<meta name="description" content="'.$page_description.'">';
} else {
$description ='';
}

if (!empty($for)) {
$for = '<input type="hidden" name="for" id="for" value="'.$for.'">';
} else {
$for='';
}

echo '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title>'.$host_name.' &bull; '.$page_title.'</title>'.$description.'
<link rel="stylesheet" href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'styles/'.$css.'/style.css?version='.$css_version.'" type="text/css">
<link rel="shortcut icon" href="http://'.$_SERVER['SERVER_NAME'].'/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'functions/scripts.js?version='.$scripts_version.'"></script>
</head>
<body>
';

if (isset($show_form)) {

	if (isset($op_cookies) && $op_cookies == $post_unique_id && $op_cookies <> "") {
		$show_op = ' <label class="op_input">Показать ОП\'а: <input type="checkbox" name="show_op" value="1"></label>';
	} else {
		$show_op = '';
	}

$go_back = "";
if (!empty($sec) && empty($id)) {
$input_id ='';
$input_sec = '<input type="hidden" name="sec" value="'.$sec.'">
';
$form_action = 'functions/new_thread.php';
$form_title = "Новая тема";
} else if (empty($sec) && !empty($id) && empty($post_of)) {
$input_sec = '';
$input_id = '<input type="hidden" name="id" value="'.$id.'">
';
$form_action = 'functions/new_post.php';
$form_title = "Новый комментарий";
} else if (empty($sec) && !empty($id) && !empty($post_of)) {
$input_sec = '';
$input_id = '<input type="hidden" name="id" value="'.$id.'">
';
$form_action = 'functions/new_post.php';
$form_title = "Ответ на комментарий №$id";
$page_title = "Ответ на комментарий №$id";
$go_back = '
		<br><br><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'post.php?id='.$post_of.'"><b>Вернуться к треду</b></a>
';
}

if ($allow_url_fopen > 0) {
$www_input ='<input type="text" class="form_url_file" name="www_file" maxlength="400" placeholder="URL адрес изображения">'."\r\n";
} else {
$www_input = '';
}

echo '
<form id="f1" action="'.$form_action.'" method="post" enctype="multipart/form-data">
<div class="form">
<div class="form_name" id="form_name">'.$form_title.'</div>
<div class="form_smiles" id="smiles_1"><div id="banner"></div></div>
<input class="form_title" type="text" name="title" maxlength="70" placeholder="Заголовок">
<input type="text" name="email" size="35" maxlength="75" value="" style="display: none;">
<span class="form_BB_codes" onclick="BB_code(\'f1\',\'[b]\',\'[\/b]\');" title="Полужирный"><strong>B</strong></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[i]\',\'[\/i]\');" title="Курсив"><em>I</em></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[s]\',\'[\/s]\');" title="Зачеркнутый"><del>S</del></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[u]\',\'[\/u]\');" title="Подчеркнутый"><ins>U</ins></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[spoiler]\',\'[\/spoiler]\');" title="Спойлер">Sp</span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[quote]\',\'[\/quote]\');" title="Цитата">Q</span><span class="form_BB_codes" onclick="show_smiles(\'1\',\'1\')" title="Смайлы">:)</span>'.$show_op.'
<textarea name="text" id="form_textarea" rows="8" cols="55"></textarea>
'.$www_input.'<input type="file" class="form_pc_file" name="img">
<input type="submit" class="form_submit" value="Отправить">'.$input_sec . $input_id . $for.'
</div>
</form>
';
} else {
$sec = "";
$post_sec = "";
$go_back = "";
}


echo '
<div class="menu" id="header">

	<select onchange="set_style(this)">
		<option value="0" selected="selected">Стили борды</option>
		<option value="none">Обычный</option>
		<option value="AtoX">AtoX</option>
		<option value="Neutron">Neutron</option>
	</select>

	<nav>
		<ul>
			<li><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'">Глагне</a></li>
';

$result = mysql_query("SELECT * FROM board_config ORDER BY sort_index ASC", $link);
while ($row = mysql_fetch_assoc($result)) {
$sec_id = $row['sec_id'];
$sec_title = $row['sec_title'];
$sec_description = $row['sec_description'];
$file_name = $row['file_name'];

if (isset($sec) && $sec == $sec_id || isset($post_sec) && $post_sec == $sec_id) {
	echo '			<li><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.''.$file_name.'.php" id="active" title="'.$sec_description.'">'.$sec_title.'</a></li>'."\r\n";
} else {
	echo '			<li><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.''.$file_name.'.php" title="'.$sec_description.'">'.$sec_title.'</a></li>'."\r\n";
}
}

echo '			<li><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'gallery.php" title="Галерея лучших изображений рунета и забугорья">Галерея</a></li>
			<li><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'p.php" title="Все треды и посты в одном потоке">Однопоток</a></li>
		</ul>
	</nav>'.$go_back.'

</div>
';

function pages($num_rows, $limit, $id, $order) {

global $active_page;

if(isset($limit)) {
$num_pages = ceil($num_rows / $limit);
if(isset($id)) {
$url_id = 'id='.$id.'&amp;';
}
if(isset($order)) {
$order = (int)$order;
$order = '&amp;order='.$order;
} else {
$order='';
}

if ($num_pages > '1') {
echo '
<div class="pages">Страницы: ';

				if ($active_page >= 5) {
					$i = $active_page - 5;
				} else {
					$i = 0;
				}

				$a = $i + 10;

					if( $i <> 0) {
					echo ' <a href="?'.$url_id.'page=1', $order.'">1</a> ... ';
					}

					while ($i++ < $a && $i <= $num_pages) {

						if ($i == $active_page) {
							echo '<a href="?'.$url_id.'page='.$i, $order.'"><b>'.$i.'</b></a> ';
						} else {
							echo '<a href="?'.$url_id.'page='.$i, $order.'">'.$i.'</a> ';
						}
					}

					if( $a < $num_pages) {
					echo ' ... <a href="?'.$url_id.'page='.$num_pages, $order.'">'.$num_pages.'</a> ';
					}
echo "</div>
";
}
}
}

function footer() {
global $num_rows, $web_folder;

if ($num_rows > 3) {
	$top_nav = "\r\n".'	<a href="#header" class="form_name">Наверх</a><br>';
} else {
	$top_nav = '';
}

echo '
<footer id="footer">'.$top_nav.'
	<a href="http://exachan.com/"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'styles/exachan.png" alt="Эксачан" title="Эксачан"></a>
	<a href="http://pravdorubs.ru/"><img src="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'styles/pravdorubs.png" alt="Правдорубы" title="Правдорубы"></a><br>
	<a href="http://exachan.com/exaba/">эксаба 0.9.9</a>
</footer>

</body>
</html>';
}

$user_time = TIME();

$user_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
$user_ip = mysql_real_escape_string($user_ip);

if (isset($_SERVER['HTTP_USER_AGENT'])) {
	$user_browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
	$user_browser = mysql_real_escape_string($user_browser);
} else {
	$user_browser = '';
}

if (isset($_SERVER['HTTP_REFERER'])) {
	$user_referer = htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES);
	$user_referer = mysql_real_escape_string($user_referer);
} else {
	$user_referer = '';
}

if (isset($_COOKIE["post_owner"])) {
	$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
	$post_unique_id = mysql_real_escape_string($post_unique_id);
} else {
	$post_unique_id = '';
}

mysql_query("INSERT INTO statistics (user_time, user_ip, user_browser, user_referer, user_cookies) VALUES ('$user_time', '$user_ip', '$user_browser', '$user_referer', '$post_unique_id')");

?>