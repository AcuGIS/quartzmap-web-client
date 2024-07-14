<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
			header('Location: ../login.php');
			exit;
		}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">

<style type="text/css">
a {
	text-decoration:none!important;
}
.card:hover {
  box-shadow: 0 4px 10px rgba(0,0,0,0.16), 0 4px 10px rgba(0,0,0,0.23);
}
</style>
</head>

<body>
   
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'index.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>
       
        <div class="page-wrapper">
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h1 class="mb-0 fw-bold">Docs and Guides</h1>
                    </div>
                    <div class="col-6">

                    </div>
                </div>
            </div>
           
            <div class="container-fluid">

				<div class="row">
          <div class="d-flex border-bottom title-part-padding px-0 mb-3 align-items-center">

          </div>
          <div class="row" style="width:65%">
            
 
<h3>Docs</h3>           

<h3><a href="https://www.acugis.com/quartzmap/docs/" target="_blank">QuartzMap Documentation</a></h3>

<p>&nbsp;</p>
            

<p>&nbsp;</p>
<h3>Quick Start Videos</h3><p>&nbsp;</p>

<h3><a href="https://youtu.be/M5_K22Hgqsk" target="_blank"><img src="assets/images/video.png"> QuartzMap Quick Start</a></h3>

<p>&nbsp;</p>


<h3><a href="https://youtu.be/_AwP-EAJFFA" target="_blank"><img src="assets/images/video.png"> Data Source Quick Start</a></h3>


<p>&nbsp;</p>


<h3><a href="https://youtu.be/Oh_P013VO6U" target="_blank"><img src="assets/images/video.png"> Data Source Quick Start II</a></h3>




<a>


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
