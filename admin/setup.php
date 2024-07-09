<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require('class/user.php');
require('class/database.php');

$values = array();

$msg="";
$smtp_keys = ['host' => 'text', 'user' => 'text', 'pass' => 'password', 'port' => 'number'];
$db_keys   = ['host' => 'text', 'user' => 'text', 'pass' => 'password', 'port' => 'number', 'name' => 'text'];

if(isset($_POST['submit'])){
	
	$database = new Database($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_port'], 'public');
	if(!$database->is_connected()){
		$msg = pg_last_error($databse->getConn());
	}else{
		
		$file_data  = "<?php\n";
		foreach($db_keys as $k => $t){
			$file_data .= "const DB_".strtoupper($k)." = '".$_POST['db_'.$k]."';\n";
		}
		$file_data .= "const DB_SCMA = 'public';\n";
		$file_data .= "const SUPER_ADMIN_ID = 1;\n";
		$file_data .= "const SESS_USR_KEY = 'q2w_user';\n";
		$file_data .= "const ACCESS_LEVELS = array('User', 'Admin');\n";
		$file_data .= "const APPS_DIR = '".$_POST['apps_dir']."';\n";
		$file_data .= "const DATA_DIR = '".$_POST['data_dir']."';\n";
		$file_data .= "const CACHE_DIR = '".$_POST['cache_dir']."';\n";
		
		$sent_enabled = isset($_POST['allow_sentinel_layers']) ? 'True' : 'False';
		$file_data .= "const SENTINEL_LAYERS_ENABLED = ".$sent_enabled.";\n";
		
		foreach($smtp_keys as $k => $t){
			$file_data .= "const SMTP_".strtoupper($k)." = '".$_POST['smtp_'.$k]."';\n";
		}

		file_put_contents('incl/const.php', $file_data);
		
		$con = $database->getConn();
		
		$sql = file_get_contents('setup.sql');
		$res = pg_query($con, $sql);
		if(!$res){
			echo pg_last_error($con);
			die();
		}
		
		$def_user = array('name' => $_POST['super_admin_name'], 'email' => $_POST['super_admin_email'], 'password' => $_POST['super_admin_pass'],
											'accesslevel' => 'Admin', 'ftp_user' => 'admin1', 'pg_password' => user_Class::randomPassword(), 'owner_id' => 1);
		$def_grp = array('name' => 'Default', 'owner_id' => 1);
		$def_usr_grps = array('user_id' => 1, 'access_group_id' => 1);

		$def_user['password'] = password_hash($def_user['password'], PASSWORD_DEFAULT);
		
		// insert manually
		if(!pg_insert($con, 'public.user', 					$def_user) ||
			 !pg_insert($con, 'public.access_groups', $def_grp)  ||
			 !pg_insert($con, 'public.user_access',		$def_usr_grps) 	){
			die(pg_last_error($con));
		}

		user_Class::create_ftp_user($def_user['ftp_user'], $def_user['email'], $def_user['password']);
		$database->create_user($def_user['ftp_user'], $def_user['pg_password']);
		
		if(!isset($_POST['allow_signup'])){
			
			$result = pg_query($con, 'DROP TABLE signup');
			
			unlink('../signup.php');
			unlink('class/signup.php');
			unlink('action/signup.php');
			unlink('action/verify.php');
		}
		
		unlink('setup.sql');
		unlink('setup.php');
		
		header('location:index.php');
	}
}

if(file_exists('incl/const.php')){
	require('incl/const.php');
}

$values['db_host'] = defined('DB_HOST') ? DB_HOST : 'localhost';
$values['db_port'] = defined('DB_PORT') ? DB_PORT : '5432';
$values['db_user'] = defined('DB_USER') ? DB_USER : '';
$values['db_pass'] = defined('DB_PASS') ? DB_PASS : '';
$values['db_name'] = defined('DB_NAME') ? DB_NAME : '';

$values['smtp_host'] = defined('SMTP_HOST') ? SMTP_HOST : '';
$values['smtp_port'] = defined('SMTP_PORT') ? SMTP_PORT : '';
$values['smtp_user'] = defined('SMTP_USER') ? SMTP_USER : '';
$values['smtp_pass'] = defined('SMTP_PASS') ? SMTP_PASS : '';

$values['apps_dir']	= defined('APPS_DIR') ? APPS_DIR : '/var/www/html/apps';
$values['data_dir']	= defined('DATA_DIR') ? DATA_DIR : '/var/www/data';
$values['cache_dir']= defined('CACHE_DIR')? CACHE_DIR: '/var/www/cache';
?>

<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>QuartzMap Installer</title>
			<link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
			<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
table{width:30% !important; text-align:center; margin:auto; margin-top:70px;}
.success{color:green;}
.error{color:red;}
.frm{width:70% !important; margin:auto; margin-top:100px;}
</style>

<script>
function pwd_vis(pwd_field_id) {
	var x = document.getElementById(pwd_field_id);
	var i = document.getElementById(pwd_field_id + '_vis_i');
	if (x.type === "password") {
		x.type = "text";
		i.innerHTML = "visibility_off";
	} else {
		x.type = "password";
		i.innerHTML = "visibility";
	}
}
</script>
	 </head>
   <body>

      <main role="main" class="container">
         <?php
			if((isset($_GET['step'])) && $_GET['step']==2){
				?>
				<div align="center"><p>&nbsp;</p>QuartzMap Installer</div>

				<form class="frm" method="post">
				<span class="error"><?=$msg?></span>
				
				<div>
					<fieldset>
						<legend>App</legend>
						<div class="form-group">
							<input type="text" class="form-control" placeholder="super admin name" id="super_admin_name" name="super_admin_name" value="John Doe">
							<input type="text" class="form-control" placeholder="super admin email" id="super_admin_email" name="super_admin_email" value="admin@admin.com">
							<input type="password" class="form-control" placeholder="super admin pass" id="super_admin_pass" name="super_admin_pass" value="1234">
							<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('super_admin_pass')">
								<i id="super_admin_pass_vis_i" class="material-icons" style="color:grey">visibility</i>
							</a>
							
							<input type="hidden" class="form-control" placeholder="apps_dir" id="apps_dir" name="apps_dir" value="<?=$values['apps_dir']?>">
							<input type="hidden" class="form-control" placeholder="data_dir" id="data_dir" name="data_dir" value="<?=$values['data_dir']?>">
							<input type="hidden" class="form-control" placeholder="cache_dir" id="cache_dir" name="cache_dir" value="<?=$values['cache_dir']?>">
						</div>
					</fieldset>
				</div>
				
				<div>
					<fieldset>
						<legend>Database</legend>
						<div class="form-group">
						<?php foreach($db_keys as $k => $t){ ?>
								<input type="<?=$t?>" class="form-control" placeholder="<?=$k?>" id="db_<?=$k?>" name="db_<?=$k?>" value="<?=$values['db_'.$k]?>" required>
								<?php if($k == 'pass'){ ?>
									<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('db_pass')">
										<i id="db_pass_vis_i" class="material-icons" style="color:grey">visibility</i>
									</a>
								<?php } ?>
						<?php } ?>
					</div>
				</fieldset>
			</div>
				
				<div>
					<fieldset>
						<legend>SMTP details:</legend>
						<div class="form-group">
						<?php foreach($smtp_keys as $k => $t){ ?>
							<input type="<?=$t?>" class="form-control" placeholder="<?=$k?>" id="smtp_<?=$k?>" name="smtp_<?=$k?>" value="<?=$values['smtp_'.$k]?>" required>
							<?php if($k == 'pass'){ ?>
								<a class="icon-link" href="#" title="Show Password" data-toggle="tooltip" data-placement="bottom" data-trigger="hover" onclick="pwd_vis('smtp_pass')">
									<i id="smtp_pass_vis_i" class="material-icons" style="color:grey">visibility</i>
								</a>
							<?php } ?>
						<?php } ?>
						</div>
					</fieldset>
				</div>
				
				<div>
					<fieldset>
					<legend>Options</legend>
						<div class="form-group">
							<input type="checkbox" class="form-checkbox" placeholder="signup allowed" name="allow_signup" value="1"/>
							<label for="allow_signup">Allow Sign-Up for Admin accounts</label>
						</div>
						<div class="form-group">
							<input type="checkbox" class="form-checkbox" placeholder="sentinel layers allowed" name="allow_sentinel_layers" value="1"/>
							<label for="allow_sentinel_layers">Allow Sentinel Layers</label>
						</div>
					</fieldset>
				</div>
				
				<div align="right">
				  <button type="submit" name="submit" class="btn btn-primary">Submit</button>
				</div>
			</form>

			<?php
		}else{
		?>

	  <div align="center"><p>&nbsp;</p>QuartzMap Installer</div>

         <table class="table">
		  <thead>
			<tr>
			  <th scope="col">Requirement</th>
			  <th scope="col">Status</th>
			</tr>
		  </thead>
		  <tbody>
			<tr>
			  <th scope="row">PHP Version</th>
			  <td>
				<?php
					$is_error="";
					$php_version=phpversion();
					if($php_version>5){
						echo "<span class='success'>".$php_version."</span>";
					}else{
						echo "<span class='error'>".$php_version."</span>";
						$is_error='yes';
					}
				?>
			  </td>
			</tr>
			<tr>
			  <th scope="row">Session Working</th>
			  <td>
				<?php
				$_SESSION['IS_WORKING']=1;
				if(!empty($_SESSION['IS_WORKING'])){
					echo "<span class='success'>Yes</span>";
				}else{
					echo "<span class='error'>No</span>";
					$is_error='yes';
				}
				?>
			  </td>
			</tr>

			<?php
				$app_dirs = array('apps', 'data', 'cache');
				foreach($app_dirs as $d){ ?>
					<tr>
						<th scope="row"><?=$values[$d.'_dir']?></th>
						<td>
						<?php
						if(is_writeable($values[$d.'_dir'])){
							echo "<span class='success'>Writeable</span>";
						}else{
							echo "<span class='error'>Not writeable</span>";
							$is_error='yes';
						}
						?>
						</td>
					</tr>
				<?php }
			?>

			<tr>
			  <td colspan="2">
				<?php
				if($is_error==''){
					?>
					<a href="?step=2"><button type="button" class="btn btn-success">Next</button></a>
					<?php
				}else{
					?><button type="button" class="btn btn-danger">Errors</button><br><br>Please fix above error(s) and try again<?php
				}
				?>
			  </td>
			</tr>
		  </tbody>

		</table>
		<?php }?>

      </main>

      <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js"></script>
   </body>
</html>
