function optimize(elem) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open('POST','functions/board_optimizer.php', true);
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	xmlhttp.onreadystatechange=function() {

		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			elem.innerHTML = "Оптимизировано";
		}

	}

	xmlhttp.send();

}


function ban_admin(post_id, total_ban) {
	var ban_div = document.getElementById("c" + post_id);
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open('POST','functions/ban.php', true);
	var params = 'id=' + post_id + '&total_ban=' + total_ban;
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	xmlhttp.onreadystatechange=function() {

		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			ban_div.innerHTML = xmlhttp.responseText;
		}

	}

	xmlhttp.send(params);

}

function delete_post_admin(delete_id) {
	var thread_div = document.getElementById("c" + delete_id);
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open('POST','functions/delete_thread.php', true);
	var params = 'id=' + delete_id;
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	xmlhttp.onreadystatechange=function() {

		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			thread_div.parentNode.removeChild(thread_div);
		}

	}

	xmlhttp.send(params);

}