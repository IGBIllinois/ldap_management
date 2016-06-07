function confirm_disable_user() {
	return confirm("Are you sure you wish to delete this user? This operation cannot be undone.");
}

function sort_table(column){
	// Build new url
	var currentURL = window.location.href;
	var URLMatch = currentURL.match(/sort=(.*?)(&|$)/);
	if(URLMatch == null){
		// Add sort to the end of the string
		if (currentURL.indexOf('?') > -1){
			window.location.href=currentURL+"&sort="+column;
		} else {
			window.location.href=currentURL+"?sort="+column;
		}
	} else {
		// Replace in place
		var ascMatch = currentURL.match(/asc=(.*?)(&|$)/);
		if(URLMatch[1] == column){
			// Clicked twice, flip asc
			if(ascMatch == null){
				window.location.href=currentURL+"&asc=false";
			} else if(ascMatch[1] == "true") {
				window.location.href=currentURL.replace(/asc=(.*?)(&|$)/g, "asc=false");
			} else {
				window.location.href=currentURL.replace(/asc=(.*?)(&|$)/g, "asc=true");
			}
		} else {
			// New column, default asc
			if(ascMatch == null){
				window.location.href=currentURL.replace(/sort=(.*?)(&|$)/g, "sort="+column+"$2");
			} else {
				window.location.href=currentURL.replace(/sort=(.*?)(&|$)/g, "sort="+column+"$2").replace(/asc=(.*?)(&|$)/g, "asc=true");
			}
		}
	}
}

function show_classroom_text(){
	var prefix = $('#prefix-input').val();
	var num = $('#num-input').val();
	if(prefix == ""){
		$('#classroom-txt').html('<div class="glyphicon glyphicon-remove"></div> Please enter a username prefix').removeClass('text-success').addClass('text-danger');	
		document.getElementById('add_user_submit').disabled = true;
	} else if(num == "" || num<2){
		$('#classroom-txt').html('<div class="glyphicon glyphicon-remove"></div> Number must be > 1').removeClass('text-success').addClass('text-danger');
		document.getElementById('add_user_submit').disabled = true;
	} else {
		var onepadded = "1";
		var log = Math.floor(Math.log(num)/Math.log(10));
		console.log(log);
		for (var i=0; i<log; i++){
			onepadded = "0"+onepadded;
		}
		$('#classroom-txt').html('<div class="glyphicon glyphicon-ok"></div> Will create/clean up users '+prefix+onepadded+'-'+prefix+num).removeClass('text-danger').addClass('text-success');
		document.getElementById('add_user_submit').disabled = false;
	}
}

function copy_panel(event){
	var $this = $(this);
	var $textarea = $this.parents('.panel').find('.copy-text');
	var showTextArea = true;
	if(document.queryCommandSupported('copy')){
		showTextArea = false;
		$textarea.removeClass('hidden');
		
		var range = document.createRange();
		range.selectNode($textarea[0]);
		window.getSelection().addRange(range);
		
		try {
			var success = document.execCommand('copy');
		} catch(err) {
			showTextArea = true;
		}
	
		$textarea.addClass('hidden');
		window.getSelection().removeAllRanges();
	}
	if(showTextArea){
		$textarea.removeClass('hidden');
	}
}


function showPasswordError(rulenum,valid,text){
	if(valid){
		$('#passworderror'+rulenum).removeClass('text-danger').addClass('text-success');
		$('#passworderror'+rulenum+' .glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok');
	} else {
		$('#passworderror'+rulenum).removeClass('text-success').addClass('text-danger');
		$('#passworderror'+rulenum+' .glyphicon').removeClass('glyphicon-ok').addClass('glyphicon-remove');
	}
	$('#passworderror'+rulenum+' .text').html(text);
}

function showUsernameWarning(warnnum,valid,text){
	if(valid){
		$('#usernamewarning'+warnnum).removeClass('text-warning').addClass('text-success');
		$('#usernamewarning'+warnnum+' .glyphicon').removeClass('glyphicon-alert').addClass('glyphicon-ok');
	} else {
		$('#usernamewarning'+warnnum).removeClass('text-success').addClass('text-warning');
		$('#usernamewarning'+warnnum+' .glyphicon').removeClass('glyphicon-ok').addClass('glyphicon-alert');
	}
	$('#usernamewarning'+warnnum+' .text').html(text);
}

function showUsernameError(errornum,valid,text){
	if(valid){
		$('#usernameerror'+errornum).removeClass('text-danger').addClass('text-success');
		$('#usernameerror'+errornum+' .glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok');
	} else {
		$('#usernameerror'+errornum).removeClass('text-success').addClass('text-danger');
		$('#usernameerror'+errornum+' .glyphicon').removeClass('glyphicon-ok').addClass('glyphicon-remove');
	}
	$('#usernameerror'+errornum+' .text').html(text);
}

