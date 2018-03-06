<?php
	// TODO validate GET
	require_once "includes/main.inc.php";
	$attr = extensions::get_attribute('user',$_REQUEST['attr']);
	$title = "Remove ".$attr['fullname'];
	$sitearea = "users";
	require_once 'includes/header.inc.php';
	
	$fields = array();
	if(isset($attr['button']['inputs'])){
		$fields = $attr['button']['inputs'];
	} else {
		$fields[] = array("name"=>$attr['name'],"fullname"=>$attr['fullname'],"field"=>$attr['field'],"format"=>$attr['format']);
	}
	
	$user = new user($ldap,$_REQUEST['uid']);
	
	$message="";
	if(isset($_POST['remove_'.$attr['name']])){
		// TODO implement form action
		$_POST = array_map("trim",$_POST);
		if($_POST['uid']=="" || !user::is_ldap_user($ldap,$_POST['uid'])){
			$message .= html::error_message("Invalid username. Please stop trying to break my web interface.");
		} else {
			$classname = 'ext_'.$attr['ext'];
			$funcname = $attr['name'].'_remove';
			if(method_exists($classname, $funcname)){
				// Call remove function, if it exists
				$inputs = array();
				for($i=0; $i<count($fields); $i++){
					if(isset($fields[$i]['name'])){
						$inputs[$fields[$i]['name']] = $_POST[$fields[$i]['name']];
					}
				}
				$result = $classname::$funcname($ldap,$_POST['uid']);
				if($result['RESULT']){
					header("Location: user.php?uid=".$result['uid']);
					unset($_POST);
					exit;
				} else {
					$message .= html::error_message($result['MESSAGE']);
				}
			} else {
				// Default remove action
				if(count($fields)==1){
					// Multi-field attributes *must* have custom edit functions
					if(isset($fields[0]['options']) && !in_array($_POST[$fields[0]['name']],$fields[0]['options'])){
						$message .= html::error_message("Invalid option given for ".$fields[0]['fullname'].".");
					} else {
						// Finally do the edit
						$result = $user->remove_attribute($attr['field']);
						if($result['RESULT']){
							header("Location: user.php?uid=".$result['uid']);
							unset($_POST);
							exit;
						} else {
							$message .= html::error_message($result['MESSAGE']);
						}
					}
				} else {
					$message .= html::error_message("Extension error: multi-field attributes must have custom remove functions.");
				}
			}
		}
	} else if(isset($_POST['cancel_remove'])){
		header('location: user.php?uid='.$_POST['uid']);
		unset($_POST);
		exit;
	}
	$searchdescription = html::get_list_users_description_from_cookies();
	
	?>
<div class="minijumbo"><div class="container">
	<?php
		if(isset($attr['button']['title'])){
			$title = $attr['button']['title'];
		} else {
			$title = 'Remove '.$attr['fullname'];
		}
		echo $title;
	?>
	<nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo html::get_list_users_url_from_cookies(); ?>">Users<?php if($searchdescription!=""){echo " ($searchdescription)";} ?></a></li><li class="breadcrumb-item"><a href="user.php?uid=<?php echo $user->get_username(); ?>"><?php echo $user->get_username(); ?></a></li><li class="breadcrumb-item active"><?php echo $title; ?></li></ol></nav>
</div></div>
<div class="container">
<form class="mt-4" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>" name="form">
	<fieldset>
		<legend>
		
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
					<label class="col-sm-4 col-form-label"><?php if(isset($fields[$i]['fullname'])){ echo $fields[$i]['fullname'].':'; } ?></label>
					<div class="col-sm-8">
						<?php
							if (isset($fields[$i]['type']) && $fields[$i]['type']=='text'){
								// Text
								echo $fields[$i]['text'];
							} else {
								?>
						<label class="col-form-label"><?php echo extensions::format($user->get_attribute($fields[$i]['field']),isset($fields[$i]['format'])?$fields[$i]['format']:''); ?></label>
						<?php } ?>
					</div>
				</div>
					<?php
					}
					?>
				<div class="form-group row">
					<div class="col-sm-8 offset-sm-4">
						<div class="btn-group">
							<?php 
							$color = "btn-danger";
							$text = "Remove ".$attr['fullname'];
							if(isset($attr['button']['submit'])){
								if(isset($attr['button']['submit']['color'])){
									$color = "btn-".$attr['button']['submit']['color'];
								}
								if(isset($attr['button']['submit']['text'])){
									$text = $attr['button']['submit']['text'];
								}
							} ?>
							<input class="btn <?php echo $color; ?>" type="submit" name="remove_<?php echo $attr['name'];?>" id="change_<?php echo $attr['name'];?>_submit" value="<?php echo $text;?>"/>
							<input class="btn btn-light" type="submit" name="cancel_remove" value="Cancel"/>
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
	echo '$(document).ready(function(){'."\n";
	for($i=0;$i<count($fields);$i++){
		echo "\t".'$("#'.$fields[$i]['name'].'_input").on("input",'.$jsfunc.');'."\n";
	}
	echo "\t".$jsfunc.'();'."\n";
	echo '});</script>';// TODO validation needs to actually disable the submit button
}
?>

<?php if(isset($message))echo $message;
	require_once 'includes/footer.inc.php';
	?>