<?php
if(count(get_included_files()) == 1) {
header("Location: http://".$_SERVER['SERVER_NAME']);
exit;
}

// Обязательные настройки борды.

$web_folder = ""; // директория в которую устанавливается эксаба, со слешем / например "exaba/", оставьте незаполненным, если устанавливается в корневую директорию.

$hostname = ""; // MySQL адрес сервера базы данных (MySQL Server)
$username = ""; // MySQL имя пользователя базы данных (MySQL Username)
$password = ""; // MySQL пароль к базе данных (MySQL Password)
$dbname = ""; // MySQL имя базы данных (MySQL Database Name)

// Необязательные настройки (с настройками по умолчанию все будет работать).

$host_name = "Анонимная имиджборда";	// Название вашего чана, например "Анонимная имиджборда".
$css_version = "01"; // При изменении стилей, измените номер версии, это избавит всех от закешированных файлов css, переименовывать файлы не нужно.
$scripts_version = "01"; // // При изменении скрипта, измените номер версии, это избавит всех от закешированного файла java скрипта , переименовывать файлы не нужно.

error_reporting(0); // Не показывать системные ошибки, измените на -1 если нужен полный вывод ошибок для тестирования.
date_default_timezone_set('Etc/GMT-4'); // Установите время доски, например разница GMT с московским временем -4 часа

$image_opening = 1; // Изображения открываются в теле поста, измените на 0 чтобы открывались отдельно.
$OP_moderator = 1; // Может ли ОП треда удалять и банить, 0 если не может.

$img_dir = "img/08_2012"; // директория для изображений постов, директорию нужно создать вручную если её нет. (если текущая директория имеет более 5000-10000 пикч создайте и впишите новую)

$ImageMagik = 0; // Измените на 1, если хостинг поддерживает ImageMagick, если "0" то *.GIF превьюшки не будут анимированными.
$ImageMagik_path = ""; // Путь к ImageMagick, например "/usr/bin/".

$thumb_w = 250; // Максимальная ширина для превьюшки.
$thumb_h = 250; // Максимальная высота для превьюшки.
$thumb_quality = 90; // Качество изображения для JPG превьюшки от 1 до 100.

$max_image_width = 0; // Максимальная ширина оригинала, изображение больше будут уменьшены, если 0 то останется оригинальный размер.
$max_image_height = 0; // Максимальная высота оригинала, изображение больше будут уменьшены, если 0 то останется оригинальный размер
$image_quality = 100; // Качество изображения для JPG от 1 до 100.

/* Будьте очень осторожны выставляя опцию  max_pages_for_section (значение 0 означает что функция не задействована, минимальное количество 5 страниц),
опция включит функцию удаление старых тредов при оптимизации борды в админке,
выставив для примера число 20, функция оставит по 20 страниц тредов в каждом разделе,
остальные треды удалит из базы данных вместе с комментами и пикчами. */
$max_pages_for_section = 0;


// Конец настроек борды, код ниже следует изменять только если вы знаете что делаете.


if ($_SERVER['HTTP_HOST'] != 'localhost') {
$domain = preg_replace( "#www.#is", "", $_SERVER['HTTP_HOST'] );
$domain = '.'.$domain;
}else {
$domain = "";
}

$root_dir = realpath(dirname(__FILE__))."/";
$month_array = array(1 => "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");

@$link = mysql_connect($hostname, $username, $password);
if (!$link) {
	$page_title = "Упс";
	$error = "Нет связи с MySQL сервером.<br>".mysql_error();
	include 'functions/404.php';
	exit;
}

if (!@mysql_select_db($dbname, $link)) {
	$page_title = "Упс";
	$error = "Связь с MySQL сервером установлена но нет связи с базой данных.<br>".mysql_error();
	include 'functions/404.php';
	exit;
}

mysql_query("SET NAMES utf8");

	if(ini_get('allow_url_fopen') == "off" || ini_get('allow_url_fopen') == 0) {
		$allow_url_fopen = 0;
	} else {
		$allow_url_fopen = 1;
	}

if (get_magic_quotes_runtime()) {
	set_magic_quotes_runtime(0);
}

