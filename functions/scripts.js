var post_id;
var last_comment_id;
var order_comments;
var id_responseText;
var text_responseText;
var comment_timer;
var page_title = document.title;
var loaded_comments = 0;
var preview_top = 0
var preview_bottom = 0;
var mouse_location = 0;


function update_mouth_location(event) {

	mouse_location = event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);

}


function start_timer() {

	var new_comments_div = document.getElementById("new_comments");
	var banner_div = document.getElementById("banner");

		if (new_comments_div) {
			comment_timer = setInterval(check_comments,10000);
		}

}


function gallery(elem, img_width) {

	var img_span = elem.parentNode.parentNode;
	var post_div = document.getElementById("gallery");
	var post_div_width = post_div.clientWidth;
	var post_div_style = window.getComputedStyle(post_div,null);
	var post_div_padding_left = parseFloat(post_div_style.getPropertyValue("padding-left"));
	var post_div_padding_right = parseFloat(post_div_style.getPropertyValue("padding-right"));
	var post_div_free_space = post_div_width-post_div_padding_left-post_div_padding_right

	var image_width = elem.width;

		if (img_width > post_div_free_space) {
			img_width = post_div_free_space;
		}

	var my_exist_progress = document.getElementById("progress_" + elem.parentNode.href);

		if (!my_exist_progress) {

			if (elem.src.search("small") > 0) {

				var my_index = "s" + parseInt(new Date().getTime()/1000)
				my_index = my_index.substr(6);

				elem.style.height = "auto";
				elem.style.width = img_width + "px";

				img_span.style.position = "absolute";
				img_span.style.zIndex = my_index;

				var html = document.documentElement;
				var body = document.body;
				var scrollTop = html.scrollTop || body && body.scrollTop || 0;

				img_span.style.top = scrollTop + 30 + "px";
				img_span.style.left = (document.documentElement.clientWidth - elem.width) / 2 + "px";
				elem.setAttribute("onclick", "gallery(this,'" + image_width + "'); return false");
				img_span.style.backgroundColor = post_div_style.getPropertyValue("background-color");

				var pc_div = document.createElement("div");
				pc_div.innerHTML = '<p><img class="text_img" src="styles/loading.gif" alt="" title="">Загрузка оригинала...</span>';
				pc_div.setAttribute("class", "comment");
				pc_div.style.width = 'auto';
				pc_div.style.opacity = '0.8';
				pc_div.style.position = 'absolute';
				pc_div.style.zIndex = my_index + 1;
				pc_div.style.top = img_span.style.top
				pc_div.style.left = img_span.style.left;
				pc_div.setAttribute("id", "progress_" + elem.parentNode.href);
				post_div.insertBefore(pc_div, post_div.firstChild);

				var img = document.createElement("img");

					img.onload=function(){

						var my_exist_progress = document.getElementById("progress_" + elem.parentNode.href);
							if (!my_exist_progress) {
								return;
							} else {
								my_exist_progress.parentNode.removeChild(my_exist_progress);
							}

							if (elem.src.search("small") > 0) {
								elem.src = elem.src.replace("small","big");
							} else {
								elem.src = elem.src.replace("big","small");
							}

					}

				img.src = elem.parentNode.href;
			} else {
				elem.src = elem.src.replace("big","small");
				elem.style.width = img_width + 'px';
				img_span.removeAttribute("style");
				img_span.style.position = 'static';
				elem.setAttribute("onclick", "gallery(this,'" + image_width + "'); return false");
			}

		} else {
			elem.style.width = img_width + 'px';
			img_span.removeAttribute("style");
			img_span.style.position = 'static';
			elem.setAttribute("onclick", "gallery(this,'" + image_width + "'); return false");
			my_exist_progress.parentNode.removeChild(my_exist_progress);
		}

}


