<?php
mb_internal_encoding("UTF-8");
include '../config.php';

if (!empty($_POST['email'])) {
	exit;
}

if (!isset($_COOKIE["post_owner"])) {
$page_title = 'Отсутствуют кукис';
$error = 'Включите поддержку кукис в браузере.';
include '404.php';
exit;
}

$post_time = TIME();
$rnd_name = "";

$id = (int)$_POST['id'];

$post_title = trim($_POST['title']);
$post_title = preg_replace('# +#is', ' ', $post_title);
$post_title = mb_substr($post_title, 0, 70);
$post_title = htmlspecialchars($post_title, ENT_QUOTES);

if (preg_match('/(саж|Саж|sage|Sage)/', $post_title)) {
$sage = 1;
} else {
$sage = 0;
}

$post_text = trim($_POST['text']);
$post_text = preg_replace('# +#is', ' ', $post_text);
$post_text = mb_substr($post_text, 0, 5000);
$post_text = htmlspecialchars($post_text, ENT_QUOTES);

if (isset($_POST['for'])) {
$for = (int)$_POST['for'];
} else {
$for = 0;
}

if (isset($_POST['show_op'])) {
$show_op = (int)$_POST['show_op'];
} else {
$show_op = 0;
}

$post_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
$post_ip = mysql_real_escape_string($post_ip);
$post_browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);
$post_browser = mysql_real_escape_string($post_browser);
$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
$post_unique_id = mysql_real_escape_string($post_unique_id);


$result = mysql_query("SELECT * FROM board WHERE post_id ='$id'", $link);
$row = mysql_fetch_assoc($result);
$post_of = $row['post_of'];

if ($post_of == 0) {
$tred_id = $id;
} else {
$tred_id = $post_of;
}

