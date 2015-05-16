function escapeHtml(text) {
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

var nickname = "";
var parentUrl = "";	// Enter the URL of the folder where the server-side PHP files are located

function sendMessage() {
	var textField = document.getElementById("textField");
	if(textField.value !== "") {
		$.ajax({
			url: parentUrl+"PutMessage.php",
			data:{
				message: escapeHtml(textField.value),
				nickname: nickname
			},
			type: "POST"
		});
		textField.value = "";
	}
}

var isSettingsOpen = false;
function openSettings() {
	var $settingsMenu = $("#settingsMenu");
	if(!isSettingsOpen) {
		$settingsMenu.css("display", "block");
		isSettingsOpen = true;
	} else {
		$settingsMenu.css("display", "none");
		isSettingsOpen = false;
	}
}

function setNick() {
	var nickVal = document.getElementById("nickField").value;
	if(nickVal !== "" && nickVal.length <= 20)  { 
		var finalVal = escapeHtml(nickVal);
		$.ajax({
			url: parentUrl+"SetNick.php",
			data: {
				newnick: finalVal,
				oldnick: nickname
			},
			dataType: "json",
			type: "GET",
			success: function(json) {
				if(json.success === "true") {
					nickname = json.nickname;
				}
			}
		});
	}
	document.getElementById("nickField").value = "";
}

function removeSendButton() {
	var $send = $("#send");
	var $textField = $("#textField");
	if(document.getElementById("removeSendButton").checked == true) {
		$("#send").css("display", "none");
		$("#textField").css("width", "100%");
	} else {
		$send.css("display", "inline");
		$send.css("width", "5%");
		$textField.css("width", "95%");
	}
}

var autoScroll = true;
function disableAutoScroll() {
	if(document.getElementById("disableAutoScroll").checked == true) { autoScroll = false; }
	else { autoScroll = true; }
}

function setOpacity() {
	var $settingsMenu = $("#settingsMenu");
	var slider = document.getElementById("setOpacity");
	$settingsMenu.css("opacity", slider.value/100);
	$settingsMenu.css("filter", "alpha(opacity=" + String(slider.value) + ")");
}

$(document).ready(function() {

	var $messageBar = $("#messageBar");
	var $send = $("#send");
	var $textField = $("#textField");
	var $messages = $("#messages");
	var $message = $(".message");
	
	var messageQueue = [];
	var messageIdQueue = [];
	var prevMessageIdQueue = [];
	
	// Set number of messages on in document
	var maxMessageSize = 100;
	
	// Client side message queue size
	var clientQueueSize = 50;
	
	$textField.keyup(function(event) {
		if(event.keyCode == 13) {
			$send.click();
		}
	});
	
	document.getElementById("setOpacity").value = $("#settingsMenu").css("opacity") * 100;
	
	var prevTopBarScrollHeight, prevMessagesHeight;
	function calibrateScreen() {
		var topBarScrollHeight = document.getElementById("topBar").scrollHeight;
		var messagesHeight = $(window).height()
		- document.getElementById("messageBar").scrollHeight
		- document.getElementById("topBar").scrollHeight;
		
		if(!(prevTopBarScrollHeight === topBarScrollHeight && prevMessagesHeight === messagesHeight)) {
			$messages.css("height", String(messagesHeight) + "px");
			$("#topBar").css("height", String(topBarScrollHeight) + "px");
			$messages.css("top", String(topBarScrollHeight) + "px");
			$messageBar.css("top", String($(window).height() - document.getElementById("messageBar").scrollHeight) + "px");
			$("#settingsMenu").css("top", String(topBarScrollHeight) + "px");
			$("#ouHeading").css("height", String(topBarScrollHeight) + "px");
			$("#userList").css("top", String(topBarScrollHeight) + "px");
			$("#userList").css("height", String($(window).height() - topBarScrollHeight) + "px");
			
			prevTopBarScrollHeight = topBarScrollHeight;
			prevMessagesHeight = messagesHeight;
		}
	}
	
	calibrateScreen();

	function queueOperations() {
		
		while(messageIdQueue.length) {
			prevMessageIdQueue.push(messageIdQueue.pop());
			if(prevMessageIdQueue.length > clientQueueSize) { prevMessageIdQueue.shift(); }
		}
		while(messageQueue.length) { messageQueue.pop(); }
		
	}
	
	function printMessages() {
		for(var i = 0; i < messageQueue.length; i++) {
			var m = $("<div/>", {
					"class": "message",
					html: $("<div/>").append($("<span/>", {
						"class": "nickname",
						html: messageQueue[i].n
					})).html() + " " + messageQueue[i].m
				});
			$messages.append(m);
		}
		var $messageVar = $(".message");
		for(var i = maxMessageSize; i < $messageVar.length; i++) {
			$messageVar.eq(i - maxMessageSize).remove();
		}
		
		if(autoScroll) { $("#messages").scrollTop(document.getElementById("messages").scrollHeight); }
		
		queueOperations();
	}
	
	function onReceive(json) {
		if(json.length > 0) {
			for(var i = 0; i < json.length; i++) {
				var messageId = json[i].md5;
				if($.inArray(messageId, prevMessageIdQueue) === -1) {
					messageIdQueue.push(messageId);
					messageQueue.push({ n: json[i].nickname, m: json[i].message });
				}
			}
			if(messageQueue.length > 0) { printMessages(); }
		}
	}

	function getMessage() {
		$.ajax({
			url: parentUrl+"GetMessage.php",
			data: {
				nickname: nickname
			},
			type: "GET",
			dataType: "json",
			success: function(json) {
				onReceive(json);
			}
		});
	}
	
	function receiveOnlineUsers(json) {
		$("#userList").empty();
		json.sort();
		var userList = $("#userList");
		for(var i = 0; i < json.length; i++) {
			userList.append($("<div/>", { "class": "user", html: json[i] }));
		}
	}
	
	function updateAndFetch() {
		$.ajax({
			url: parentUrl+"UpdateAndFetch.php",
			data: {
				nickname: nickname
			},
			type: "GET",
			dataType: "json",
			success: function(json) {
				receiveOnlineUsers(json);
			}
		});
	}
	
	function getNewNick() {
		$.ajax({
			url: parentUrl+"GetNewNick.php",
			dataType: "json",
			type: "GET",
			success: function(json) {
				nickname = json.newnick;
				updateAndFetch();
			}
		});
	}
	
	getNewNick();

	setInterval(getMessage, 2000);
	setInterval(calibrateScreen, 2000);
	setInterval(updateAndFetch, 8000);
});