function show_image(elem, img_width) {

	var post_div = elem.parentNode.parentNode;
	var post_div_width = post_div.clientWidth;
	var post_div_style = window.getComputedStyle(post_div,null);
	var post_div_padding_left = parseFloat(post_div_style.getPropertyValue("padding-left"));
	var post_div_padding_right = parseFloat(post_div_style.getPropertyValue("padding-right"));
	var post_div_free_space = post_div_width-post_div_padding_left-post_div_padding_right

	var image_width = elem.width;

		if (img_width > post_div_free_space) {
			img_width = post_div_free_space;
		}

	var my_exist_progress = document.getElementById("progress_" + elem.parentNode.parentNode.id);

		if (!my_exist_progress) {

			if (elem.src.search("small") > 0) {

				var pc_div = document.createElement("div");
				pc_div.innerHTML = '<p><img class="text_img" src="styles/loading.gif" alt="" title="">Загрузка оригинала...</span>';
				pc_div.style.position = 'absolute';
				pc_div.setAttribute("class", "comment");
				pc_div.style.width = 'auto';
				pc_div.style.opacity = '0.8';
				pc_div.setAttribute("id", "progress_" + post_div.id);
				post_div.insertBefore(pc_div, post_div.firstChild);

				elem.style.height = 'auto';
				elem.style.width = img_width + 'px';
				elem.setAttribute("onclick", "show_image(this,'" + image_width + "'); return false");

				var img = document.createElement("img");

					img.onload=function(){

						var my_exist_progress = document.getElementById("progress_" + elem.parentNode.parentNode.id);
							if (!my_exist_progress) {
								return;
							} else {
								my_exist_progress.parentNode.removeChild(my_exist_progress);
							}

							if (elem.src.search("small") > 0) {
								elem.src = elem.src.replace("small","big");
							} else {
								elem.src = elem.src.replace("big","small");
							}

					}

				img.src = elem.parentNode.href;
			} else {
				elem.src = elem.src.replace("big","small");
				elem.style.width = img_width + 'px';
				elem.setAttribute("onclick", "show_image(this,'" + image_width + "'); return false");
			}
		} else {
			elem.style.width = img_width + 'px';
			elem.setAttribute("onclick", "show_image(this,'" + image_width + "'); return false");
			my_exist_progress.parentNode.removeChild(my_exist_progress);
		}

}


function remove_reply() {

	var input_for = document.getElementById("for");
	var form_name = document.getElementById("form_name");
	form_name.innerHTML = "Новый комментарий";
	input_for.parentNode.removeChild(input_for);

}


function reply(elem) {

	var for_id = elem.parentNode.parentNode.id;
	for_id = for_id.replace("copy_","");
	for_id = for_id.replace("c","");
	var replay_form = document.forms[0];
	var exist_input = document.getElementById("for");
	var form_name = document.getElementById("form_name");

		if (!exist_input) {
			var new_hiden_input = document.createElement("input");
			new_hiden_input.setAttribute("type", "hidden");
			new_hiden_input.setAttribute("name", "for");
			new_hiden_input.setAttribute("id", "for");
			new_hiden_input.value = for_id;
			replay_form.appendChild(new_hiden_input);
			form_name.innerHTML = 'Ответ на комментарий №' + for_id + ' <span style="cursor: pointer; color: red;" title="Отменить" onclick="remove_reply()">(x)</span>';
			window.scroll(0, 0);
			var selectedText = window.getSelection();

				if (selectedText !='') {
					selectedText = selectedText.toString();
					replay_form.form_textarea.value = replay_form.form_textarea.value + ">>" + for_id + " [quote]" + selectedText + "[/quote]\n";
				} else {
					replay_form.form_textarea.value = replay_form.form_textarea.value + ">>" + for_id + " ";
				}

			replay_form.form_textarea.focus();
		} else {
			exist_input.value = for_id;
			form_name.innerHTML = 'Ответ на комментарий №' + for_id + ' <span style="cursor: pointer; color: red;" title="Отменить" onclick="remove_reply()">(x)</span>';
			window.scroll(0, 0);
			var selectedText = window.getSelection();
				if (selectedText !='') {
					selectedText = selectedText.toString();
					replay_form.form_textarea.value = replay_form.form_textarea.value + ">>" + for_id + " [quote]" + selectedText + "[/quote]\n";
				} else {
					replay_form.form_textarea.value = replay_form.form_textarea.value + ">>" + for_id + " ";
				}
			replay_form.form_textarea.focus();
		}

}


