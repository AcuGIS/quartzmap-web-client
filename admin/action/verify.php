<?php
	session_start(['read_and_close' => true]);
	
	require('../incl/const.php');
	require('../class/database.php');
	require('../class/signup.php');
	require('../class/user.php');
	require('../class/access_groups.php');
	
	$loc = '../../signup.php?err='.urlencode('Error: Bad verify request!');
	
  if(	!empty($_GET['id']) && !empty($_GET['verify']) ){
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
  	$obj = new signup_Class($database->getConn());
		
		$result = $obj->verify($_GET['id'], $_GET['verify']);
		if(!$result || (pg_num_rows($result) > 0)){
			
			$row = pg_fetch_assoc($result);
			pg_free_result($result);
 
			$row['accesslevel'] = 'Admin';
			$row['groups'] = [1];	// Default group

			$email_user = explode('@', $row['email'])[0];
			$row['ftp_user'] = $email_user.$row['id'];
			
			// create a new user
			$uobj = new user_Class($database->getConn(), SUPER_ADMIN_ID);
			$newId = $uobj->create($row, true);
			if($newId){
				user_Class::create_ftp_user($row['ftp_user'], $row['email'], $row['password']);
				
				// create def access group for new admin
				$def_grp = array('name' => $row['ftp_user'], 'userids' => array($newId));
				$acc_obj = new access_group_Class($database->getConn(), $newId);
				$grp_id = $acc_obj->create($def_grp);
				
				if($grp_id > 0){
					$row['id'] = $newId;
					$row['groups'] = array($grp_id);
					$uobj->update($row);
					
					$obj->delete($_GET['id']);			// remove signup request
					$loc = '../../login.php?msg='.urlencode('Congratulations '.$row['name'].'. Your verification is successfull !');
				}else{
					$loc = '../../signup.php?err='.urlencode('Error: Failed to create group!');
				}
			}else{
				$loc = '../../signup.php?err='.urlencode('Error: Failed to create user!');
			}
			
		}else{	// error
			$loc = '../../signup.php?err='.urlencode('Error: Invalid verify request!');
		}
	}
	
	header('Location: '.$loc);
?>