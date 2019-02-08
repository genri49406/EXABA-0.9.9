<?php
mb_internal_encoding("UTF-8");
include '../config.php';

if (!isset($_COOKIE['post_owner'])) {
	$page_title = 'Отсутствуют кукис';
	$error = 'Включите поддержку кукис в браузере.';
	include '404.php';
	exit;
}

$post_time = TIME();
$rnd_name = "";

$post_sec = (int)$_POST['sec'];

$post_title = trim($_POST['title']);
$post_text = trim($_POST['text']);
$post_title = preg_replace('# +#is', ' ', $post_title);
$post_text = preg_replace('# +#is', ' ', $post_text);
$post_title = mb_substr($post_title, 0, 70);
$post_text = mb_substr($post_text, 0, 10000);

$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
	if (preg_match('/^((ver03)[a-z0-9_]+)$/i', $post_unique_id)) {
		$post_unique_id = mysql_real_escape_string($post_unique_id);
	} else {
		$page_title = 'Отсутствуют кукис';
		$error = 'Включите поддержку кукис в браузере.';
		include '404.php';
		exit;
	}

if (empty($post_text)) {
	$page_title = 'Отсутствует текст';
	$error = 'Для создания нового треда, текст и пикча обязательны.';
	include '404.php';
	exit;
}

$post_title=htmlspecialchars($post_title, ENT_QUOTES);
$post_text=htmlspecialchars($post_text, ENT_QUOTES);

$post_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
$post_browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES);

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM black_list WHERE ban_id = '0' AND ban_ip ='$post_ip' OR ban_id = '0' AND ban_cookies = '$post_unique_id'"),0);
	if ($total > 0) {
		$page_title = 'Упс';
		$error = 'Вы не можете создавать новые треды - Администрация борды заблокировала вас.';
		include '404.php';
		exit;
	}

$post_title = mysql_real_escape_string($post_title);
$post_text = mysql_real_escape_string($post_text);
$post_ip = mysql_real_escape_string($post_ip);
$post_browser = mysql_real_escape_string($post_browser);

$result = mysql_query("SELECT * FROM board_config WHERE sec_id = '$post_sec'", $link);
$row = mysql_fetch_assoc($result);
$page_title = $row['sec_title'];
$page_description = $row['sec_description'];
$limit = $row['sec_limit'];
$sec_total_limit = $row['sec_ip_limit'];
$sec_ip_limit = $row['sec_ip_limit'];
$num_rows = $row['sec_rows'];
$file_name = $row['file_name'];

if (mysql_num_rows($result) == 0) {
	header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'');
	exit;
}

$my_time = TIME()-3600;
$my_result = mysql_query("SELECT * FROM board WHERE post_time > '$my_time'");

while ($row = mysql_fetch_assoc($my_result)) {
	$test_text = $row['post_text'];

		if ($test_text == $post_text) {
			$page_title = 'Повторяющийся текст';
			$error = 'Тред с таким текстом уже существует!';
			include '404.php';
			exit;
		}
}

$my_time = TIME()-1800;
$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board WHERE post_time > '$my_time' AND post_ip = '$post_ip' AND post_sec  = '$post_sec'"),0);
	if ($total >= $sec_ip_limit) {
		$page_title = 'Превышение лимита добавления новых тем';
		$error = 'Чтобы как-то предотвращать «Вайп», установлено ограничение на добавление новых тем для этого раздела - не более '.$sec_ip_limit.' новых тем в течении 30 минут.';
		include '404.php';
		exit;
	}

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM board WHERE post_time > '$my_time' AND post_sec  = '$post_sec'"),0);
	if ($total >= $sec_total_limit) {
		$page_title = 'Превышение лимита добавления новых тем';
		$error = 'Чтобы как-то предотвращать «Вайп», установлено ограничение на добавление новых тем для этого раздела - не более '.$sec_total_limit.' новых тем в течении 30 минут.';
		include '404.php';
		exit;
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
	$img_name = $_FILES['img']['tmp_name'];
} else {
	$img_name = '';
	$page_title = 'Неудалось обработать изображение';
	$error = 'Возможно что пикча была с дефектом или неопознанный формат файла.';
	include '404.php';
	exit;
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
		exec("'$ImageMagik_path'convert $secure_name'[0]' -sample 200x200\> ../'$img_dir'/'$rnd_name'_small.gif");
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
$page_title = 'Отсутствует изображение';
$error = 'Для создания нового треда, текст и пикча обязательны.';
include '404.php';
exit;
}

if (!empty($post_text) && !empty($rnd_name)) {
mysql_query("INSERT INTO board (post_time, post_title, post_text, img_dir, post_img, img_ext, img_height, img_width, thumb_w, thumb_h, img_size, post_sec, post_num, post_of, post_bump, post_ip, post_browser, post_cookies) VALUES ('$post_time', '$post_title', '$post_text', '$img_dir', '$rnd_name', '$img_ext', '$height', '$width', '$thumb_w', '$thumb_h', '$img_size', '$post_sec', '0', '0', '$post_time', '$post_ip', '$post_browser', '$post_unique_id')");
mysql_query("INSERT INTO img (img_hash) VALUES ('$img_hash')");
mysql_query("UPDATE board_config SET sec_rows = sec_rows + 1 WHERE sec_id = '$post_sec'", $link);
mysql_close($link);
}

header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.''.$file_name.'.php');
?>