function set_order(elem, order) {

	set_cookies('order_comments', order);
	elem.parentNode.innerHTML = "Перегрузка страницы...";
	window.location.reload();

}


function set_cookies(c_name, c_value) {

	if (c_value != 0){

		var my_domain = window.location.hostname;
		var my_domain = my_domain.replace(/(www.)?(.+?)/, "$2");

			if (my_domain != 'localhost') {
			my_domain = "." + my_domain;
			} else {
			my_domain = "";
			}

		var exdays = 365;
		var exdate = new Date();

			if (c_value == "none") {
				exdate.setDate(exdate.getDate() - exdays);
			} else {
				exdate.setDate(exdate.getDate() + exdays);
			}

		var c_expires = exdate.toUTCString();
		var path ="/";
		var secure = "";

		document.cookie = escape(c_name) + "=" + escape(c_value) +
		((c_expires) ? "; expires=" + c_expires : "") +
		((path) ? "; path=" + path : "") +
		((my_domain) ? "; domain=" + my_domain : "") +
		((secure) ? "; secure" : "");

	}

}


function set_style(elem) {

	var c_value = elem.options[elem.options.selectedIndex].value;
	var old_select_text = elem.options[elem.options.selectedIndex].text;
	var old_style = document.getElementsByTagName('link')[0];
	var old_style_href = old_style.href.split("styles")[0];
	var old_style_query = old_style.href.split("?")[1];

		if (c_value != 0){
			set_cookies('extra_style', c_value);

				if (c_value == "none") {
					c_value = 'default';
				}

		var new_elem = document.createElement("link");
		new_elem.setAttribute("rel", "stylesheet");
		new_elem.setAttribute("type", "text/css");
		new_elem.setAttribute("href", old_style_href + "styles/" + escape(c_value) + "/style.css?" + old_style_query);
		old_style.parentNode.insertBefore(new_elem, old_style);
		elem.options[elem.options.selectedIndex].text = "загрузка...";

			new_elem.onload=function(){
				old_style.parentNode.removeChild(old_style);
				elem.options[elem.options.selectedIndex].text = old_select_text;
			}


		}

}


function delete_comment(delete_id) {

	var dc_xmlhttp = new XMLHttpRequest();
	dc_xmlhttp.open("POST","functions/delete_comment.php", true);
	var params = 'id=' + delete_id;
	dc_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		dc_xmlhttp.onreadystatechange=function() {

			if (dc_xmlhttp.readyState == 4 && dc_xmlhttp.status == 200) {
				var comment = document.getElementById("c" + delete_id);
				comment.parentNode.removeChild(comment);
			}

		}

	dc_xmlhttp.send(params);

}


function check_comments() {

	show_state('blue');
	var cc_xmlhttp = new XMLHttpRequest();

		if (post_id == 0) {
			cc_xmlhttp.open('POST','functions/p_check_new_comments.php', true);
			var params = 'id=' + last_comment_id;
		} else {
			cc_xmlhttp.open('POST','functions/check_new_comments.php', true);
			var params = 'id=' + last_comment_id + '&post_id=' + post_id;
		}

	cc_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		cc_xmlhttp.onreadystatechange=function() {

				if (cc_xmlhttp.readyState != 4) {
					return;
				}

			clearTimeout(cc_timeout);

				if (cc_xmlhttp.status == 200) {
					show_state('green');
				} else {
					show_state('red');
				}

				if (cc_xmlhttp.status == 200 && cc_xmlhttp.responseText != "") {
					var new_post = document.createElement("div");
					new_post.setAttribute("class", "comment");
					new_post.innerHTML = cc_xmlhttp.responseText;

						if (order_comments != 1) {
							var new_comments_div = document.getElementById("new_comments");
							new_comments_div.appendChild(new_post);
						} else {
							var new_comments_div = document.getElementById("new_comments").firstChild;
							new_comments_div.parentNode.insertBefore(new_post, new_comments_div);
						}

					var last_id_span = document.getElementById("id");
					last_comment_id = last_id_span.innerHTML;

						if (post_id != 0) {
							var post_num = document.getElementById("ans");
							post_num.innerHTML = parseFloat(post_num.innerHTML) + 1;
						}

					new_post.setAttribute("id", "c" + last_comment_id);
					last_id_span.parentNode.removeChild(last_id_span);
					loaded_comments = loaded_comments + 1;
					document.title = '[' + loaded_comments + '] ' + page_title;
				}

		}

	cc_xmlhttp.send(params);
	var cc_timeout = setTimeout( function(){ cc_xmlhttp.abort(); show_state('red'); }, 8000);

}


