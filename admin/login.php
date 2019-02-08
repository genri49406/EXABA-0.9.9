<?php
mb_internal_encoding("UTF-8");
ob_start("ob_gzhandler");
include '../config.php';

$my_time = TIME();

if (isset($_GET['logout'])) {
	setcookie('admin', 'expired', TIME()-60*60*24*365, '/', $domain, false);
}

if (isset($_POST['admin_name'])) {
	$input_admin_name = trim($_POST['admin_name']);
	$input_admin_name = htmlspecialchars($input_admin_name, ENT_QUOTES);
	$input_admin_name = mysql_real_escape_string($input_admin_name);
} else {
	$input_admin_name = '';
}

$input_admin_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES);
$input_admin_ip = mysql_real_escape_string($input_admin_ip);

if (isset($_POST['admin_password'])) {
$input_admin_password = trim($_POST['admin_password']);
} else {
$input_admin_password = '';
}

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM admin"),0);

	if ($total == 0) {
		$page_title = 'Создание админского аккаунта (Не создавайте простых паролей и юзернеймов)';
	} else {
		$page_title = 'Авторизация';
	}

if (empty($input_admin_name) || empty($input_admin_password)) {

include '../functions/html.php';

echo '
<form action="" method="post" enctype="multipart/form-data">
	<div class="post">
		<h2>'.$page_title.'</h2>
		<img src="../styles/default/logo.png" alt="" class="text_image">
		<input type="text" name="admin_name" maxlength="15" placeholder="Имя"><br><br>
		<input type="password" name="admin_password" maxlength="15" placeholder="Пароль"><br>
		<input type="submit" class="form_submit" value="Ok">
		<div class="info">&nbsp;</div>
	</div>
</form>
';

footer();
mysql_close($link);
exit;
}


$input_admin_password = md5($input_admin_password);

$check_time = $my_time - 300;

$result = mysql_query("SELECT admin_login_time, admin_login_attempt, admin_ip FROM admin LIMIT 1", $link);
$row = mysql_fetch_assoc($result);
$admin_login_time = $row['admin_login_time'];
$admin_login_attempt = $row['admin_login_attempt'];
$admin_ip = $row['admin_ip'];

if ($admin_login_attempt > 3 && $admin_login_time > $check_time && $admin_ip <> $input_admin_ip) {
	mysql_query("UPDATE admin SET admin_login_time = '$my_time', admin_login_attempt = admin_login_attempt + 1", $link);
	$page_title = "Защита от перебора паролей";
	$error = "Повторите попытку авторизации через 5 минут.";
	include '../functions/404.php';
	exit;
}

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM admin"),0);
if ($total == 0) {

		$length = 17;
		$characters = str_shuffle('123456789aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ');
		$string = "";

			for ($i = 0; $i < $length; $i++) {
				$string .= $characters[mt_rand(0, 60)];
			}

		$admin_cookies = 'admin'.$my_time.$string;
		setcookie('admin', $admin_cookies, TIME()+60*60*24*365, '/', $domain, false);

	mysql_query("INSERT INTO admin (admin_name, admin_password, admin_cookies, admin_login_time, admin_login_attempt, admin_ip) VALUES ('$input_admin_name', '$input_admin_password', '$admin_cookies', '$my_time', '0', '$input_admin_ip')");
	$page_title = 'Аккаут админа создан';
	$error = 'Админский аккаунт создан.<br>Логин: '.$input_admin_name.'<br>Пароль: указанный при создании аккаунта<br><a href="./">Перейти в админку</a>';
	include '../functions/404.php';
	exit;

}

$total = mysql_result(mysql_query("SELECT COUNT(*) FROM admin WHERE admin_name = '$input_admin_name' AND admin_password = '$input_admin_password' LIMIT 1"),0);
if ($total > 0) {

		$length = 17;
		$characters = str_shuffle('123456789aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ');
		$string = "";

			for ($i = 0; $i < $length; $i++) {
				$string .= $characters[mt_rand(0, 60)];
			}

		$admin_cookies = 'admin'.$my_time.$string;
		setcookie('admin', $admin_cookies, TIME()+60*60*24*365, '/', $domain, false);

		mysql_query("UPDATE admin SET admin_cookies = '$admin_cookies', admin_ip = '$input_admin_ip' WHERE admin_name = '$input_admin_name' AND admin_password = '$input_admin_password'", $link);
		mysql_query("UPDATE admin SET admin_login_time = '$my_time', admin_login_attempt = '0'", $link);

} else {

	mysql_query("UPDATE admin SET admin_login_time = '$my_time', admin_login_attempt = admin_login_attempt + 1", $link);
	$page_title = 'Ошибка авторизации';
	$error = 'Имя или пароль не верен.';
	include '../functions/404.php';
	exit;

}

mysql_close($link);
header('Location: http://'.$_SERVER['SERVER_NAME'].'/'.$web_folder.'admin/index.php');
?>