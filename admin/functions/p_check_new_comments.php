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
			echo '<a href="login.php">Пожалуйста авторизируйтесь</a>';
			exit;
		}

} else {
	mysql_close($link);
	echo '<a href="login.php">Пожалуйста авторизируйтесь</a>';
	exit;
}

$id = (int)$_POST['id'];

$result = mysql_query("SELECT * FROM board WHERE post_id > '$id' AND post_sec <> '-1' ORDER BY post_id ASC limit 1", $link);
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
if ($text_length > '300') {
$post_text = mb_substr($post_text, 0, 300);
$post_text = $post_text.'... <a href="../post.php?id='.$post_id.'#'.$post_num.'">Далее</a>...';
}

text_formating();

$ban = '<span class="ban_image" onclick="ban_admin(\''.$post_id.'\', \'0\')" title="Забанить в пределах треда"> </span> ';

$total_ban = '<span class="ban_image" onclick="ban_admin(\''.$post_id.'\', \'1\')" title="Забанить по всей доске"> </span> ';


if (empty($post_sec)) {
$type = 'Комментарий №'.$post_id.'<span id="id">'.$post_id.'</span> ';
$delete = '<span class="delete_image" onclick="delete_comment(\''.$post_id.'\')" title="Удалить без возможности восстановления"> </span>';
$url = ' <a href="../post.php?id='.$post_of.'">Перейти в тред</a>';
$move_to = '';
} else {
$type = 'Тред №'.$post_id.'<span id="id">'.$post_id.'</span> ';
$delete = '<span class="delete_image" onclick="delete_post_admin(\''.$post_id.'\')" title="Удалить без возможности восстановления"> </span> ';
$url = ' <a href="../post.php?id='.$post_id.'#'.$post_num.'">Ответов: '.$post_num.'</a> ';

		if ($post_sec == -1) {
			$sec_title ='Удаленные';
		} else {
			$my_sec = mysql_query("SELECT * FROM board_config WHERE sec_id = '$post_sec'", $link);
			$row = mysql_fetch_assoc($my_sec);
			$sec_title = $row['sec_title'];
		}

$move_to .= '
<span id="move">
<select id="edit_'.$post_id.'" class="edit_select" onchange="move_select(\''.$post_id.'\', this.options[selectedIndex].value);">
<option value="0" selected="selected">'.$sec_title.', перенести в:</option>'."\r\n";

$result = mysql_query("SELECT * FROM board_config ORDER BY sort_index ASC", $link);
while ($row = mysql_fetch_assoc($result)) {
$sec_id = $row['sec_id'];
$sec_title = $row['sec_title'];
$move_to .= '<option value="'.$sec_id.'">'.$sec_title.'</option>'."\r\n";
}

$move_to .= '<option value="-1">Удаленные</option>
</select>
</span>
';
}

echo '
'.$post_title,$post_img.'
<p>'.$post_text.'</p>
<div class="info">'.$type, $url, $post_time, $move_to, $total_ban,  $delete, $ban.' </div>
';

mysql_free_result($result);
?>