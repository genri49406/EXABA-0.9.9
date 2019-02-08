<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include 'config.php';

$show_form = 1;
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
} else {
	$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
	$post_unique_id = mysql_real_escape_string($post_unique_id);
}

if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
}
if (isset($_GET['for'])) {
	$for = (int)$_GET['for'];
}
if (isset($_COOKIE['order_comments'])) {
$order_comments = (int)($_COOKIE['order_comments']);
}

$sql = ("SELECT * FROM post_config WHERE config_post_id = '$id'");
$result = mysql_query($sql, $link);
$num_rows = mysql_num_rows($result);
$row = mysql_fetch_assoc($result);
$comment_limit = $row['comment_limit'];

if (!empty($num_rows) && $comment_limit==0) {
$form_disabled = ' disabled ';
$disabled_text = 'Тред закрыт для постинга';
} else {
$form_disabled = '';
$disabled_text = '';
}

$result = mysql_query("SELECT * FROM board WHERE post_id = '$id'");
$row = mysql_fetch_assoc($result);
$post_id = $row['post_id'];
$post_time = $row['post_time'];
$post_time = date('d '.$month_array[date('n',$post_time)].', Y H:i',$post_time);
$post_title = $row['post_title'];
$post_text = $row['post_text'];
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$img_height = $row['img_height'];
$img_width = $row['img_width'];
$thumb_w = $row['thumb_w'];
$thumb_h = $row['thumb_h'];
$img_size = $row['img_size'];
$post_sec = $row['post_sec'];
$num_rows = $row['post_num'];
$post_of = $row['post_of'];
$post_cookies = $row['post_cookies'];

if (!empty($post_of)) {
$op_result = mysql_query("SELECT post_cookies FROM board WHERE post_id = '$post_of'", $link);
$row = mysql_fetch_assoc($op_result);
$op_cookies = $row['post_cookies'];
} else {
$op_cookies = $post_cookies;
}


if (empty($row)) {
unset($show_form);
$page_title = 'Тред или пост №'.$id.' не найден.';
$error = 'Он был удален или он настолько унылый, что не хочет отобразится.<br>Не останавливайся на этом Анон! - у нас еще много других <del>унылых</del> интересных тредов!';
include 'functions/404.php';
exit;
}

if (empty($post_title)) {
$page_title = 'Тред №'.$post_id;

} else {
$page_title = $post_title;
}

if(empty($num_rows)) {
$last_comment_id = $post_id;
} else {
$last_comment_result = mysql_query("SELECT post_id FROM board WHERE post_of = '$id' ORDER BY post_id DESC limit 1");
$row = mysql_fetch_assoc($last_comment_result);
$last_comment_id = $row['post_id'];
mysql_free_result($last_comment_result);
}

include 'functions/html.php';

text_formating();

if ($post_cookies == $post_unique_id) {
$post_edit =' <span title="Редактировать" class="edit_image" onclick="edit_comment(\''.$post_id.'\')">&nbsp;</span>';
$op = '1';
} else {
$post_edit ='';
$op = '0';
}

$ans = ' <a href="#" onclick="reply(this); return false">ответить</a>(<span id="ans">'.$num_rows.'</span>) ';

echo '
<div class="post" id="c'.$post_id.'">
'.$post_title, $post_img, $video.'<p>'.$post_text.'</p>
<span class="info">Тред №'.$post_id, $ans, $post_time, $replays, $post_edit.'</span>
</div>
';

if (!empty($post_sec) && isset($order_comments)) {
$order_by = 'DESC';
echo '<div class="extra_setting" id="extra_setting"><a href="#footer" id="go_down">Вниз</a><span title="Хочу чтобы снизу"><b onclick="set_order(this, \'none\');">Новые комментарии отображаются сверху</b></span></div>
<div id="new_comments">
<script type="text/javascript">
post_id = '.$post_id.';
last_comment_id = '.$last_comment_id.';
order_comments = 1;
start_timer();
</script>
</div>
';
} else if (!empty($post_sec) && !isset($order_comments)) {
$order_by = 'ASC';
echo '<div class="extra_setting" id="extra_setting"><a href="#footer" id="go_down">Вниз</a><span title="Хочу чтобы сверху"><b onclick="set_order(this, \'1\');">Новые комментарии отображаются снизу</b></span></div>'."\r\n";
$new_comments_down = '
<div id="new_comments">
<script type="text/javascript">
post_id = '.$post_id.';
last_comment_id = '.$last_comment_id.';
order_comments = 0;
start_timer();
</script>
</div>
';
}

$result = mysql_query("SELECT * FROM board WHERE post_of = '$post_id' ORDER BY post_id $order_by LIMIT $page, $limit", $link);
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
$post_of = $row['post_of'];
$post_cookies = $row['post_cookies'];
$show_op = $row['post_op'];

	if (isset($_COOKIE[$post_id])) {
		$hide_style = ' style="display: none;"';
		$show_hide =' <span title="Показать" class="show_image"  onclick="show_hide(this)"></span>';
	} else {
		$hide_style = '';
		$show_hide =' <span title="Скрыть" class="hide_image"  onclick="show_hide(this)"></span>';
	}

if ($post_cookies == $post_unique_id) {
$hide_style = '';
$show_hide = '';
}

text_formating();

if (!empty($post_text)) {
$post_text ='<p'.$hide_style.'>'.$post_text.'</p>'."\r\n";
} else {
$post_text ='';
}

$ans = ' <a href="#" onclick="reply(this); return false;">ответить</a> ';

if ($show_op != 0 && $post_cookies == $op_cookies) {
$show_opa ='&nbsp;&nbsp;<b>ОП</b>&nbsp;&nbsp;';
} else {
$show_opa ='';
}

if ($post_cookies == $post_unique_id) {
$post_edit =' <span title="Редактировать" class="edit_image" onclick="edit_comment(\''.$post_id.'\')">&nbsp;</span>';
$post_ban = '';
} elseif ($op == '1' && $post_cookies != $post_unique_id && $OP_moderator != 0) {
$post_edit =' <span title="Удалить" class="delete_image" onclick="delete_comment(\''.$post_id.'\')">&nbsp;</span>';
$post_ban =' <span title="Забанить" class="ban_image" onclick="ban(\''.$post_id.'\')">&nbsp;</span>';
} else {
$post_edit ='';
$post_ban = '';
}

echo '
<div class="comment" id="c'.$post_id.'">
'.$post_title, $post_img, $video, $post_text.'<span class="info">Комментарий №'.$post_id, $ans, $post_time, $show_opa, $replays, $show_hide, $post_ban, $post_edit.'</span>
</div>
';
}

if (isset($new_comments_down)) {
	echo $new_comments_down;
}

mysql_free_result($result);
if($num_rows > $limit) {
pages($num_rows, $limit, $id, '');
}

footer();
mysql_close($link);
?>