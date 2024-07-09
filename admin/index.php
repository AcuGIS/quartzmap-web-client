<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
		
		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
				header('Location: ../login.php');
				exit;
		}
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
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

                        <h1 class="mb-0 fw-bold">Dashboard</h1>
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
            
            
            


<div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="maps.php"
                class="card bg-info text-white w-100 card-hover"
              >
<div class="card-header pt-5">
        <!--begin::Title-->
        <div class="card-title d-flex flex-column">   
            <!--begin::Amount-->
            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-map-fill" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.598-.49L10.5.99 5.598.01a.5.5 0 0 0-.196 0l-5 1A.5.5 0 0 0 0 1.5v14a.5.5 0 0 0 .598.49l4.902-.98 4.902.98a.5.5 0 0 0 .196 0l5-1A.5.5 0 0 0 16 14.5zM5 14.09V1.11l.5-.1.5.1v12.98l-.402-.08a.5.5 0 0 0-.196 0zm5 .8V1.91l.402.08a.5.5 0 0 0 .196 0L11 1.91v12.98l-.5.1z"/>
</svg>
 Maps</span>
            <!--end::Amount-->

            <!--begin::Subtitle-->
            <span class="text-white opacity-75 pt-1 fw-semibold fs-6"></span>             
            <!--end::Subtitle--> 
        </div>
        <!--end::Title-->         
    </div>
                <div class="card-body" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);">
                  <div class="d-flex align-items-center">
                    <span class="ri-apple-fill display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      Maps
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Add and Edit Maps
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="links.php"
                class="card bg-success text-white w-100 card-hover"
              >
<div class="card-header pt-5">
        <!--begin::Title-->
        <div class="card-title d-flex flex-column">   
            <!--begin::Amount-->
            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><i class="mdi mdi-database"></i>
 Data Sources</span>
            <!--end::Amount-->

            <!--begin::Subtitle-->
            <span class="text-white opacity-75 pt-1 fw-semibold fs-6"></span>             
            <!--end::Subtitle--> 
        </div>
        <!--end::Title-->         
    </div>
                <div class="card-body" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);">
                  <div class="d-flex align-items-center">
                    <span class="ri-folders-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">Connections</h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Add and Edit Connections
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="access_groups.php"
                class="card bg-warning text-white w-100 card-hover"
              >

<div class="card-header pt-5">
        <!--begin::Title-->
        <div class="card-title d-flex flex-column">   
            <!--begin::Amount-->
            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
  <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
</svg> User Groups</span>
            <!--end::Amount-->

            <!--begin::Subtitle-->
            <span class="text-white opacity-75 pt-1 fw-semibold fs-6"></span>             
            <!--end::Subtitle--> 
        </div>
        <!--end::Title-->         
    </div>
                <div class="card-body" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);">
                  <div class="d-flex align-items-center">
                    <span class="ri-spam-2-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      User Groups
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Manage User Groups
                    </h6>
                  </div>
                </div>
              </a>

</div>
</div>
</div>










<div class="row">
          <div class="d-flex border-bottom title-part-padding px-0 mb-3 align-items-center">

          </div>
          <div class="row" style="width:65%">
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="permalinks.php"
                class="card bg-purple text-white w-100 card-hover"
              >
                <div class="card-body" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);">
                  <div class="d-flex align-items-center">
                    <span class="ri-apple-fill display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      Secure Share
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Manage Permalinks
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="../index.php"
                class="card bg-warning2 text-white w-100 card-hover"
              >
                <div class="card-body" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);">
                  <div class="d-flex align-items-center">
                    <span class="ri-folders-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">Front End</h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Client View
                    </h6>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-md-4 col-xl-2 d-flex align-items-stretch">
              <a
                href="help.php"
                class="card bg-danger text-white w-100 card-hover"
              >
                <div class="card-body" style="box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23);">
                  <div class="d-flex align-items-center">
                    <span class="ri-spam-2-line display-6"></span>
                    <div class="ms-auto">
                      <i data-feather="arrow-right" class="fill-white"></i>
                    </div>
                  </div>
                  <div class="mt-4">
                    <h4 class="card-title mb-1 text-white">
                      Docs and Help
                    </h4>
                    <h6 class="card-text fw-normal text-white-50">
                      Docs and Help
                    </h6>
                  </div>
                </div>
              </a>
            </div>
























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
