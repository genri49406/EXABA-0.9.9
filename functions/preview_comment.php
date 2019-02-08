<?php
mb_internal_encoding("UTF-8");
include '../config.php';

$id = (int)$_POST['id'];

$result = mysql_query("SELECT * FROM board WHERE post_id = '$id' LIMIT 1", $link);
$row = mysql_fetch_assoc($result);

if (mysql_num_rows($result) == 0) {
echo '
<h2>Комментарий не найден.</h2>
<p>Комментарий не найден в базе данных, вероятно он был удален.</p>
<span class="info">Комментарий №'.$id.'</span>
';
mysql_close($link);
exit;
}

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

text_formating();

if (!empty($post_text)) {
$post_text ='<p>'.$post_text.'</p>'."\r\n";
} else {
$post_text ='';
}

echo $post_title, $post_img, $video, $post_text.'
<span class="info">Комментарий №'.$post_id.' '.$post_time.'</span>
';

mysql_free_result($result);
mysql_close($link);
?>