<?php
mb_internal_encoding("UTF-8");
header("HTTP/1.0 404 Not Found");

if (!$page_title) {
	$page_title = 'Ошибка 404 Not Found';
}

if (!$error) {
	$error = 'Ошибка вышла же!<br>То, что вы ищите уже удалено или неправильный адрес.';
}

include_once dirname(__FILE__).'/../config.php';
include_once dirname(__FILE__).'/../functions/html.php';

echo'
<div class="comment">
	<h2>'.$page_title.'</h2>
	<img src="http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'styles/default/logo.png" alt="" class="text_image">
	<p class="text">'.$error.'</p>
	<span class="info">&nbsp;</span>
</div>
';

footer();
mysql_close($link);
?>