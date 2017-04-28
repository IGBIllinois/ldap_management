function ext_base_username_validate(){
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
	showValidateWarning('username',1,warning1,warning1?"Username matches a UIUC netid":"Username does not match a UIUC netid");
	if(document.getElementById('emailforward_input') != null){
		if(warning1){
			document.getElementById('emailforward_input').value = document.getElementById('username_input').value + "@illinois.edu";
		} else {
			document.getElementById('emailforward_input').value = '';
		}
	}
	
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

	showValidateError('username',1,rule1==0,rule1==0?"Username not in use":(rule1==1?"Username already exists":"Username exists as group"));
	showValidateError('username',2,rule2,"Username must begin with a lowercase letter");
	showValidateError('username',3,rule3,"Username must be alphanumeric (letters, numbers, underscore)");
	
	return rule1==0 && rule2 && rule3;
}