function show_state(color) {

	var div_extra_setting = document.getElementById('extra_setting');

	if (div_extra_setting) {
		var last_child = div_extra_setting.lastChild;
		var state_button = document.getElementById('state_button');

			if (!state_button) {
				var state_button = document.createElement("span");
				state_button.setAttribute("id", "state_button");
				div_extra_setting.insertBefore(state_button, last_child);
			}

			if (color == "red") {
				state_button.innerHTML = '<img src="styles/button_red.png" title="Автоподгрузке не удалось проверить наличие новых комментариев.">';
			} else if (color == "blue") {
				state_button.innerHTML = '<img src="styles/button_blue.png" title="Идет проверка новых комментариев...">';
			} else {
				state_button.innerHTML = '<img src="styles/button_green.png" title="Автоподгрузка новых комментариев работает нормально.">';
			}
	}

}


function preview_comment(event, comment_id) {

	document.body.addEventListener('mousemove', update_mouth_location,false);
	var oldNode = document.getElementById("c" + comment_id);

	my_curor_Y = 0;
	my_curor_Y = 0;
	var my_curor_Y = event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	var my_curor_X = event.clientX;

	if (oldNode) {
		var check_exist = document.getElementById("copy_" + comment_id);

		if (!check_exist) {
			var newNode = oldNode.cloneNode(true);
			newNode.setAttribute("id", "copy_" + comment_id);
			newNode.style.border = '3px double #800080';
			newNode.style.position = 'absolute';
			newNode.style.zIndex='0';
			newNode.style.top = my_curor_Y - 30 + 'px';
			newNode.style.left = event.target.parentNode.parentNode.offsetLeft + 30 + 'px';
			document.body.appendChild(newNode);

				if (parseFloat(newNode.style.top) < preview_top && preview_top > 0 || preview_top == 0) {
					preview_top = parseFloat(newNode.style.top) + 30;
				}

				if (parseFloat(newNode.style.top) + newNode.offsetHeight + 50 > preview_bottom) {
					preview_bottom = my_curor_Y + newNode.offsetHeight;
				}

			window.setTimeout("remove_preview_comment('" + comment_id + "')", 1000);

		}

	} else {
		var check_exist = document.getElementById("copy_" + comment_id);

		if (pc_xmlhttp == null && !check_exist) {
			var newNode = document.createElement("div");
			newNode.setAttribute("class", "comment");
			newNode.setAttribute("id", "copy_" + comment_id);
			newNode.style.border = '3px double #800080';
			newNode.style.position = 'absolute';
			newNode.style.zIndex='0';
			newNode.style.top = my_curor_Y - 30 + 'px';
			newNode.style.left = event.target.parentNode.parentNode.offsetLeft + 30 + 'px';
			newNode.innerHTML = '<p><img class="text_img" src="styles/loading.gif" alt="" title="">На этой странице комментарий не найден.<br>Идет поиск по базе данных...</p><span class="info">Комментарий №' + comment_id + '</span>';
			document.body.appendChild(newNode);

				if (parseFloat(newNode.style.top) < preview_top && preview_top > 0 || preview_top == 0) {
					preview_top = parseFloat(newNode.style.top) + 30;
				}

				if (parseFloat(newNode.style.top) + newNode.offsetHeight + 50 > preview_bottom) {
					preview_bottom = my_curor_Y + newNode.offsetHeight;
				}

			window.setTimeout("remove_preview_comment('" + comment_id + "')", 1000);

			if (id_responseText != comment_id ) {
			var pc_xmlhttp = new XMLHttpRequest();
			pc_xmlhttp.open('POST','functions/preview_comment.php', true);
			var params = 'id=' + comment_id + '&rnd=' + Math.random();
			pc_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

			pc_xmlhttp.onreadystatechange=function() {

				if (pc_xmlhttp.readyState==4 && pc_xmlhttp.status==200 && pc_xmlhttp.responseText != "") {
					text_responseText = pc_xmlhttp.responseText;
					newNode.innerHTML = text_responseText;
					id_responseText = comment_id;
					pc_xmlhttp = null;
				}


			}

			pc_xmlhttp.send(params);
			} else if (id_responseText == comment_id ) {
					newNode.innerHTML = text_responseText;
			}

		}

	}

}


