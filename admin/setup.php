<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require('class/user.php');
require('class/database.php');
require('class/app.php');

function post_example($auth, $map){
	$proto = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
	$post_url = $proto.'://'.$_SERVER['HTTP_HOST'].str_replace('setup.php', 'action', $_SERVER['PHP_SELF']);
	$cookie = '/tmp/sample.cookie';
	
	// login
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
	curl_setopt($ch, CURLOPT_URL, $post_url.'/login.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
	$response = curl_exec($ch);
	
	// post map
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, null);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $post_url.'/map.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $map);
	$response = curl_exec($ch);
	$response_js = json_decode($response);
	if($response_js == null){
		$fp = fopen('/tmp/response.txt', 'a');
		fwrite($fp, '[post_example] '.$response."\n");
		fclose($fp);
	}
	
	curl_close($ch);
	return $response_js->id;
}

function load_r_examples($database, $pgl){

	$auth = ['email' => $_POST['super_admin_email'], 'pwd' => $_POST['super_admin_pass'], 'submit' => 'Submit'];
	
	# create 3js map	
	$zip_file = curl_file_create('../examples/threejs.zip','application/zip', 'threejs.zip');
	$map = ['save' => 1, 'name' => 'Qgis2threejs Map', 'description' => 'Simple Bee qgis2threejs Map',
			'from_type' => 'archive', 'archive' => $zip_file, 'accgrps[]' => 1,
			'infobox_content' => '<p>ThreeJS map with WebGL</p>',
			'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create plotly_pg
	$map_source_r = file_get_contents('../examples/plotly_pg/index.R');
	$map_source_r = str_replace(['$DB_NAME', '$DB_USER', '$DB_PASS'], [$pgl['name'], $pgl['username'], $pgl['password']], $map_source_r);
	
	$map = ['save' => 1, 'name' => 'Simple Bee Harvest', 'description' => 'Postgres Plotly Chart',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>Apiary average harvest per area ID.</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);

	# create multiple_charts
	$map_source_r = file_get_contents('../examples/multiple_charts/index.R');
	$map = ['save' => 1, 'name' => 'Multiple Charts', 'description' => 'Multiple Charts with Plotlyjs',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>Plot of multiple charts.</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create choropleth
	$map_source_r = file_get_contents('../examples/choropleth/index.R');
	$map = ['save' => 1, 'name' => 'R Choropleth', 'description' => 'Density of USA',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>Choropleth map with R/Leaflet and data from GeoJSON</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create covid.R
	$zip_file = curl_file_create('../examples/covid1.zip','application/zip', 'covid1.zip');
	$map = ['save' => 1, 'name' => 'R Animated', 'description' => 'R Animated',
		'from_type' => 'archive', 'archive' => $zip_file, 'accgrps[]' => 1,
		'infobox_content' => '<p>Choropleth map with R/Leaflet and data from CSV</p>',
		'thismap_css' => '/* map specific CSS */'
	];
	post_example($auth, $map);
	
	# create tables
	$zip_file = curl_file_create('../examples/tables.zip','application/zip', 'tables.zip');
	$map = ['save' => 1, 'name' => 'R Tables', 'description' => 'R Tables with KableExtra',
		'from_type' => 'archive', 'archive' => $zip_file, 'accgrps[]' => 1,
		'infobox_content' => '<p>Page with R/KabelExtra tables</p>',
		'thismap_css' => '/* map specific CSS */', 'cron_period' => 'never', 'cron_custom' => '*/30 * * * *'
	];
	post_example($auth, $map);
	
	# create report1
	$map_source_r = file_get_contents('../examples/report1/skimr0.Rmd');
	$map = ['save' => 1, 'name' => 'RMarkdow Report', 'description' => 'My Super Fancy Report',
		'from_type' => 'code', 'map_source_r0' => $map_source_r, 'accgrps[]' => 1,
		'infobox_content' => '<p>RMarkdown report example</p>',
		'thismap_css' => '/* map specific CSS */', 'cron_period' => 'never', 'cron_custom' => '*/30 * * * *'
	];
	post_example($auth, $map);
}

function load_simple_bee($database, $pgl){

	// create db
	if(!$database->create_user_db($pgl['dbname'], $pgl['username'], $pgl['password'])){
		$err = pg_last_error($database->getConn());
		die();
	}
	
	$exts = ['hstore', 'postgis'];
	$dsdb = new Database(DB_HOST, $pgl['dbname'], DB_USER, DB_PASS, DB_PORT, DB_SCMA);
	$dsdb->create_extensions($exts);
	pg_close($dsdb->getConn());
	
	
	$proto = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
	$post_url = $proto.'://'.$_SERVER['HTTP_HOST'].str_replace('setup.php', 'action', $_SERVER['PHP_SELF']);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/sample.cookie');

	// login
	$auth = ['email' => $_POST['super_admin_email'], 'pwd' => $_POST['super_admin_pass'], 'submit' => 'Submit'];
	curl_setopt($ch, CURLOPT_URL, $post_url.'/login.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $auth);
	$response = curl_exec($ch);
	
	curl_setopt($ch, CURLOPT_COOKIEJAR, null);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/sample.cookie');
	
	// create pg link
	curl_setopt($ch, CURLOPT_URL, $post_url.'/pglink.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$pgl['save'] = 1;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pgl);
	$response = curl_exec($ch);
	$response_js = json_decode($response);
	if($response_js == null){
		$fp = fopen('/tmp/response.txt', 'a');
		fwrite($fp, '[create pg link] '.$response."\n");
		fclose($fp);
	}
	
	$pglink_id = $response_js->id;
	
	
	// connect to datasource db
	$dsdb = new Database($pgl['host'], $pgl['dbname'], $pgl['username'], $pgl['password'], $pgl['port'], DB_SCMA);
	if(!$dsdb->is_connected()){
		echo pg_last_error($dsdb->getConn());
		die();
	}
	
	// load simple_bee database
	$sql = file_get_contents('../examples/simple_bee.sql');
	$res = pg_query($dsdb->getConn(), $sql);
	if(!$res){
		echo pg_last_error($dsdb->getConn());
		die();
	}

	// install map
	$zip_file = curl_file_create('../examples/simple_bee.zip','application/zip', 'simple_bee.zip');
	$qgs_file = curl_file_create('../examples/simple_bee_farming.qgs','application/octet-stream', 'simple_bee_farming.qgs');
	$map = ['save' => 1, 'name' => 'Simple Bee Map', 'description' => 'Simple Bee qgis2web Map', 'from_type' => 'archive',
		'qgis_file[]' => $qgs_file, 'archive' => $zip_file, 'accgrps[]' => 1,
		'infobox_content' => '<p>Enter information to be displayed, when your map Info button is clicked.</p>',
		'thismap_css' => '.leaflet-popup-content > table img {width: 300px;}'."\n".
										 '.leaflet-popup-content > img { width: 300px;}'
	];
	
	$map_id = post_example($auth, $map);
	
	// update map
	unset($map['from_type']);
	unset($map['app']);
	unset($map['archive']);
	
	
	$map['save'] = 1;
	$map['id'] = $map_id;	// set ID, so we update
	
	$map['data_type0'] = 'pg';
	$map['pglink_id0'] = $pglink_id;
	$map['pg_schema0'] = 'public';
	$map['pg_tbl0'] 	 = 'fields';
	$map['pg_geom0'] 	 = 'geom';
	$map['pg_cache_val0'] = 0;
	$map['pg_cache_per0'] = 'Off';
	
	$map['data_type1'] = 'pg';
	$map['pglink_id1'] = $pglink_id;
	$map['pg_schema1'] = 'public';
	$map['pg_tbl1'] 	 = 'apiary';
	$map['pg_geom1'] 	 = 'geom';
	$map['pg_cache_val1'] = 0;
	$map['pg_cache_per1'] = 'Off';
	
	$map['qgis_layout'] = 'Bees in Laax';
	
	curl_setopt($ch, CURLOPT_URL, $post_url.'/map.php');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $map);
	$response = curl_exec($ch);

	curl_close($ch);

	return 0;
}

$values = array();

$msg="";
$smtp_keys = ['host' => 'text', 'user' => 'text', 'pass' => 'password', 'port' => 'number'];
$db_keys   = ['host' => 'text', 'user' => 'text', 'pass' => 'password', 'port' => 'number', 'name' => 'text'];

if(file_exists('incl/const.php')){
	require('incl/const.php');
}

if(isset($_POST['submit'])){
	
	$database = new Database($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_port'], 'public');
	if(!$database->is_connected()){
		$msg = pg_last_error($database->getConn());
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

		$file_data .= "const SENTINEL_LAYERS_ENABLED = True;\n";
		
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
		
		if(isset($_POST['load_sample_data'])){
			// sample pg link
			$pgl = ['name' => 'beedatabase', 'host' => DB_HOST, 'port' => DB_PORT, 'dbname' => 'beedatabase', 'svc_name' => 'beedatabase',
				'username' => $def_user['ftp_user'], 'password' => $def_user['pg_password']];

			load_simple_bee($database, $pgl);
			load_r_examples($database, $pgl);
		}
		
		unlink('setup.sql');
		unlink('setup.php');
		App::rrmdir('../examples');

		header('Location: index.php');
	}
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
.frm{width:70% !important; margin:auto; margin-top:50px;}

legend {
    float: left;
    width: 100%;
    padding: 0;
    margin-bottom: .5rem;
    font-size: 15px;
    line-height: inherit;
}

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

      <main role="main" class="container" style="width:30%; margin-top:25px">
         <?php
			if((isset($_GET['step'])) && $_GET['step']==2){
				?>
				<div align="center"><p>&nbsp;</p><img src="../assets/images/login_box.png" style="width:12%">QuartzMap Installer</div>

				<form class="frm" method="post">
				<span class="error"><?=$msg?></span>
				
				<div>
					<fieldset>
						<legend>Administrator</legend>
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
							
							<?php foreach($db_keys as $k => $t){ ?>
								<input type="hidden" class="form-control" placeholder="<?=$k?>" id="db_<?=$k?>" name="db_<?=$k?>" value="<?=$values['db_'.$k]?>">
							<?php } ?>
						</div>
					</fieldset>
				</div>

				<div>
					<fieldset>
						<legend>SMTP</legend>
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
					<fieldset> <br/>
					<legend>Options</legend>
                                               
						<div class="form-group">
							<input type="checkbox" class="form-checkbox" placeholder="signup allowed" name="allow_signup" value="1"/>
							<label for="allow_signup">Allow Sign-Up for Admin accounts</label>
						</div>
						<div class="form-group">
							<input type="checkbox" class="form-checkbox" placeholder="sample data" name="load_sample_data" value="1"/>
							<label for="load_sample_data">Load Sample Data (Recommended)</label>
						</div>
					</fieldset>
				</div>
				
				<div align="right">
				  <button type="submit" name="submit" class="btn btn-primary">Submit</button>
				</div>
			</form>
<p>&nbsp;</p>
			<?php
		}else{
		?>

	  <div align="center"><p>&nbsp;</p><img src="../assets/images/login_box.png" style="width:12%">QuartzMap Installer</div>

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
