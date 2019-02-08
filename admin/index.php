<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include '../config.php';

$page_title = "Admin";
$limit = 75;
$num_rows = 0;

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

$last_comment_result = mysql_query("SELECT post_id FROM board WHERE post_sec != '-1' ORDER BY post_id DESC limit 1");
$row = mysql_fetch_assoc($last_comment_result);
$last_comment_id = $row['post_id'];
mysql_free_result($last_comment_result);

echo '
<div class="menu" id="menu">
		<nav>
			<ul>
				<li><a href="login.php?logout=1" title="Удалить куку админа и выйти">Выйти</a></li>
				<li><a href="#" id="c0" onclick="ban_admin(\'0\', \'0\');">Очистить баны</a></li>
				<li><a href="#" onclick="optimize(this);">Оптимизировать борду</a></li>
				<li><a href="functions/db_synchronization.php">Синхронизация базы данных</a></li>
				<li><a href="sec.php">Разделы чана</a></li><br><br>
				<li><a href="?order=0">По ID</a></li>
				<li><a href="?order=2">По тредам</a></li>
				<li><a href="?order=1">По бампу тредов</a></li>
				<li><a href="?order=3">По пикчам</a></li>
				<li><a href="?order=4">Удаленные (Корзина)</a></li>
			</ul>
		</nav>
	</div>

<div id="new_comments">
<script type="text/javascript" src="functions/scripts.js?version=01"></script>
<script type="text/javascript">
post_id = 0;
last_comment_id = '.$last_comment_id.';
order_comments = 1;
start_timer();
</script>
</div>
';

if($order == 1) {
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

$result = mysql_query("SELECT * FROM board $what DESC LIMIT $page, $limit", $link);
while ($row = mysql_fetch_assoc($result)) {
$post_id = $row['post_id'];
$post_title = $row['post_title'];
$post_text = $row['post_text'];
$post_time = $row['post_time'];
$post_time = date('d F,Y H:i',$post_time);
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$img_height = $row['img_height'];
$img_width = $row['img_width'];
$thumb_w = $row['thumb_w'];
$thumb_h = $row['thumb_h'];
$img_size = $row['img_size'];
$post_num = $row['post_num'];
$post_sec = $row['post_sec'];
$post_of = $row['post_of'];
$post_bump = $row['post_bump'];

$text_length = mb_strlen($post_text);
if ($text_length > '700') {
$post_text = mb_substr($post_text, 0, 700);
$post_text = $post_text.'&#8230; <a href="post.php?id='.$post_id.'#'.$post_num.'">Далее</a>&#8230;';
}

text_formating();

$ban = '<span class="ban_image" onclick="ban_admin(\''.$post_id.'\', \'0\')" title="Забанить в пределах треда"> </span> ';
$total_ban = '<span class="ban_image" onclick="ban_admin(\''.$post_id.'\', \'1\')" title="Забанить по всей доске"> </span> ';

if (empty($post_sec)) {

	$type = 'Комментарий №'.$post_id.' ';
	$delete = ' <span class="delete_image" onclick="delete_comment(\''.$post_id.'\')" title="Удалить комментарий без возможности восстановления"> </span> ';
	$url = ' <a href="../post.php?id='.$post_of.'">Перейти в тред</a> ';
	$move_to = '';

} else {

	$type = 'Тред №'.$post_id.' ';
	$delete = '<span class="delete_image" onclick="delete_post_admin(\''.$post_id.'\')" title="Удалить тред без возможности восстановления"> </span> ';
	$url = ' <a href="../post.php?id='.$post_id.'#'.$post_num.'">Ответов: '.$post_num.'</a> ';

		if ($post_sec == -1) {
			$sec_title ='Удаленные';
		} else {
			$title_result = mysql_query("SELECT * FROM board_config WHERE sec_id = '$post_sec'", $link);
			$myrow = mysql_fetch_assoc($title_result);
			$sec_title = $myrow['sec_title'];
			mysql_free_result($title_result);
		}
$move_to = '';
$move_to .= '
<span id="move">
<select id="edit_'.$post_id.'" class="edit_select" onchange="move_select(\''.$post_id.'\', this.options[selectedIndex].value);">
<option value="0" selected="selected">'.$sec_title.', перенести в:</option>'."\r\n";

$section_result = mysql_query("SELECT * FROM board_config ORDER BY sort_index ASC", $link);
while ($row = mysql_fetch_assoc($section_result)) {
$mysec_id = $row['sec_id'];
$sec_title = $row['sec_title'];
$move_to .= '<option value="'.$mysec_id.'">'.$sec_title.'</option>'."\r\n";
}
mysql_free_result($section_result);
$move_to .= '<option value="-1">Удаленные</option>
</select>
</span>
';
}

echo '
<div class="post" id="c'.$post_id.'">
'.$post_title, $post_img, $video.'<p>'.$post_text.'</p>
<div class="info">'.$type, $url, $post_time, $move_to, $total_ban,  $delete, $ban.'</div>
</div>
';
}

$num_rows = mysql_result(mysql_query("SELECT COUNT(*) FROM board $what"),0);
pages($num_rows, $limit, '', $order);

mysql_free_result($result);
mysql_close($link);
footer();
?>