function text_formating() {

	global $root_dir, $web_folder, $post_id, $post_title, $post_text, $video, $hide_style,
	$post_img, $img_dir, $img_ext, $img_size, $img_width, $img_height, $img_wh, $thumb_w, $thumb_h, $ext_info, $image_opening, $replays;

	$post_text = preg_replace('#(\r\n)|(\n)#is', '<br>', $post_text);
	$post_text = preg_replace( "#(&gt;&gt;([\d]+))#is", "<a href=\"#c\\2\" onclick=\"comment_css('c\\2');\" onmouseover=\"preview_comment(event, '\\2');\">&gt;&gt;\\2</a>", $post_text );
	$post_text = preg_replace( "#\[quote\](.+?)\[/quote\]#is", "<span class=\"quote\">\\1</span>", $post_text );
	$post_text = preg_replace( "#\[b\](.+?)\[/b\]#is", "<b>\\1</b>", $post_text );
	$post_text = preg_replace( "#\[i\](.+?)\[/i\]#is", "<i>\\1</i>", $post_text );
	$post_text = preg_replace( "#\[u\](.+?)\[/u\]#is", "<ins>\\1</ins>", $post_text );
	$post_text = preg_replace( "#\[s\](.+?)\[/s\]#is", "<del>\\1</del>", $post_text );
	$post_text = preg_replace( "#\[spoiler\](.+?)\[/spoiler\]#is", "<span class=\"spoiler\">\\1</span>", $post_text );
	$post_text = preg_replace( '#(https?://|www\.)([-a-z0-9+._%:/?=\#\&amp;]+)#i', '<a href="http://$2">$1$2</a>', $post_text);
	$post_text = preg_replace("#\[smile\]([1-9]|[123][0-9]|[4][0-3])\[/smile\]#is","<img src=\"http://".$_SERVER['SERVER_NAME']."/".$web_folder."img/smiles/\\1.gif\" alt=\"\">", $post_text);

	$out="";
	$matches ="";
	$val = "";
	$replays = "";

	preg_match_all( "#(&lt;&lt;([\d]+))#is", $post_text, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			$out .= "<a href=\"#c".$val[2]."\" onclick=\"comment_css('c".$val[2]."');\" onmouseover=\"preview_comment(event, '".$val[2]."');\">&gt;&gt;".$val[2]."</a> ";
		}

		if(!empty($out)) {
			$replays = " &nbsp;&nbsp;Ответы:".$out;
			$post_text = preg_replace( "#(&lt;&lt;([\d]+))#is", "", $post_text );
		}

	if (preg_match('/youtube\.com\/watch\?(.+?)v=([A-Za-z0-9._%-]*)[&\w;=\+_\-]*/', $post_text, $video_id)) {
		$remote_image = 'http://img.youtube.com/vi/'.$video_id[2].'/default.jpg';
		$video_image = $root_dir.'img/video_images/'.$post_id.'.jpg';

			if (!file_exists($video_image)) {

				if (!copy($remote_image, $video_image)) {
					$video_image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'styles/video_error.png';
				} else {
					$video_image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'img/video_images/'.$post_id.'.jpg';
				}

			} else {
				$video_image = 'http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'img/video_images/'.$post_id.'.jpg';
			}

		$video = '<p'.$hide_style.' id="video_'.$post_id.'" class="video"><span class="youtube" style="background-image: url(http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'styles/video.png), url('.$video_image.');" onclick="show_video(\''.$post_id.'\', \''.$video_id[2].'\')"></span></p>';
	} else {
		$video = '';
	}

	if (!empty($post_title)) {
		$post_title ='<h2'.$hide_style.'>'.$post_title.'</h2>'."\r\n";
	}

	if ($image_opening == 1) {
		$image_open_inside = ' onclick="show_image(this,\''.$img_width.'\'); return false"';
	} else {
		$image_open_inside = '';
	}

	if (!empty($post_img)) {

		if (!empty($img_ext)) {
			$ext_info = strtoupper($img_ext).' Image';
		} else {
			$ext_info = '';
		}

		if (!empty($img_size)) {
			$img_size = ', '.$img_size.'KB';
		}

		if (!empty($img_height) && !empty($img_width)) {
			$img_wh = ', '.$img_width.'px &#215; '.$img_height.'px';
		} else {
			$img_wh = '';
		}

		if (!empty($thumb_w) && !empty($thumb_h)) {
			$thumb_wh = 'style="width: '.$thumb_w.'px; height: '.$thumb_h.'px;"';
		} else {
			$thumb_wh = '';
		}

		$post_img = '<a'.$hide_style.' href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.$img_dir.'/'.$post_img.'_big.'.$img_ext.'" class="text_image"><img'.$image_open_inside.' src="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.$img_dir.'/'.$post_img.'_small.'.$img_ext.'" '.$thumb_wh.' alt="" title="'.$ext_info . $img_size . $img_wh.'"></a>'."\r\n";
	}

}

?>