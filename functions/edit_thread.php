<?php
mb_internal_encoding("UTF-8");
include '../config.php';

$post_time = TIME();
$rnd_name = "";

if (isset($_COOKIE['post_owner'])) {
$post_unique_id = htmlspecialchars($_COOKIE["post_owner"], ENT_QUOTES);
$post_unique_id = mysql_real_escape_string($post_unique_id);
} else {
$page_title = 'Ошибка авторизации';
$error = 'Вы не опознаны как автор этого коммента и можете его редактировать.';
include '404.php';
exit;
}

$id = (int)$_POST['id'];

if (isset($_POST['show_op'])) {
	$show_op = (int)$_POST['show_op'];
} else {
	$show_op = 0;
}

$comment_limit = (int)$_POST['comment_limit'];


$text = trim($_POST['text']);
$text = mb_substr($text, 0, 20000);
$text = htmlspecialchars($text, ENT_QUOTES);
$text = mysql_real_escape_string($text);

$title = trim($_POST['title']);
$title = mb_substr($title, 0, 70);
$title = htmlspecialchars($title, ENT_QUOTES);
$title = mysql_real_escape_string($title);

$sql = ("SELECT * FROM board WHERE post_id = '$id' AND post_sec > '0' AND post_cookies = '$post_unique_id'");
$result = mysql_query($sql, $link);

$row = mysql_fetch_assoc($result);
$old_img_dir = $row['img_dir'];
$post_img = $row['post_img'];
$old_img_ext = $row['img_ext'];
$post_sec = $row['post_sec'];

$num_rows = mysql_num_rows($result);

if ($num_rows == '0') {
mysql_close($link);
header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'');
exit;
}

$www_file = $_POST['www_file'];
$pc_file = $_FILES['img'];
$img_name = '../img/tmp_file/'.TIME() . rand(100, 999);

if (!empty($www_file) && preg_match('#^(https?://)#i', $www_file)) {

if (!@copy($www_file, $img_name)) {
$img_name = '';
}

} elseif (is_uploaded_file($_FILES['img']['tmp_name'])) {
$img_name = $_FILES['img']['tmp_name'];
} else {
$img_name = '';
}

if ($img_name <> '') {
ini_set('memory_limit', '-1');
$rnd_name = $post_time . rand(100, 999);

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
}

$img_del = '../'.$old_img_dir.'/'.$post_img.'_small.'.$old_img_ext;
if (file_exists($img_del)) {
unlink($img_del);
}
$img_del = '../'.$old_img_dir.'/'.$post_img.'_big.'.$old_img_ext;
if (file_exists($img_del)) {
unlink($img_del);
}
mysql_query("UPDATE board SET img_dir = '$img_dir', post_img = '$rnd_name', img_ext = '$img_ext', img_height = '$height', img_width = '$width', thumb_w = '$thumb_w', thumb_h = '$thumb_h', img_size = '$img_size' WHERE post_id = '$id'");
}

if (!empty($text)) {
mysql_query("UPDATE board SET post_title = '$title', post_text = '$text' WHERE post_id = '$id'");
}

$sql = ("SELECT * FROM post_config WHERE config_post_id = '$id'");
$result = mysql_query($sql, $link);
$num_rows = mysql_num_rows($result);
$row = mysql_fetch_assoc($result);

if (empty($num_rows)) {
mysql_query("INSERT INTO post_config ( config_post_id, comment_limit, show_op) VALUES ('$id', '$comment_limit', '$show_op')");
} else {
mysql_query("UPDATE post_config SET comment_limit = '$comment_limit', show_op = '$show_op' WHERE config_post_id = '$id'");
}

mysql_close($link);

header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'post.php?id='.$id);
exit;
?>