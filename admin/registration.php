<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/user.php');
		require('class/access_groups.php');
		require('class/database.php');
		
    if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
    }
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
    $dbconn = $database->getConn();

		$acc_obj = new access_group_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
    $acc_grp = $acc_obj->getRowsArr();

    if(isset($_POST['submit'])&&!empty($_POST['submit'])){
			
			$usr_obj = new user_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
			$_POST['ftp_user'] = '';
			$_POST['pg_password'] = user_Class::randomPassword();
			if(empty($_POST['groups'])){
				$_POST['groups'] = array(1);	// give new admin Default group for now
			}

			$newId = $usr_obj->create($_POST);
			if($newId > 0){
				
				if($_POST['accesslevel'] == 'Admin'){
					
					$myuser_result = $usr_obj->getById($newId);
					$myuser = pg_fetch_assoc($myuser_result);
					pg_free_result($myuser_result);
					
					$email_user = explode('@', $_POST['email'])[0];
					$_POST['ftp_user'] = $email_user.$newId;
					
					user_Class::create_ftp_user($_POST['ftp_user'], $_POST['email'], $myuser['password']);
					$database->create_user($_POST['ftp_user'], $_POST['pg_password']);
					
					// create def access group for new admin
					$def_grp = array('name' => $_POST['ftp_user'], 'userids' => array($newId));
					$acc_obj = new access_group_Class($dbconn, $newId);
					$grp_id = $acc_obj->create($def_grp);
					
					if($grp_id > 0){
						$_POST['id'] = $newId;
						$_POST['groups'] = array($grp_id);
						$usr_obj->update($_POST);
					}
				}
				
				header("Location: users.php");
			}else{
				echo "Something Went Wrong";
			}
    }

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	
	<script type="text/javascript">
		$(document).ready(function() {
			
			$(document).on("change", "#accesslevel", function() {
				var obj = $(this);
				const acc_level = obj.find('option:selected').text();
				
				if(acc_level == 'User'){
					$('#acc_grp_div').show(); $('#acc_grp_div').attr('required', true); 
				}else{
					$('#acc_grp_div').hide(); $('#acc_grp_div').attr('required', false); 
				}
			});
			
		});
	</script>
</head>

<body>
    
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php define('MENU_SEL', 'registration.php');
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
        
        <div class="page-wrapper">
           
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Register New User</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                           




                        </div>
                    </div>
                </div>
            </div>
           
            <div class="container-fluid">

				<table class="table table-bordered">


					<tbody>

<form method="post">

    <div class="form-group">
      <label for="name">Name:</label>
      <input type="text" class="form-control" id="name" placeholder="Enter name" name="name" required>
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" class="form-control" id="email" placeholder="Enter email" name="email" required>
    </div>

    <div class="form-group">
      <label for="accesslevel">Access Level:</label>
      <select name="accesslevel" id="accesslevel">
				<option value="User">User</option>
				<?php if($_SESSION[SESS_USR_KEY]->id == SUPER_ADMIN_ID) { // only super admin can create admins ?>
				<option value="Admin">Admin</option>
				<?php } ?>
			</select>
    </div>


		<div class="form-group" id="acc_grp_div">
			<fieldset>
			<legend>Access Groups:</legend>
			<?php
				foreach($acc_grp as $group_id => $name){ ?>
				<p>
					<input type="checkbox" name="groups[]" id="group_<?=$group_id?>" value="<?=$group_id?>"/>
					<label for="group_<?=$group_id?>" class="form-label"><?=$name?></label>
				</p>
			<?php } ?>
			</fieldset>
    </div>

    <div class="form-group">
      <label for="pwd">Password:</label>
      <input type="password" class="form-control" id="password" placeholder="Enter password" name="password">
    </div>

    <input type="submit" name="submit" class="btn btn-primary" value="Submit">
  </form>



					</tbody>
				</table>







               
                <div class="row">


                    <div class="col-6">
						<p>&nbsp;</p>
						<div id = "repThumbnail" class = "alert alert-danger">
   <a href = "#" class = "close" data-dismiss = "alert">&times;</a>
   <strong>Note:</strong> Be sure to set the Access Level for the user.
</div>



<script type = "text/javascript">
   $(function(){
      $(".close").click(function(){
         $("#repThumbnail").alert();
      });
   });
</script>
</div>
                </div>
               
            </div>
           
            <footer class="footer text-center">

            </footer>
            
        </div>
       
    </div>
   
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
