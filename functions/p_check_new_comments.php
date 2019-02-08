<?php
mb_internal_encoding("UTF-8");
include '../config.php';

$id = (int)$_POST['id'];

$result = mysql_query("SELECT * FROM board WHERE post_id > '$id' AND post_sec != '-1' ORDER BY post_id ASC limit 1", $link);
$num_rows = mysql_num_rows($result);
if (mysql_num_rows($result) == 0 || empty($id)) {
mysql_close($link);
mysql_free_result($result);
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

$ans = ' <a href="post.php?id='.$post_id.'" title="Быстрый ответ в однопотоке" onclick="reply(this); document.getElementById(\'f1\').style.display = \'block\'; document.getElementById(\'replay_id\').value = \''.$post_id.'\'; return false;" style="float: right;">Быстрый ответ</a> ';


if ((int)($_COOKIE[$post_id] != 1) && (int)($_COOKIE[$post_of] != 1)) {
echo "\r\n".$post_title, $post_img, $post_text.'
<span class="info">Комментарий №'.$post_id.'<span id="id">'.$post_id.'</span>'.$url, $post_time, $ans.'</span>'."\r\n";
}

mysql_free_result($result);
mysql_close($link);
?>