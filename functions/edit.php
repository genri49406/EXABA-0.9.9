<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include '../config.php';

$post_id = (int)$_GET['id'];

if (isset($_COOKIE['post_owner'])) {
$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
$post_unique_id = mysql_real_escape_string($post_unique_id);
} else {
mysql_close($link);
echo 'ups';
exit;
}

if ($allow_url_fopen > 0) {
$www_input ='<input type="text" class="form_url_file" name="www_file" maxlength="400" placeholder="URL адрес изображения">'."\r\n";
} else {
$www_input = '';
}

$sql = ("SELECT * FROM board WHERE post_id = '$post_id' AND post_cookies = '$post_unique_id'");
$result = mysql_query($sql, $link);
$num_rows = mysql_num_rows($result);

if(empty($num_rows)) {
mysql_close($link);
echo 'ups';
exit;
}

$row = mysql_fetch_assoc($result);
$post_title = $row['post_title'];
$post_text = $row['post_text'];
$post_sec = $row['post_sec'];
$img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$img_ext = $row['img_ext'];
$post_of = $row['post_of'];
$post_op = $row['post_op'];

$post_title = preg_replace( "#\<i>(.+?)\</i>#is", "\\1", $post_title );
$post_text = preg_replace( "#\<b>(.+?)\</b>#is", "[b]\\1[/b]", $post_text );
$post_text = preg_replace( "#\<i>(.+?)\</i>#is", "[i]\\1[/i]", $post_text );
$post_text = preg_replace( "#\<ins>(.+?)\</ins>#is", "[u]\\1[/u]", $post_text );
$post_text = preg_replace( "#\<del\>(.+?)\</del\>#is", "[s]\\1[/s]", $post_text );
$post_text = preg_replace( "#\<span\>(.+?)\</span\>#is", "[spoiler]\\1[/spoiler]", $post_text );
$post_text = preg_replace( "#\<span\>(.+?)\</span\>#is", "%%\\1%%", $post_text );
$post_text = preg_replace( '#\<a href="(.+?)\">(.+?)\</a\>#is', '\\1', $post_text);

if (!empty($post_img)) {
$post_img ='<a href="'.$img_dir.'/'.$post_img.'_big.'.$img_ext.'"><img src="'.$img_dir.'/'.$post_img.'_small.'.$img_ext.'" alt="" class="text_image"></a>';
$delete_checkbox = '&nbsp;&nbsp;&nbsp;Удалить изображение: <input type="checkbox" name="delete_img" value="1">';
} else {
$post_img ='&nbsp;';
$delete_checkbox ='';
}

$sql = ("SELECT * FROM post_config WHERE config_post_id = '$post_id'");
$result = mysql_query($sql, $link);
$num_rows = mysql_num_rows($result);
$row = mysql_fetch_assoc($result);
$comment_limit = $row['comment_limit'];

if (empty($post_of)) {
echo'
<form id="f'.$post_id.'" action="functions/edit_thread.php" method="post" enctype="multipart/form-data">
<p>
<span class="form_smiles" id="smiles_'.$post_id.'">'.$post_img.'</span>
<input class="form_title" type="text" name="title" value="'.$post_title.'" maxlength="70" placeholder="Заголовок">
<span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[b]\',\'[\/b]\');" title="Полужирный"><strong>B</strong></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[i]\',\'[\/i]\');" title="Курсив"><em>I</em></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[s]\',\'[\/s]\');" title="Зачеркнутый"><del>S</del></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[u]\',\'[\/u]\');" title="Подчеркнутый"><ins>U</ins></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[spoiler]\',\'[\/spoiler]\');" title="Спойлер">Sp</span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[quote]\',\'[\/quote]\');" title="Цитата">Q</span><span class="form_BB_codes" onclick="show_smiles(\''.$post_id.'\',\'1\')" title="Смайлы">:)</span>
<textarea name="text" id="edit_textarea" rows="8" cols="55">'.$post_text.'</textarea>
'.$www_input.'<input type="file" class="form_pc_file"  name="img">
<input type="submit" class="form_submit" value="Отправить">
<input type="hidden" name="id" value="'.$post_id.'">
';

if ($post_sec > 0) {
echo '<span class="info">Мах.комментов в минуту:
<select class ="edit_select" name ="comment_limit">';

for ($i = 10; $i >= 0; $i--) {
if ($i == $comment_limit && (!empty($row))) {
echo'<option value="'.$i.'" selected="selected">'.$i.'</option>
';
} else {
echo'<option value="'.$i.'">'.$i.'</option>
';
}
}

echo'</select>
<span id="move">
<select id="edit_'.$post_id.'" class="edit_select" onchange="move_select(\''.$post_id.'\', this.options[selectedIndex].value);">
<option value="0" selected="selected">Перенести в:</option>';

$result = mysql_query("SELECT * FROM board_config ORDER BY sort_index ASC", $link);
while ($row = mysql_fetch_assoc($result)) {
$sec_id = $row['sec_id'];
$sec_title = $row['sec_title'];
echo '<option value="'.$sec_id.'">'.$sec_title.'</option>'."\r\n";
}
echo '<option value="-1">Удаленные</option>
</select></span>
<a href="" onclick="remove_ban(\''.$post_id.'\'); return false;">Очистить баны</a>
</span>
</p>
</form>
';
} else {
echo '<input type="hidden" name="comment_limit" value="10"><br>
</p>
</form>
';
}
} else {

$result = mysql_query("SELECT * FROM board WHERE post_id = '$post_of'", $link);
$row = mysql_fetch_assoc($result);
$op_cookies = $row['post_cookies'];

if ($op_cookies == $post_unique_id) {

if ($post_op == 1) {
$show_op = ' <label class="op_input">Показать ОП\'а: <input type="checkbox" name="show_op" value="1" checked></label>';
} else {
$show_op = ' <label class="op_input">Показать ОП\'а: <input type="checkbox" name="show_op" value="1"></label>';
}

} else {
$show_op ='';
}

echo'
<form id="f'.$post_id.'" action="functions/edit_post.php" method="post" enctype="multipart/form-data">
<div class="text">
<span class="form_smiles" id="smiles_'.$post_id.'">'.$post_img.'</span>
<input class="form_title" type="text" name="title" value="'.$post_title.'" maxlength="70" placeholder="Заголовок">
<span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[b]\',\'[\/b]\');" title="Полужирный"><strong>B</strong></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[i]\',\'[\/i]\');" title="Курсив"><em>I</em></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[s]\',\'[\/s]\');" title="Зачеркнутый"><del>S</del></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[u]\',\'[\/u]\');" title="Подчеркнутый"><ins>U</ins></span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[spoiler]\',\'[\/spoiler]\');" title="Спойлер">Sp</span><span class="form_BB_codes" onclick="BB_code(\'f'.$post_id.'\',\'[quote]\',\'[\/quote]\');" title="Цитата">Q</span><span class="form_BB_codes" onclick="show_smiles(\''.$post_id.'\',\'1\')" title="Смайлы">:)</span>'.$show_op.'
<textarea name="text" id="edit_textarea" rows="8" cols="55">'.$post_text.'</textarea>
'.$www_input.'<input type="file" class="form_pc_file"  name="img">
<input type="submit" class="form_submit" value="Отправить">
<input type="hidden" name="id" value="'.$post_id.'">
<div class="info">'.$delete_checkbox.'<span title="Удалить" class="delete_image" onclick="delete_comment(\''.$post_id.'\')"></span>&nbsp;</div>
</div>
</form>
';
}
mysql_close($link);

?>