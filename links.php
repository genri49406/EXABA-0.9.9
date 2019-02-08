<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");

$limit = 1000;
include 'config.php';

$page_title = 'Статистика ссылок';
$page_description = 'Статистика ссылок, по которым приходят на '.$domain;

$domain = preg_replace( "#^(\.)#", "", $domain);

// Фильтр нежелательных ссылок.
$my_filter = "
AND user_referer NOT LIKE 'http://$domain%'
AND user_referer NOT LIKE 'http://www.$domain%'
";

$num_rows = 0;
include 'functions/html.php';

echo '
<div class="post">
';

$result = mysql_query("SELECT * FROM (SELECT user_referer, user_time FROM statistics WHERE user_referer <>'' $my_filter GROUP BY user_ip) q1 order by user_time DESC LIMIT $limit");
while ($row = mysql_fetch_assoc($result)) {
$user_referer = $row['user_referer'];
$user_time = $row['user_time'];
$user_time = date('d '.$month_array[date('n',$user_time)].', H:i',$user_time);

$decode_text = htmlspecialchars(urldecode($user_referer), ENT_QUOTES);

if(preg_match('/((q=%22|q=|text=|query=)(.+?)(%22|&|$))/', $decode_text, $search_result)) {
$user_referer = preg_replace( '#(https?://|www\.)([-a-z0-9+._%:/?=\#\&amp;]+)#i', $user_time.' <a href="http://$2">'.$search_result[3].'</a>', $user_referer);
} else {
$user_referer = preg_replace( '#(https?://|www\.)([-a-z0-9+._%:/?=\#\&amp;]+)#i', $user_time.' <a href="http://$2">'.$decode_text.'</a>', $user_referer);
}

echo '<p>'.$user_referer.'</p>
';
}
echo '</div>
';

mysql_free_result($result);

footer();
mysql_close($link);
?>