<?php
    session_start(['read_and_close' => true]);
		require('admin/incl/const.php');
		require('admin/class/database.php');
		require('admin/class/access_groups.php');

    if(!isset($_SESSION[SESS_USR_KEY])) {
        header('Location: login.php');
        exit;
    }

		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		
		$acc_obj	= new access_group_Class($database->getConn(), $_SESSION[SESS_USR_KEY]->id);
		$usr_grps = ($_SESSION[SESS_USR_KEY]->id == SUPER_ADMIN_ID) ? $acc_obj->getRowsArr()
																													: $acc_obj->getByUserId($_SESSION[SESS_USR_KEY]->id);
		
		$rows = array();
		if(count($usr_grps)){
			$usr_grps_keys = array_keys($usr_grps);
			$usr_map_grps = $acc_obj->getGroupMapGroups($usr_grps_keys);
			
			if(count($usr_map_grps)){
				$usr_map_grps_ids = implode(',', array_keys($usr_map_grps));
				$rows = $database->getAll('map', "id IN (".$usr_map_grps_ids.")",	'id');
			}
		}
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>QuartzMap</title>

    <link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }

      .card {
          box-shadow: 0 0.15rem 0.55rem rgba(0, 0, 0, 0.1);
          transition: box-shadow 0.3s ease-in-out;
        }

        .card:hover {
          box-shadow: 0 0.35rem 0.85rem rgba(0, 0, 0, 0.3);
        }
        .col {
            padding-right: calc(var(--bs-gutter-x) * .75);
            padding-left: calc(var(--bs-gutter-x) * .75);
        }

.navbar {
    position: relative;
    min-height: 50px;
    margin-bottom: 0px!important;
    border: 1px solid transparent;
}

.dataTables {

padding: 35px!important;

}


    </style>
<script type="text/javascript">

			$(document).ready(function() {
				$(document).on("click", ".features", function() {
					var obj = $(this);
					var data = {'features': true, 'id': obj.attr("data-id")}
					$.ajax({
							type: "POST",
							url: 'admin/action/features.php',
							data: data,
							dataType:"json",
							success: function(response){
								if(response.success){
									$('.modal-body').html(response.html);
									$('#qgis_modal').modal('show');
								}
							}
					});
				});
		});	
</script>

  </head>
  <body>

<header>

  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:#50667f!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;QuartzMaps</strong>
      </a>

<?php
if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') { ?>
  <a href="admin/index.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>
<?php }

if(($_SESSION[SESS_USR_KEY]->id == SUPER_ADMIN_ID) && is_dir('../master')){ ?>
  <a href="../master/index.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Master</a>
<?php }
?>


      <a href="logout.php" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Log Out</a>


    </div>
  </div>
</header>


<main style="background-color:#edf0f2">
	
	<section class="py-5 text-left container" style="padding-bottom: 0rem!important;">
    <div class="row py-lg-5">
      <div class="col-lg-6 col-md-8 mx-auto" style="margin-left: 5px!important;">
        <h1 class="fw-light"><?php if(isset($_SESSION[SESS_USR_KEY])) { echo($_SESSION[SESS_USR_KEY]->name); } ?> Maps

  </h1>
        <p class="lead text-muted">Maps</p>
      </div>
    </div>
  </section>
  <div class="album py-5 bg-light">
    <div class="container">


        <div class="row row-cols-1 row-cols-md-4 g-4">
					
				  <?php foreach($rows as $row) {		
						$image = file_exists("assets/maps/{$row['id']}.png") ? "assets/maps/{$row['id']}.png" : "assets/maps/default.png"; ?>
						<div class="col">
								<a href="apps/<?=$row['id']?>/index.php" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">
									<div class="card">
										<div class="card-body">
											<h5 class="card-title" style="font-size: 15px; font-weight: 800;"><?=$row['name']?></h5>
										</div>
										<div class="px-3">
											 <div style="height: 150px; width: 100%; background: url('<?=$image?>') no-repeat; background-size: cover; background-position: center center;"></div>
										</div>
										<?PHP if($row['description']) { ?>
											<div class="card-body">
												<p class="card-text" style="color: #6c757d!important; font-size: 15px; font-weight: 600;"> <?=$row['description']?> </p>
											</div>
									<?PHP } ?>
									
									<?php if(is_file(APPS_DIR.'/'.$row['id'].'/proxy_qgis.php')) { ?>
										<!--<a class="features" title="View Metadata"	 data-toggle="tooltip" data-id="<?=$row['id']?>">
											<button class="material-icons">View Metadata</button>
										</a>-->
									<?php } ?>
									</div>
							</a>
						</div>
				<?php } ?>
			</div>
		</div>
	</div>
</main>

<div id="qgis_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content" style="width:fit-content!important;">
			<div class="modal-header">
				<p>QGIS Metadata</p>
			</div>
			
			<div class="modal-body" id="modal-body"><p>QGIS Metadata</p></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

	<footer class="text-muted py-5">
	  <div class="container">
	    <p class="float-end mb-1">
	<a href="#" style="text-decoration:none; color: #6c757d!important; font-size: 1.25rem; font-weight: 300;">Back to top</a>    </p>
	  </div>
	</footer>
  
	<script src="assets/dist/js/bootstrap.bundle.min.js"></script>

  </body>
</html>