function remove_preview_comment(comment_id) {

	var delite_div = document.getElementById("copy_" + comment_id);

		if (delite_div && preview_top > mouse_location || delite_div && preview_bottom < mouse_location) {
			document.body.removeChild(delite_div);
			preview_top = 0;
			preview_bottom = 0;
		} else {
			window.setTimeout("remove_preview_comment('" + comment_id + "')", 1000);
		}

}


function ban(ban_id) {

	var xmlhttp = new XMLHttpRequest();

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				document.getElementById("c" + ban_id).innerHTML= xmlhttp.responseText;
			}
		}

	xmlhttp.open("GET","functions/ban.php?id=" + ban_id,true);
	xmlhttp.send();

}


function remove_ban(ban_id) {

	var xmlhttp = new XMLHttpRequest();

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				alert (xmlhttp.responseText);
			}
		}

	xmlhttp.open("GET","functions/ban.php?id=" + ban_id + "&remove_ban=1",true);
	xmlhttp.send();

}


function move_select(thread_id, move_to) {

	var move_span = document.getElementById('edit_' + thread_id).parentNode;

		if (move_to != 0) {
			var move_button = document.getElementById("move_button");

				if (!move_button) {
					move_button = document.createElement('input');
					move_button.setAttribute("type", "button");
					move_button.setAttribute("value", "ok");
					move_button.setAttribute("id", "move_button");
				}
			move_button.setAttribute("onclick", "move_thread('" + thread_id + "','" + move_to + "')");
			move_span.appendChild(move_button);
		} else {
			move_span.removeChild(move_button);
		}

}


function move_thread(thread_id, move_to) {

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("POST",'functions/move_thread.php',true);
	var params = 'id=' + thread_id + '&to=' + move_to;
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				var elem = document.getElementById('edit_' + thread_id);
				elem.options[0].text = xmlhttp.responseText;
				elem.options.selectedIndex = 0;
			}
		}

	xmlhttp.send(params);

}


function show_hide(elem) {

	var my_div = elem.parentNode.parentNode;

		if(elem.getAttribute("class") == "hide_image") {

			var tag_h2 = my_div.getElementsByTagName("h2");
				if(tag_h2.item(0)) {
					tag_h2.item(0).setAttribute("style", "display: none;");
				}

			var tag_a = my_div.getElementsByTagName("a");
				if(tag_a[0]) {
					tag_a[0].setAttribute("style", "display: none;");
				}

			var tag_p = my_div.getElementsByTagName("p");
				if(tag_p[0]) {

					for (var i = 0; i < tag_p.length; i++) {
						tag_p[i].setAttribute("style", "display: none;");
					}
				}

			elem.setAttribute("class", "show_image");
			elem.setAttribute("title", "Показать");
			var my_id = my_div.id;
			my_id = my_id.replace("c","");
			set_cookies(my_id, 1);
		} else {
			var tag_h2 = my_div.getElementsByTagName("h2");
				if(tag_h2.item(0)) {
					tag_h2.item(0).removeAttribute("style");
				}

			var tag_a = my_div.getElementsByTagName("a");
				if(tag_a[0]) {
					tag_a[0].removeAttribute("style");
				}

			var tag_p = my_div.getElementsByTagName("p");
				if(tag_p[0]) {

					for (var i = 0; i < tag_p.length; i++) {
						tag_p[i].removeAttribute("style");
					}
				}

			elem.setAttribute("class", "hide_image");
			elem.setAttribute("title", "Скрыть");
			var my_id = my_div.id;
			my_id = my_id.replace("c","");
			set_cookies(my_id, "none");
		}

}


