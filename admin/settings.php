<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		require('class/user.php');
		require('class/database.php');
		
    if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
    }
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link rel="stylesheet" href="../assets/dist/codemirror/codemirror.css">
	<link rel="stylesheet" href="../assets/dist/codemirror/show-hint.css">
	<script src="../assets/dist/codemirror/codemirror.js"></script>
	<script src="../assets/dist/codemirror/show-hint.js"></script>
	<script src="../assets/dist/codemirror/css-hint.js"></script>
	<script src="../assets/dist/codemirror/css.js"></script>

	<style>
.error{color:red;}
	</style>
	<link href="dist/css/table.css" rel="stylesheet">
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
                        <h1 class="mb-0 fw-bold">Image and CSS Settings</h1>
                    </div>
                    <div class="col-6">
                        <div class="text-end upgrade-btn">
                        </div>
                    </div>
                </div>
            </div>
						
<div class="container-fluid">

	<form method="post" action="action/settings.php" enctype="multipart/form-data">
		
		<?php if(isset($_GET['err'])) { ?>
			<span class="error"><?=$_GET['err']?></span>
		<?php } ?>
    
		<div class="form-group">
      <label for="name">Login Box Image:</label>
			<img src="../assets/images/login_box.png?<?=filemtime('../assets/images/login_box.png');?>" class="img-fluid" alt="" data-aos="zoom-out" data-aos-delay="100" style="width: 50%">
      <input type="file" class="form-control" id="login_box_img" name="login_box_img" placeholder="Login Box Image" accept=".png,.jpeg">
    </div>
		
		<div class="form-group">
      <label for="name">Login Page Image:</label>
			<img src="../assets/images/login_page.png?<?=filemtime('../assets/images/login_page.png');?>" class="img-fluid" alt="" data-aos="zoom-out" data-aos-delay="100" style="width: 50%">
      <input type="file" class="form-control" id="login_page_img" name="login_page_img" placeholder="Login Page Image" accept=".png,.jpeg">
    </div>
		
		<div class="form-group">
			<label for="custom_css" class="form-label">Custom CSS</label>
			<textarea name="custom_css" id="custom_css" rows="10" cols="80"><?php readfile('../assets/dist/css/custom.css'); ?></textarea>
		</div>
		
		<div class="form-group">
			<label for="maps_css" class="form-label">Maps CSS</label>
			<textarea name="maps_css" id="maps_css" rows="10" cols="80"><?php readfile('../assets/dist/css/maps.css'); ?></textarea>
		</div>

    <input type="submit" name="submit" class="btn btn-primary" value="Submit">
  </form>
</div>

    <footer class="footer text-center"></footer>
  </div>
</div>
		
		<script>	
			var editor1 = CodeMirror.fromTextArea(document.getElementById("custom_css"), {
				extraKeys: {"Ctrl-Space": "autocomplete"}
			});
			
			var editor2 = CodeMirror.fromTextArea(document.getElementById("maps_css"), {
				extraKeys: {"Ctrl-Space": "autocomplete"}
			});
		</script>
		
    <script src="dist/js/sidebarmenu.js"></script>
    <script src="dist/js/custom.js"></script>
</body>

</html>
