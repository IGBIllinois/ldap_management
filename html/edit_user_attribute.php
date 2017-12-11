<?php
	// TODO validate GET
	require_once "includes/main.inc.php";
	$attr = extensions::get_attribute('user',$_REQUEST['attr']);
	$title = "Edit ".$attr['fullname'];
	$sitearea = "users";
	require_once 'includes/header.inc.php';
	
	$fields = array();
	if(isset($attr['button']['inputs'])){
		$fields = $attr['button']['inputs'];
	} else {
		$fields[] = array("name"=>$attr['name'],"fullname"=>$attr['fullname']);
	}
	
	$user = new user($ldap,$_REQUEST['uid']);
	
	$message="";
	if(isset($_POST['change_'.$attr['name']])){
		// TODO implement form action
		foreach($_POST as $var){
			$var = trim(rtrim($var));
		}
		if($_POST['uid']=="" || !user::is_ldap_user($ldap,$_POST['uid'])){
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		} else {
			$classname = 'ext_'.$attr['ext'];
			$funcname = $attr['name'].'_edit';
			if(method_exists($classname, $funcname)){
				// call extension function, if it exists
				$inputs = array();
				for($i=0; $i<count($fields); $i++){
					if(isset($fields[$i]['name'])){
						$inputs[$fields[$i]['name']] = $_POST[$fields[$i]['name']];
					}
				}
				$result = $classname::$funcname($ldap,$_POST['uid'],$inputs);
				if($result['RESULT']){
					header("Location: user.php?uid=".$result['uid']);
					unset($_POST);
					exit;
				} else {
					$message .= html::error_message($result['MESSAGE']);
				}
			} else {
				// Default edit action
				if(count($fields)==1){
					// Multi-field attributes *must* have custom edit functions
					if(isset($fields[0]['options']) && !in_array($_POST[$fields[0]['name']],$fields[0]['options'])){
						$message .= html::error_message("Invalid option given for ".$fields[0]['fullname'].".");
					} else {
						// If the attribute has a format set, parse the input
						if(isset($attr['format'])){
							if($attr['format'] == 'timestamp'){
								if(!strtotime($_POST[$fields[0]['name']])){
									$message .= html::error_message("Invalid date format");
								} else {
									$_POST[$fields[0]['name']] = strtotime($_POST[$fields[0]['name']]);
								}
							}
						}
						
						if($message == ""){
							// Finally do the edit
							$result = $user->set_attribute($attr['field'],$_POST[$fields[0]['name']]);
							if($result['RESULT']){
								header("Location: user.php?uid=".$result['uid']);
								unset($_POST);
								exit;
							} else {
								$message .= html::error_message($result['MESSAGE']);
							}
						}
					}
				} else {
					$message .= html::error_message("Extension error: multi-field attributes must have custom edit functions.");
				}
			}
		}
	} else if(isset($_POST['cancel_change'])){
		header('location: user.php?uid='.$_POST['uid']);
		unset($_POST);
		exit;
	}
	
	?>
<form class="mt-4" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>" name="form">
	<fieldset>
		<legend>
		<?php
			if(isset($attr['button']['title'])){
				$title = $attr['button']['title'];
			} else {
				$title = 'Change '.$attr['fullname'];
			}
			echo $title;
		?>
		</legend>
		<hr>
		<div class="row">
			<div class="col-sm-8">
				<div class="form-group row">
					<label class="col-sm-4 col-form-label">Username:</label>
					<div class="col-sm-8">
						<label class="col-form-label"><?php echo $user->get_username(); ?></label>
						<input type="hidden" name="uid" value="<?php echo $_REQUEST['uid']; ?>"/>
						<input type="hidden" name="attr" value="<?php echo $_REQUEST['attr']; ?>"/>
					</div>
				</div>
				<?php
					for($i=0; $i<count($fields); $i++){
					?>
				<div class="form-group row">
					<label class="col-sm-4 col-form-label"><?php if(isset($fields[$i]['fullname'])){ ?>New <?php echo $fields[$i]['fullname'];?>:<?php } ?></label>
					<div class="col-sm-8">
						<?php if(isset($fields[$i]['type']) && $fields[$i]['type']=='select'){
							// Dropdown menu
							echo "<select class='form-control' name='".$fields[$i]['name']."' id='".$fields[$i]['name']."_input'>";
							// TODO initially select user's current value for this field
							for($option=0; $option<count($fields[$i]['options']);$option++){
								echo "<option value='".$fields[$i]['options'][$option]."'>".$fields[$i]['options'][$option]."</option>";
							}
							echo "</select>";
						} else if (isset($fields[$i]['type']) && $fields[$i]['type']=='text'){
							// Text
							echo $fields[$i]['text'];
						} else {
							// Text input
							$placeholder = "";
							if(isset($fields[$i]['placeholder'])){
								$placeholder = $fields[$i]['placeholder'];
							}
							echo '<input class="form-control" name="'.$fields[$i]['name'].'" id="'.$attr['name'].'_input" value="" placeholder="'.$placeholder.'"/>';
						} ?>
					</div>
				</div>
					<?php	
					}
					?>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<?php 
							$color = "btn-info";
							$text = "Change ".$attr['fullname'];
							$onclick = "";
							if(isset($attr['button']['submit'])){
								if(isset($attr['button']['submit']['color'])){
									$color = "btn-".$attr['button']['submit']['color'];
								}
								if(isset($attr['button']['submit']['text'])){
									$text = $attr['button']['submit']['text'];
								}
								if(isset($attr['button']['submit']['onclick'])){
									$onclick = $attr['button']['submit']['onclick'];
								}
							} ?>
							<input class="btn <?php echo $color; ?>" onclick="<?php echo $onclick;?>" type="submit" name="change_<?php echo $attr['name'];?>" id="change_<?php echo $attr['name'];?>_submit" value="<?php echo $text;?>"/>
							<input class="btn btn-light" type="submit" name="cancel_change" value="Cancel"/>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-4" id="validation">
				
			</div>
		</div>
	</fieldset>
</form>

<?php
// Load validation, if any
$jsfile = '../extensions/'.$attr['ext'].'/ext_'.$attr['ext'].'.js';
$jsfunc = 'ext_'.$attr['ext'].'_'.$attr['name'].'_validate';
if(file_exists($jsfile) && strpos(file_get_contents($jsfile),$jsfunc)!==false){
	echo '<script type="text/javascript">';
	include $jsfile;
	echo 'function validateform(){
		document.getElementById("change_'.$attr['name'].'_submit").disabled = !'.$jsfunc.'();
	}'."\n";
	echo '$(document).ready(function(){'."\n";
	for($i=0;$i<count($fields);$i++){
		echo "\t".'$("#'.$fields[$i]['name'].'_input").on("input",validateform);'."\n";
	}
	echo "\t".'validateform();'."\n";
	echo '});</script>';// TODO validation needs to actually disable the submit button
}
?>

<?php if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
	?>