function edit_comment(comm_id) {

	var xmlhttp = new XMLHttpRequest();

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("c" + comm_id).innerHTML=xmlhttp.responseText;
			}
		}

	xmlhttp.open("GET","functions/edit.php?id=" + comm_id,true);
	xmlhttp.send();

}

function comment_css(comment_id) {

	var comment = document.getElementById(comment_id);

		if (comment) {
			var original_style_border = comment.style.border;
			comment.style.border = '3px double #800080';
			window.setTimeout(function() {comment.style.border = original_style_border;}, 3000);
		}

}


function show_smiles(form_id, pack) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET","img/smiles/smiles.php?id=" + form_id,true);
	smile_box = document.getElementById("smiles_" + form_id);
	smile_box.innerHTML = '<img class="text_img" src="styles/loading.gif" alt="" title="">';

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				smile_box.innerHTML = xmlhttp.responseText;
			}
		}

	xmlhttp.send();
}


function show_video(c_id, video_id) {
	var parent_div = document.getElementById("c" + c_id);

	var div_style = window.getComputedStyle(parent_div,null);
	var div_width = parseFloat(div_style.getPropertyValue("width"));

	var div_image = parent_div.getElementsByTagName('img')[0];
		if (div_image) {
			var image_style = window.getComputedStyle(div_image,null);
			var image_width = parseFloat(image_style.getPropertyValue("width"));
			var image_pl = parseFloat(image_style.getPropertyValue("margin-left"));
			var image_pr = parseFloat(image_style.getPropertyValue("margin-right"));
			var image_width_all = image_width + image_pl + image_pr;
		} else {
			image_width_all = 0;
		}

	var total_free_space = div_width - image_width_all;
		if (total_free_space < 640) {
			width_video = 560;
			height_video = 315;
		} else {
			width_video = 640;
			height_video = 360;
		}

	var post_div = document.getElementById("video_" + c_id);
	var my_video = '<object class="video" type="application/x-shockwave-flash" data="http://www.youtube.com/v/' + video_id + '?version=3&amp;fs=1&amp;autohide=1&amp;modestbranding=1&amp;autoplay=1" width="' + width_video + '" height="' + height_video + '"><param name="movie" value="http://www.youtube.com/v/' + video_id + '"><param name="FlashVars" value="playerMode=embedded"><param name="allowFullScreen" value="true"></object>';

	post_div.innerHTML = my_video;
}


function BB_code(form_id, sStartTag, sEndTag) {
var oMsgInput = document.getElementById(form_id).text;
var bDouble = arguments.length > 1,
nSelStart = oMsgInput.selectionStart,
nSelEnd = oMsgInput.selectionEnd,
sOldText = oMsgInput.value;
oMsgInput.value = sOldText.substring(0, nSelStart) + (bDouble ? sStartTag + sOldText.substring(nSelStart, nSelEnd) + sEndTag : sStartTag) + sOldText.substring(nSelEnd);
oMsgInput.setSelectionRange(bDouble || nSelStart === nSelEnd ? nSelStart + sStartTag.length : nSelStart, (bDouble ? nSelEnd : nSelStart) + sStartTag.length);
oMsgInput.focus();
}