<?php
	include_once "includes/main.inc.php";
	header('content-type:application/json');
	
	if(isset($_REQUEST['task'])){
		if($_REQUEST['task']=='group_members' && isset($_REQUEST['gid'])){
			$filter = "(cn=".$_REQUEST['gid'].")";
			$attributes = array('cn','memberUID');
			$groupinfo = $ldap->search($filter, __LDAP_GROUP_OU__, $attributes);
			if($groupinfo['count']==0){
				echo json_encode(array(
					'code'=> 404,
					'msg'=> 'No such group'
				));
				exit();
			} else {
				$group = new group($ldap,$_REQUEST['gid']);
				$users = $group->get_users();
				echo json_encode(array(
					'code'=>200,
					'msg'=>'OK',
					'group'=>$_REQUEST['gid'],
					'members'=>$users
					));
					exit();
				}
		}
		if($_REQUEST['task']=='user' && isset($_REQUEST['uid'])){
			$user= new user($ldap,$_REQUEST['uid']);
			if($user->get_username()==null){
				echo json_encode(array(
					'code'=>404,
					'msg'=>'No such user'
				));
			} else {
				echo json_encode(array(
					'code'=>200,
					'msg'=>'OK',
					'user'=>$user->serializable()
				));
			}
			exit();
		}

		if($_REQUEST['task']=='add_to_group'){
			if(!isset($_REQUEST['username']) || !isset($_REQUEST['password'])){
				echo json_encode(array(
					'code'=> 401,
					'msg'=> 'Username/password required'
				));
				exit();
			} else if(!$ldap->bind('uid='.$_REQUEST['username'].','.__LDAP_PEOPLE_OU__,$_REQUEST['password'])) {
				echo  json_encode(array(
					'code'=> 401,
					'msg'=> 'Invalid username/password'
				));
				exit();
			} else {
				$ldap->set_bind_user('uid='.$_REQUEST['username'].','.__LDAP_PEOPLE_OU__);
				$ldap->set_bind_pass($_REQUEST['password']);
				
				if(!isset($_REQUEST['gid']) || !isset($_REQUEST['uid'])){
					echo json_encode(array(
						'code'=> 400,
						'msg'=> 'Invalid parameters'
					));
					exit();
				}
				$filter = "(cn=".$_REQUEST['gid'].")";
				$attributes = array('cn','memberUID');
				$groupinfo = $ldap->search($filter, __LDAP_GROUP_OU__, $attributes);
				if($groupinfo['count']==0){
					echo json_encode(array(
						'code'=> 404,
						'msg'=> 'No such group'
					));
					exit();
				} else {
					if(isset($groupinfo[0]['memberuid']) && in_array($_REQUEST['uid'],$groupinfo[0]['memberuid'])){
						echo json_encode(array(
							'code'=> 200,
							'msg'=> 'User already in group'
						));
					} else {
						$group = new group($ldap,$_REQUEST['gid']);
						$result = $group->add_user($_REQUEST['uid']);
						if($result['RESULT']){
							echo json_encode(array(
								'code'=> 200,
								'msg'=> 'User added'
							));
						} else {
							echo json_encode(array(
								'code'=> 500,
								'msg'=> $result['MESSAGE']
							));
						}
					}
				}
				exit();
			}
		}
		if($_REQUEST['task']=='remove_from_group'){
			if(!isset($_REQUEST['username']) || !isset($_REQUEST['password'])){
				echo json_encode(array(
					'code'=> 401,
					'msg'=> 'Username/password required'
				));
				exit();
			} else if(!$ldap->bind('uid='.$_REQUEST['username'].','.__LDAP_PEOPLE_OU__,$_REQUEST['password'])) {
				echo  json_encode(array(
					'code'=> 401,
					'msg'=> 'Invalid username/password'
				));
				exit();
			} else {
				$ldap->set_bind_user('uid='.$_REQUEST['username'].','.__LDAP_PEOPLE_OU__);
				$ldap->set_bind_pass($_REQUEST['password']);
				
				if(!isset($_REQUEST['gid']) || !isset($_REQUEST['uid'])){
					echo json_encode(array(
						'code'=> 400,
						'msg'=> 'Invalid parameters'
					));
					exit();
				}
				$filter = "(cn=".$_REQUEST['gid'].")";
				$attributes = array('cn','memberUID');
				$groupinfo = $ldap->search($filter, __LDAP_GROUP_OU__, $attributes);
				if($groupinfo['count']==0){
					echo json_encode(array(
						'code'=> 404,
						'msg'=> 'No such group'
					));
					exit();
				} else {
					if(isset($groupinfo[0]['memberuid']) && !in_array($_REQUEST['uid'],$groupinfo[0]['memberuid'])){
						echo json_encode(array(
							'code'=> 200,
							'msg'=> 'User not in group'
						));
					} else {
						$group = new group($ldap,$_REQUEST['gid']);
						$result = $group->remove_user($_REQUEST['uid']);
						if($result['RESULT']){
							echo json_encode(array(
								'code'=> 200,
								'msg'=> 'User removed'
							));
						} else {
							echo json_encode(array(
								'code'=> 500,
								'msg'=> print_r($_REQUEST,true)
							));
						}
					}
				}
				exit();
			}
		}
	}
	
		echo json_encode(array(
			'code'=> 418,
			'msg'=> 'I\'m a teapot'
		));
		
	