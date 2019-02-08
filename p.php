<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include 'config.php';

$sec = 10;
$page_title = 'Однопоток';
$page_description = 'Все треды и комменты чана в одном потоке';
$limit = 75;

if (!isset($_COOKIE['post_owner'])) {
	$length = 17;
	$characters = str_shuffle('123456789aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ');
	$string = "";

		for ($i = 0; $i < $length; $i++) {
			$string .= $characters[mt_rand(0, 60)];
		}

	$post_unique_id = 'ver03'.TIME().$string;
	setcookie('post_owner', $post_unique_id, TIME()+60*60*24*365, '/', $domain, false);
}

if (isset($_GET['page']) && (int)$_GET['page'] > 0) {
$page = (int)$_GET['page'];
} else {
$page = 0;
}

if (isset($_GET['order']) && (int)$_GET['order'] > 0) {
	$order = (int)$_GET['order'];
} else {
	$order = 0;
}

include 'functions/html.php';

if ($allow_url_fopen > 0) {
$www_input ='<input type="text" class="form_url_file" name="www_file" maxlength="400" placeholder="URL адрес изображения">'."\r\n";
} else {
$www_input = '';
}

echo '
<form id="f1" action="functions/new_post.php" method="post" enctype="multipart/form-data" style="display: none;">
<div class="form">
<span class="form_name" id="form_name" onclick="document.getElementById(\'f1\').style.display = \'none\';"></span>
<div class="form_smiles" id="smiles_1"><div id="banner"></div></div>
<input class="form_title" type="text" name="title" maxlength="70"  placeholder="Заголовок">
<input type="text" name="email" size="35" maxlength="75" value="" style="display: none;">
<span class="form_BB_codes" onclick="BB_code(\'f1\',\'[b]\',\'[\/b]\');" title="Полужирный"><strong>B</strong></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[i]\',\'[\/i]\');" title="Курсив"><em>I</em></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[s]\',\'[\/s]\');" title="Зачеркнутый"><del>S</del></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[u]\',\'[\/u]\');" title="Подчеркнутый"><ins>U</ins></span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[spoiler]\',\'[\/spoiler]\');" title="Спойлер">Sp</span><span class="form_BB_codes" onclick="BB_code(\'f1\',\'[quote]\',\'[\/quote]\');" title="Цитата">Q</span><span class="form_BB_codes" onclick="show_smiles(\'1\',\'1\')" title="Смайлы">:)</span>
<textarea name="text" id="form_textarea" rows="8" cols="55"></textarea>
'.$www_input.'<input type="file" class="form_pc_file" name="img">
<input type="hidden" id="replay_id" name="id" value="">
<input type="submit" class="form_submit" value="Отправить">
</div>
</form>
';

echo '
<div class="menu" id="extra_setting">
	<nav>
		Сортировка:
		<ul>
				<li><a href="?order=0">По ID</a></li>
				<li><a href="?order=2">По тредам</a></li>
				<li><a href="?order=1">По бампу тредов</a></li>
				<li><a href="?order=3">По пикчам</a></li>
				<li><a href="?order=4">Удаленные (Корзина)</a></li>
				<li><a href="links.php">Откуда к нам пришли</a></li>
		</ul>
	</nav>
</div>
';

if($order == '1') {
$what = "WHERE post_sec > '0' ORDER BY post_bump";
} else if($order == '2') {
$what = "WHERE post_sec > '0' ORDER BY post_id";
} else if($order == '3') {
$what = "ORDER BY post_img";
} else if($order == '4') {
$what = "WHERE post_sec = '-1' ORDER BY post_id";
} else {
$what = "WHERE post_sec != '-1' ORDER BY post_id";
}

$sql = "SELECT * FROM board $what";
$result = mysql_query($sql, $link);
$num_rows = mysql_num_rows($result);
mysql_free_result($result);

if(empty($num_rows)) {
$last_comment_id = 0;
} else {
$last_comment_result = mysql_query("SELECT post_id FROM board WHERE post_sec != '-1' ORDER BY post_id DESC limit 1");
$row = mysql_fetch_assoc($last_comment_result);
$last_comment_id = $row['post_id'];
mysql_free_result($last_comment_result);
}
echo '
<div id="new_comments">
<script type="text/javascript">
post_id = 0;
last_comment_id = '.$last_comment_id.';
order_comments = 1;
start_timer();
</script>
</div>
';

$sql = "SELECT * FROM board $what DESC LIMIT $page, $limit";

$result = mysql_query($sql, $link);

while ($row = mysql_fetch_assoc($result)) {

$post_id = $row['post_id'];
$post_title = $row['post_title'];
$post_text = $row['post_text'];
$post_time = $row['post_time'];
$post_time = date('d '.$month_array[date('n',$post_time)].', Y H:i',$post_time);
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$img_height = $row['img_height'];
$img_width = $row['img_width'];
$thumb_w = $row['thumb_w'];
$thumb_h = $row['thumb_h'];
$img_size = $row['img_size'];
$post_num = $row['post_num'];
$post_of = $row['post_of'];

$text_length = mb_strlen($post_text);
if ($text_length > '700') {
$post_text = mb_substr($post_text, 0, 700);

if (empty($post_of)) {
	$moar = $post_id;
} else {
	$moar = $post_of;
}

$post_text = $post_text.'&#8230; <a href="post.php?id='.$moar.'#c'.$post_id.'">Далее</a>&#8230;';
}

text_formating();

if (!empty($post_text)) {
$post_text ='<p>'.$post_text.'</p>';
} else {
$post_text ='';
}

if (empty($post_of)) {
$url = ' <a href="post.php?id='.$post_id.'">Ответов: '.$post_num.'</a> ';
} else {
$url = ' <a href="post.php?id='.$post_of.'#c'.$post_id.'">Перейти в тред</a> ';
}

$ans = ' <a href="#" title="Быстрый ответ в однопотоке" onclick="reply(this); document.getElementById(\'f1\').style.display = \'block\'; document.getElementById(\'replay_id\').value = \''.$post_id.'\'; return false;" style="float: right;">Быстрый ответ</a> ';

if (!isset($_COOKIE[$post_id]) && !isset($_COOKIE[$post_of])) {
echo '
<div class="post" id="c'.$post_id.'">
'.$post_title, $post_img, $post_text, $video.'
<span class="info">№'.$post_id, $url, $post_time, $replays, $ans.'</span>
</div>
';
}

}

mysql_free_result($result);

if($num_rows > $limit) {
	pages($num_rows, $limit, '', '');
}

footer();
mysql_close($link);
?>