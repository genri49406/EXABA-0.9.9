<?php
mb_internal_encoding("UTF-8");
include '../config.php';

$post_id = (int)$_POST['post_id'];
$thread = $post_id;
$id = (int)$_POST['id'];

$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
$post_unique_id = mysql_real_escape_string($post_unique_id);

if ($thread != 0 ) {
$result = mysql_query("SELECT * FROM board WHERE post_id ='$thread' AND post_sec > '0'", $link);
$row = mysql_fetch_assoc($result);
$thread_cookies = $row['post_cookies'];
}

$result = mysql_query("SELECT * FROM board WHERE post_id ='$id'");
$row = mysql_fetch_assoc($result);
$post_sec = $row['post_sec'];
$post_of = $row['post_of'];

if($post_sec > 0) {
$sql = "SELECT * FROM board WHERE post_of = '$id' ORDER BY post_id ASC limit 1";
} else {
$sql = "SELECT * FROM board WHERE post_of = '$post_id' and post_id > '$id' ORDER BY post_id ASC limit 1";
}

$result = mysql_query($sql, $link);
$num_rows = mysql_num_rows($result);

if (empty($num_rows)) {
mysql_close($link);
exit;
}

$row = mysql_fetch_assoc($result);
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
$post_op = $row['post_op'];

text_formating();

if (!empty($post_text)) {
$post_text ='<p>'.$post_text.'</p>'."\r\n";
} else {
$post_text ='';
}

$ans = ' <a href="#" onclick="reply(this); return false">ответить</a> ';

if ($post_op == 1) {
$show_opa ='&nbsp;&nbsp;<b>ОП</b>&nbsp;&nbsp;';
} else {
$show_opa ='';
}

if ($thread_cookies == $post_unique_id) {
$op = 1;
} else {
$op = 0;
}

$hs =' <span title="Скрыть" class="hide_image"  onclick="show_hide(this)"></span> ';

if ($post_cookies == $post_unique_id && $thread > 0) {
$post_edit ='<span title="Редактировать" class="edit_image" onclick="edit_comment(\''.$post_id.'\')"></span>';
$post_ban = '';
$hs = "";
} elseif ($op == '1' && $post_cookies != $post_unique_id && $OP_moderator != 0) {
$post_edit ='<span title="Удалить" class="delete_image" onclick="delete_comment(\''.$post_id.'\')"></span>';
$post_ban ='<span title="Забанить" class="ban_image" onclick="ban(\''.$post_id.'\')"></span>';
$hs = "";
} else {
$post_edit ='';
$post_ban = '';
}

echo $post_title, $post_img, $video, $post_text.'<span class="info">Комментарий №'.$post_id.'<span id="id">'.$post_id.'</span>&nbsp;&nbsp;'.$ans.'&nbsp;&nbsp;'.$post_time, $show_opa, $post_ban, $post_edit, $hs.'</span>
';
mysql_close($link);
mysql_free_result($result);
?>