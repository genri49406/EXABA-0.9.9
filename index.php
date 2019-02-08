<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include 'config.php';

$page_title = 'Глагне';
$page_description = 'Новое поколение анонимных досок';

include 'functions/html.php';

echo'
<div class="post">
	<img src="styles/default/logo.png" style="width: 200px; height: 200px;" class="text_image" alt="">
	<h2>эксаба</h2>
	<p>
		это новое поколение анонимных досок,
		проект призванный потеснить убогие кусабы и вакабы на анонимных просторах интернетов.<br>
		Доска запиливалась специально для анонимного общения с множеством пользовательских функций,
		уникальная защита от вайпа, отсутсвие капчи, полная анонимность постинга, автодогрузка новых комментов в треде итд.<br>
		<a href="admin/">админка</a>
	</p>
	<div class="info">We are Anonymous. We are Legion. We do not forgive. We do not forget. Expect us.</div>
</div>
';

footer();
?>