function showGroupnameError(errornum,valid,text){
	if(valid){
		$('#groupnameerror'+errornum).removeClass('text-danger').addClass('text-success');
		$('#groupnameerror'+errornum+' .glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok');
	} else {
		$('#groupnameerror'+errornum).removeClass('text-success').addClass('text-danger');
		$('#groupnameerror'+errornum+' .glyphicon').removeClass('glyphicon-ok').addClass('glyphicon-remove');
	}
	$('#groupnameerror'+errornum+' .text').html(text);
}

function check_passwords(){
	var passworda = document.getElementById('passworda_input').value;
	var passwordb = document.getElementById('passwordb_input').value;
	
	var rule1 = ( passworda.length >= 8 && passworda.length <= 15 );
	var rule2 = ( passworda.match(/[A-Z]/) );
	var rule3 = ( passworda.match(/[a-z]/) );
	var rule4 = ( passworda.match(/[^A-Za-z]/) && !passworda.match(/[\s]/) );
	var rule5 = ( passworda == passwordb );
	
	showPasswordError(1,rule1,"Password must be between 8 and 15 characters in length");
	showPasswordError(2,rule2,"Password must contain at least 1 uppercase letter");
	showPasswordError(3,rule3,"Password must contain at least 1 lowercase letter");
	showPasswordError(4,rule4,"Password must contain at least 1 number or special character (no spaces)");
	showPasswordError(5,rule5,"Password and confirm password must match");
	
	return rule1 && rule2 && rule3 && rule4 && rule5;
}

function add_user_errors(){
	var password_errors = check_passwords();
	var username_errors = check_username();
	
	if (password_errors && username_errors){
		document.getElementById('add_user_submit').disabled = false;
	} else {
		document.getElementById('add_user_submit').disabled = true;
	}
}

function change_username_errors(){
	var username_errors = check_username();
	
	if(username_errors){
		document.getElementById('change_username_submit').disabled = false;
	} else {
		document.getElementById('change_username_submit').disabled = true;
	}
}

function change_group_errors(){
	var group_errors = check_groupname();
	
	if(group_errors){
		document.getElementById('group_submit').disabled = false;
	} else {
		document.getElementById('group_submit').disabled = true;
	}
}

function change_password_errors(){
	var password_errors = check_passwords();
	
	if(password_errors){
		document.getElementById('change_password_submit').disabled = false;
	} else {
		document.getElementById('change_password_submit').disabled = true;
	}
}

function check_username(){
	var username = document.getElementById('username_input').value;
	var warning1 = false;
	$.ajax('check_netid.php',{
		async: false,
		data: {'username':username},
		method: 'POST',
		success: function(data){
			if(data == '1'){
				warning1 = true;
			}
		}
	});
	showUsernameWarning(1,warning1,warning1?"Username matches a UIUC netid":"Username does not match a UIUC netid");
	
	var rule1 = true;
	$.ajax('check_username.php',{
		async: false,
		data: {'username':username},
		method: 'POST',
		success: function(data){
			rule1 = data;
		}
	});
	var rule2 = ( username.match(/^[a-z]/) );
	var rule3 = !( username.match(/[^A-Za-z0-9_]/) );

	showUsernameError(1,rule1==0,rule1==0?"Username not in use":(rule1==1?"Username already exists":"Username exists as group"));
	showUsernameError(2,rule2,"Username must begin with a lowercase letter");
	showUsernameError(3,rule3,"Username must be alphanumeric (letters, numbers, underscore)");
	
	return rule1==0 && rule2 && rule3;
}

function check_groupname(){
	var name = document.getElementById('name_input').value;
	var rule1 = true;
	$.ajax('check_username.php',{
		async: false,
		data: {'username':name},
		method: 'POST',
		success: function(data){
			rule1 = data;
		}
	});
	var rule2 = ( name.match(/^[a-z]/) );
	var rule3 = !( name.match(/[^A-Za-z0-9_]/) );

	showGroupnameError(1,rule1==0,rule1==0?"Name not in use":(rule1==1?"Name exists as user":"Name already exists"));
	showGroupnameError(2,rule2,"Name must begin with a lowercase letter");
	showGroupnameError(3,rule3,"Name must be alphanumeric (letters, numbers, underscore)");
	
	return rule1==0 && rule2 && rule3;
}

$(document).ready(function(){
	$('.copy-button').click(copy_panel);
});