$result = mysql_query("SELECT * FROM black_list WHERE
ban_id = '$tred_id' AND ban_ip ='$post_ip'
OR
ban_id = '$tred_id' AND ban_cookies = '$post_unique_id'
OR
ban_id = '0' AND ban_ip = '$post_ip'
OR
ban_id = '0' AND ban_cookies = '$post_unique_id'
", $link);

$row = mysql_fetch_assoc($result);
$moderator = $row['moderator'];

if (mysql_num_rows($result) > 0) {

if ($moderator ==0) {
$page_title = 'Упс';
$error = 'Вы не можете комментировать в этом треде - ОП треда заблокировал вас.';
include '404.php';
exit;
} else {
$page_title = 'Упс';
$error = 'Вы не можете комментировать в этом треде - Администрация борды заблокировала вас.';
include '404.php';
exit;
}

}

$result = mysql_query("SELECT * FROM post_config WHERE config_post_id = '$tred_id'", $link);
$row = mysql_fetch_assoc($result);
$comment_limit = $row['comment_limit'];

if (mysql_num_rows($result) > 0) {
$my_time = TIME()-60;

$my_result = mysql_query("SELECT * FROM board WHERE post_time > '$my_time' AND post_of = '$id'", $link);
if (mysql_num_rows($my_result) >= $comment_limit) {
$page_title = 'Лимит комментариев в минуту';
$error = 'ОП установил ограничение на количество новых комментариев в минуту: '.$comment_limit;
include '404.php';
exit;
}
}

$my_time = TIME()-600;
$my_result = mysql_query("SELECT post_text FROM board WHERE post_time > '$my_time' AND post_text <> ''", $link);

while ($row = mysql_fetch_assoc($my_result)) {
$test_text = $row['post_text'];
if ($test_text == $post_text) {
$page_title = 'Повторяющийся текст';
$error = 'Комментарий с таким текстом уже существует!';
include '404.php';
exit;
}
}

$post_title = mysql_real_escape_string($post_title);
$post_text = mysql_real_escape_string($post_text);

$my_time = TIME()-60;

$my_result = mysql_query("SELECT * FROM board WHERE post_time > '$my_time' AND post_ip = '$post_ip' AND post_sec = '0'", $link);
if (mysql_num_rows($my_result) > '2') {
$page_title = 'Превышение лимита добавления комментариев';
$error = 'Чтобы как-то предотвращать «Вайп»,  существует ограничение на добавление новых комментариев - не более трех в минуту.';
include '404.php';
exit;
}

$result = mysql_query("SELECT * FROM board WHERE post_id ='$id'", $link);
$row = mysql_fetch_assoc($result);
$post_sec = $row['post_sec'];
$post_of = $row['post_of'];

if (mysql_num_rows($result) == 0) {
$page_title = 'Ошибка 404 Not Found';
$error = 'Тред №'.$id.' не найден, он был удален или он настолько унылый, что не хочет отобразится.<br>Не останавливайся на этом Анон! - у нас еще много других <del>унылых</del> интересных тредов!';
include '404.php';
exit;
}

$bump_result = mysql_query("SELECT * FROM board WHERE post_id = '$tred_id' AND post_sec = '1'", $link);
$row = mysql_fetch_assoc($bump_result);
$last_bump = $row['post_bump'];

$my_time = TIME() - 4320000;
if (mysql_num_rows($bump_result) > 0 && $last_bump < $my_time) {
$sage = 1;
}

$www_file = $_POST['www_file'];
$pc_file = $_FILES['img'];
$img_name = '../img/tmp_file/'.TIME() . rand(100, 999);

if (!empty($www_file) && preg_match('#^(https?://)#i', $www_file)) {

	if (!copy($www_file, $img_name)) {
	$img_name = '';
	$page_title = 'Ошибка загрузки';
	$error = 'Неудалось загрузить изображение, возможно удаленный сервер запрещает прямое скачивание изображения или неправильный адрес.';
	include '404.php';
	exit;
	}

} elseif (is_uploaded_file($_FILES['img']['tmp_name'])) {
	move_uploaded_file($_FILES['img']['tmp_name'], $img_name);
} else {
	$img_name = '';
}

if ($img_name <> '') {
ini_set('memory_limit', '-1');
$rnd_name = $post_time . rand(100, 999);

$img_hash = md5_file($img_name);
$hash_result = mysql_query("SELECT img_hash FROM img WHERE img_hash = '$img_hash' ORDER BY img_hash DESC LIMIT 500", $link);
if (mysql_num_rows($hash_result) > 0) {
$page_title = 'Идентичное изображение';
$error = 'Такое изображение уже существует.<br>Пожалуйста, выберите другое.';
include '404.php';
exit;
}

if (exif_imagetype($img_name) == IMAGETYPE_GIF) {
	$img_ext = "gif";
} else if (exif_imagetype($img_name) == IMAGETYPE_PNG) {
	$img_ext = "png";
} else if (exif_imagetype($img_name) == IMAGETYPE_JPEG) {
	$img_ext = "jpg";
} else {
	$img_ext = "";
}

if ($img_ext == "gif") {

	$img_size = round(filesize($img_name) / 1024);
	list($width, $height) = getimagesize($img_name);

	if ($width > $thumb_w || $height > $thumb_h) {

	$ratio = $width/$height;
	if ($thumb_w/$thumb_h > $ratio) {
	$thumb_w = floor($thumb_h*$ratio);
	} else {
	$thumb_h = floor($thumb_w/$ratio);
	}

	} else {
	$thumb_w = $width;
	$thumb_h = $height;
	}

	if ($ImageMagik > 0) {
		$secure_name = escapeshellarg($img_name);
		exec("'$ImageMagik_path'convert $secure_name -coalesce -sample 200x200\> ../'$img_dir'/'$rnd_name'_small.gif");
		$img_ext = "gif";
	} else {
		$source = imagecreatefromgif($img_name);
		$new_img = imagecreatetruecolor($thumb_w, $thumb_h);

		$trnprt_indx = imagecolortransparent($source);

			if ($trnprt_indx >= 0) {
				$trnprt_color = imagecolorsforindex($source, $trnprt_indx);
				$trnprt_indx = imagecolorallocate($source, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagefill($new_img, 0, 0, $trnprt_indx);
				imagecolortransparent($new_img, $trnprt_indx);
			}

		imagecopyresampled($new_img, $source, 0, 0, 0, 0, $thumb_w, $thumb_h, $width, $height);
		$save_image = imagegif($new_img, $root_dir.$img_dir.'/'.$rnd_name.'_small.gif');
		imagedestroy($new_img);
	}

} else if ($img_ext == "png" || $img_ext == "jpg") {

	if ($img_ext == "png") {
		$source = imagecreatefrompng($img_name);
	} else {
		ini_set('gd.jpeg_ignore_warning', 1);
		$source = imagecreatefromjpeg($img_name);
	}

	$width = imagesx($source);
	$height = imagesy($source);

		if ($width > $thumb_w || $height > $thumb_h) {

			$ratio = $width/$height;
				if ($thumb_w/$thumb_h > $ratio) {
					$thumb_w = floor($thumb_h*$ratio);
				} else {
					$thumb_h = floor($thumb_w/$ratio);
				}

		} else {
			$thumb_w = $width;
			$thumb_h = $height;
		}

	$new_img = imagecreatetruecolor($thumb_w, $thumb_h);

		if ($img_ext == "png") {
			imagealphablending($new_img, false );
			imagesavealpha($new_img, true);
		}

	imagecopyresampled($new_img, $source, 0, 0, 0, 0, $thumb_w, $thumb_h, $width, $height);

		if ($img_ext == "png") {
			$save_image = imagepng($new_img, $root_dir.$img_dir.'/'.$rnd_name.'_small.'.$img_ext, 9);
		} else {
			$save_image = imagejpeg($new_img, $root_dir.$img_dir.'/'.$rnd_name.'_small.'.$img_ext, $thumb_quality);
		}

	imagedestroy($new_img);
	$img_size = round(filesize($img_name) / 1024);

	if ($max_image_width > 0 && $width > $max_image_width || $max_image_height > 0 && $height > $max_image_height) {

		if ($width > $max_image_width || $height > $max_image_height) {

			$ratio = $width/$height;
				if ($max_image_width/$max_image_height > $ratio) {
					$max_image_width = floor($max_image_height*$ratio);
				} else {
					$max_image_height = floor($max_image_width/$ratio);
				}

		} else {
			$max_image_width = $width;
			$max_image_height = $height;
		}

	$new_img = imagecreatetruecolor($max_image_width, $max_image_height);

		if ($img_ext == "png") {
			imagealphablending($new_img, false );
			imagesavealpha($new_img, true);
		}

	imagecopyresampled($new_img, $source, 0, 0, 0, 0, $max_image_width, $max_image_height, $width, $height);

		if ($img_ext == "png") {
			$save_image = imagepng($new_img, $root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext, 9);
		} else {
			$save_image = imagejpeg($new_img, $root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext, $image_quality);
		}

		$width = $max_image_width;
		$height = $max_image_height;
		$img_size = round(filesize($root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext) / 1024);
		imagedestroy($new_img);

	} else if ($image_quality < 100 && $img_ext == "jpg") {

		$new_img = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_img, $source, 0, 0, 0, 0, $width, $height, $width, $height);
		$save_image = imagejpeg($new_img, $root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext, $image_quality);
		$img_size = round(filesize($root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext) / 1024);
		imagedestroy($new_img);

	}

}

$check_img = $root_dir.$img_dir.'/'.$rnd_name.'_small.'.$img_ext;
if (!file_exists($check_img)) {

	if (file_exists(unlink($img_name)));
	if (file_exists(unlink($root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext)));

	$img_dir = "";
	$rnd_name = "";
	$img_ext = "";
	$width = 0;
	$height = 0;
	$img_size = 0;
	$thumb_w = 0;
	$thumb_h = 0;

} else {

	if (!file_exists($root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext)) {
		rename($img_name, $root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext);
	}

	chmod($root_dir.$img_dir.'/'.$rnd_name.'_big.'.$img_ext, 0644);
	chmod($root_dir.$img_dir.'/'.$rnd_name.'_small.'.$img_ext, 0644);
	mysql_query("INSERT INTO img (img_hash) VALUES ('$img_hash')", $link);
}

} else {
$img_dir = "";
$rnd_name = "";
$img_ext = "";
$width = 0;
$height = 0;
$img_size = 0;
$thumb_w = 0;
$thumb_h = 0;
}

if (!empty($post_text) | !empty($rnd_name)) {

$result = mysql_query("SELECT * FROM board WHERE post_id = '$tred_id'", $link);
$row = mysql_fetch_assoc($result);
$post_sec = $row['post_sec'];
$op_cookies = $row['post_cookies'];

if ($op_cookies <> $post_unique_id) {
$show_op = 0;
}

if (empty($post_of)) {

mysql_query("INSERT INTO board ( post_time, post_title, post_text, img_dir, post_img, img_ext, img_height, img_width, thumb_w, thumb_h, img_size, post_sec, post_num, post_of, post_bump, post_ip, post_browser, post_cookies, post_op) VALUES ('$post_time', '$post_title', '$post_text', '$img_dir', '$rnd_name', '$img_ext', '$height', '$width', '$thumb_w', '$thumb_h', '$img_size', '0', '0', '$id', '0', '$post_ip', '$post_browser', '$post_unique_id', '$show_op')", $link);
$last_id = mysql_insert_id($link);
if ($sage == 1) {
mysql_query("UPDATE board SET post_num = post_num + 1 WHERE post_id = '$id'", $link);
} else {
mysql_query("UPDATE board SET post_num = post_num + 1, post_bump = '$post_time' WHERE post_id = '$id'", $link);
}

if (!empty($for)) {
	mysql_query("UPDATE board SET post_text = CONCAT(post_text, '&lt;&lt;$last_id') WHERE post_id = '$for'");
}

} else {

mysql_query("INSERT INTO board (post_time, post_title, post_text, img_dir, post_img, img_ext, img_height, img_width, thumb_w, thumb_h, img_size, post_sec, post_num, post_of, post_bump, post_ip, post_browser, post_cookies, post_op) VALUES ('$post_time', '$post_title', '$post_text', '$img_dir', '$rnd_name', '$img_ext', '$height', '$width', '$thumb_w', '$thumb_h', '$img_size', '0', '0', '$post_of', '0', '$post_ip', '$post_browser', '$post_unique_id', '$show_op')", $link);
$last_id = mysql_insert_id($link);
if ($sage == 1) {
mysql_query("UPDATE board SET post_num = post_num + 1 WHERE post_id = '$post_of'", $link);
} else {
mysql_query("UPDATE board SET post_num = post_num + 1, post_bump = '$post_time' WHERE post_id = '$post_of'", $link);
}

if (!empty($for)) {
	mysql_query("UPDATE board SET post_text = CONCAT(post_text, '&lt;&lt;$last_id') WHERE post_id = '$for'");
}

mysql_close($link);
header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'post.php?id='.$post_of.'#c'.$last_id);
exit;
}
}
mysql_close($link);
header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'post.php?id='.$id.'#c'.$last_id);
?>