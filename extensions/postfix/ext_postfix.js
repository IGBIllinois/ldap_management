function ext_postfix_emailforward_validate(){
	var email = document.getElementById('emailforward_input').value;
	
	var rule1 = ( email.length==0 || email.match(/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.[a-zA-Z0-9\._-]+$/) );
	
	showValidateError('emailforward',1,rule1,rule1?"Valid forwarding email":"Invalid forwarding email");
	
	return rule1;
}
