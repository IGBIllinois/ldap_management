function confirm_disable_user() {
	return confirm("Are you sure you wish to delete this user? This operation cannot be undone.");
}

function toggleClasses(e,onClass,offClass){
	if(e.hasClass(onClass)){
		e.addClass(offClass);
		e.removeClass(onClass);
	} else {
		e.addClass(onClass);
		e.removeClass(offClass);
	}
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

function filter_table(filter){
	// Build new URL
	var currentURL = window.location.href;
	var URLMatch = currentURL.match(/filter=(.*?)(&|$)/);
	if(URLMatch == null){
		// Add filter to the end of the URL
		if(currentURL.indexOf('?')>-1){
			window.location.href=currentURL+"&filter="+filter;
		} else {
			window.location.href=currentURL+"?filter="+filter;
		}
	} else {
		// Replace filter in place
		if(URLMatch[1] == filter){
			// Clicked twice, no filter
			window.location.href=currentURL.replace(/filter=(.*?)(&|$)/g, "filter=none$2");
		} else {
			window.location.href=currentURL.replace(/filter=(.*?)(&|$)/g, "filter="+filter+"$2");
		}
	}
}

function show_add_classroom_text(){
	var prefix = $('#prefix-input').val();
	var num = $('#num-input').val();
	if(prefix == ""){
		$('#classroom-txt').html('<div class="glyphicon glyphicon-remove"></div> Please enter a username prefix').removeClass('text-success').addClass('text-danger');	
		document.getElementById('add_user_submit').disabled = true;
	} else if(num == "" || num<2){
		$('#classroom-txt').html('<div class="glyphicon glyphicon-remove"></div> Number must be > 1').removeClass('text-success').addClass('text-danger');
		document.getElementById('add_user_submit').disabled = true;
	} else {
		$('#classroom-txt').html('<div class="glyphicon glyphicon-ok"></div> Will create/clean up users '+prefix+'01-'+prefix+(num<10?'0':'')+num).removeClass('text-danger').addClass('text-success');
		document.getElementById('add_user_submit').disabled = false;
	}
}

function show_remove_classroom_text(){
	var prefix = $('#prefix-input').val();
	var start = $('#start-input').val();
	var end = $('#end-input').val();
	var paddedstart = start<10?'0'+start:start;
	var paddedend = end<10?'0'+end:end;
	
	var rule1 = 0;
	$.ajax('check_username.php',{
		async: false,
		data: {'username':prefix+paddedstart},
		method: 'POST',
		success: function(data){
			rule1 = data;
		}
	});
	rule1 = rule1==1?true:false;
	
	var rule2 = start>0;
	var rule3 = end>start;
	
	var error = prefix+paddedstart+'-'+prefix+paddedend+' will be removed';
	if(prefix == ""){
		error = "Please enter a prefix.";
	} else if (!isInt(start) || Number(start)<1){
		error = "Start must be >=1";
	} else if (!isInt(end) || Number(end)<=Number(start)){
		error = "End must be greater than start";
	} else if (!rule1){
		error = prefix+paddedstart+" does not exist";
	}
	
	showUsernameError(1,rule1 && rule2 && rule3,error);
	document.getElementById('remove_user_submit').disabled = !( rule1 && rule2 && rule3 );
}

function copy_panel(event){
	var $this = $(this);
	var $textarea = $this.parents('.panel').find('.copy-text');
	var showTextArea = true;
	if(document.queryCommandSupported('copy')){
		showTextArea = false;
		$textarea.removeClass('hidden');
		$textarea[0].select();
		
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
	
	var rule1 = ( passworda.length >= 8 && passworda.length <= 12 );
	var rule2 = ( passworda.match(/[A-Z]/) );
	var rule3 = ( passworda.match(/[a-z]/) );
	var rule4 = ( passworda.match(/[^A-Za-z]/) && !passworda.match(/[\s]/) );
	var rule5 = ( passworda == passwordb );
	
	showPasswordError(1,rule1,"Password must be between 8 and 12 characters in length");
	showPasswordError(2,rule2,"Password must contain at least 1 uppercase letter");
	showPasswordError(3,rule3,"Password must contain at least 1 lowercase letter");
	showPasswordError(4,rule4,"Password must contain at least 1 number or special character (no spaces)");
	showPasswordError(5,rule5,"Password and confirm password must match");
	
	return rule1 && rule2 && rule3 && rule4 && rule5;
}

function add_user_errors(username_errors,password_errors){
	if(password_errors==null){
		password_errors = check_passwords();
		
	}
	if(username_errors==null){
		username_errors = check_username();		
	}
	
	if (password_errors && username_errors){
		document.getElementById('add_user_submit').disabled = false;
	} else {
		document.getElementById('add_user_submit').disabled = true;
	}
	
	return [username_errors,password_errors];
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
	var rule2 = ( username.match(/^[a-z]/)!=null );
	var rule3 = ( username.match(/[^A-Za-z0-9_]/)==null );

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

function random_password(length){
	var randomchars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@$%&';
	var password = '';
	var array = new Uint8Array(length);
	window.crypto.getRandomValues(array);
	for(var i=0; i<length; i++){
		password += randomchars.charAt(array[i]%randomchars.length);
	}
	return password;
}

function generate_password(){
	do {
		var password = random_password(8);
		$('#password-text').html(password);
		$('#passworda_input').val(password);
		$('#passwordb_input').val(password);
	} while(!check_passwords());
}

function isInt(value) {
  return !isNaN(value) && 
         parseInt(Number(value)) == value && 
         !isNaN(parseInt(value, 10));
}

$(document).ready(function(){
	$('.copy-button').click(copy_panel);
});
