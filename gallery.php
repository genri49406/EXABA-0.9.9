<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include 'config.php';

$page_title = 'Галерея лучших изображений рунета и забугорья';
$page_description = 'Только здесь вы найдете смишные картинки, ЦП, расчлененку, ТП и многое другое';

$num_rows ='';
$limit = 100; // Лимит изображений на странице

if (isset($_GET['page']) && (int)$_GET['page'] > 0) {
$page = (int)$_GET['page'];
} else {
$page = 0;
}

if (isset($_GET['order']) && (int)$_GET['order'] > 0) {
	$order = (int)$_GET['order'];
} else {
	$order = 0;
}

include 'functions/html.php';

echo '
<div class="menu">
	<nav>
		Сортировка:
		<ul>
				<li><a href="?order=0">По дате</a></li>
				<li><a href="?order=1">По разрешению</a></li>
				<li><a href="?order=2">По размеру в байтах</a></li>
		</ul>
	</nav>
</div>
';

if($order == '0') {
$what = "ORDER BY post_img";
} else if($order == '1') {
$what = "ORDER BY img_width";
} else if($order == '2') {
$what = "ORDER BY img_size";
} else {
$what = "ORDER BY post_img";
}

$num_rows = mysql_result(mysql_query("SELECT COUNT(*) FROM board WHERE post_img <> ''"),0);

echo '<div id="gallery" class="post">'."\r\n";
$result = mysql_query("SELECT * FROM board WHERE post_img <> '' $what DESC LIMIT $page, $limit");
while ($row = mysql_fetch_assoc($result)) {
$post_id = $row['post_id'];
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

	if ($image_opening == 1) {
		$image_open_inside = ' onclick="gallery(this,\''.$img_width.'\'); return false"';
	} else {
		$image_open_inside = '';
	}

	if (!empty($thumb_w) && !empty($thumb_h)) {
		$thumb_wh = 'style="width: '.$thumb_w.'px; height: '.$thumb_h.'px;"';
	} else {
		$thumb_wh = '';
	}

if (empty($post_of)) {
	$url = ' <a href="post.php?id='.$post_id.'" title="Перейти в тред">'.$post_time.'</a> ';
} else {
	$url = ' <a href="post.php?id='.$post_of.'#c'.$post_id.'" title="Перейти в тред">'.$post_time.'</a> ';
}

echo '
<span><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.$img_dir.'/'.$post_img.'_big.'.$img_ext.'"><img'.$image_open_inside.' src="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.$img_dir.'/'.$post_img.'_small.'.$img_ext.'" '.$thumb_wh.' alt=""></a><br>
Размер: '.$img_size.'КБ, Тип: '.strtoupper($img_ext).'<br>
Ширина: '. $img_width.'px, Высота: '.$img_height.'px<br>'.$url.'</span>'."\r\n";
}

echo '</div>';
mysql_free_result($result);

if($num_rows > $limit) {
	pages($num_rows, $limit, '', $order);
}

footer();
mysql_close($link);
?>