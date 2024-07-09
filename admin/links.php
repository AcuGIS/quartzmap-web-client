<?php
    session_start(['read_and_close' => true]);
		require('incl/const.php');
    require('class/database.php');
    require('class/pglink.php');
		require('class/gslink.php');

		if(!isset($_SESSION[SESS_USR_KEY]) || $_SESSION[SESS_USR_KEY]->accesslevel != 'Admin') {
        header('Location: ../login.php');
        exit;
    }
		
		$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);
		$dbconn = $database->getConn();
		
		if(empty($_GET['tab']) || ($_GET['tab'] == 'pg')){
			$tab = 'pg';	$action = 'pglink';			// default tab is PostGIS
			$conn_obj = new pglink_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
			$rows = $conn_obj->getRows();
			
		}else if($_GET['tab'] == 'gs'){
			$tab = 'gs'; $action = 'gslink';
			$conn_obj = new gslink_Class($dbconn, $_SESSION[SESS_USR_KEY]->id);
			$rows = $conn_obj->getRows();
			
		}else if($_GET['tab'] == 'import'){
			$tab = 'import'; $action = 'import';

		}else{
			die('Error: Invalid tab');
		}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en" >

<head>
	<?php include("incl/meta.php"); ?>
	<link href="dist/css/table.css" rel="stylesheet">
	<style>

.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    border: none!important;
    border-radius: inherit;
    text-decoration: none!important;
}
.bg-warning {
    background-color: #50667f!important;

}


td {
    max-width: 175px !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}



/* Table CSS */
.custom-table thead tr, .custom-table thead th {
    border-top: none;
    border-bottom: none !important;
}

.custom-table.table>thead {
	background-color: transparent !important;
    color: inherit;
    border-style: hidden !important;
}

.custom-table.table thead th {
	color: #000 !important;
	border-color: transparent !important;
}

.custom-table {
  border-collapse: separate !important;
  border-spacing: 0 1em !important;
  /*min-width: 900px;*/ }
  .custom-table thead tr, .custom-table thead th {
    border-top: none;
    border-bottom: none !important; }
  .custom-table tbody th, .custom-table tbody td {
    color: #777;
    font-weight: 400;
    padding-bottom: 20px !important;
    padding-top: 20px !important;
    font-weight: 300;}
    .custom-table tbody th small, .custom-table tbody td small {
      color: #b3b3b3;
      font-weight: 300; }
  .custom-table tbody tr:not(.spacer) {
    border-radius: 7px;
    overflow: hidden;
    -webkit-transition: .3s all ease;
    -o-transition: .3s all ease;
    transition: .3s all ease; }
    .custom-table tbody tr:not(.spacer):hover {
      -webkit-box-shadow: 0 2px 10px -5px rgba(0, 0, 0, 0.1);
      box-shadow: 0 2px 10px -5px rgba(0, 0, 0, 0.1); }
  .custom-table tbody tr th, .custom-table tbody tr td {
    background: #fff;
    border: none; }
    .custom-table tbody tr th:first-child, .custom-table tbody tr td:first-child {
      border-top-left-radius: 7px;
      border-bottom-left-radius: 7px; }
    .custom-table tbody tr th:last-child, .custom-table tbody tr td:last-child {
      border-top-right-radius: 7px;
      border-bottom-right-radius: 7px; }
  .custom-table tbody tr.spacer td {
    padding: 0 !important;
    height: 10px;
    border-radius: 0 !important;
    background: transparent !important; }
</style>



	<script type="text/javascript">
	        
			$(document).ready(function() {
						
						$('[data-toggle="tooltip"]').tooltip();

						// Add row on add button click
						$(document).on("click", ".add", function() {
						    var obj = $(this);
							var empty = false;
							var input = $(this).parents("tr").find('input[type="text"], select');
							input.each(function() {
								if (($(this).attr('name') != 'svc_name') && !$(this).val()) {
									$(this).addClass("error");
									empty = true;
								} else {
									$(this).removeClass("error");
								}
							});

							$(this).parents("tr").find(".error").first().focus();
							if (!empty) {
								var data = {};
								data['save'] = 1;
								data['id'] = $(this).closest('tr').attr('data-id');

								input.each(function() {
								    if($(this).closest('td').attr('data-type') == 'select') {
								        var val = $(this).find('option:selected').text();
								        $(this).parent("td").attr('data-value', $(this).val());
								        $(this).parent("td").html(val);
								    }
								    else {
								        $(this).parent("td").html($(this).val());
								    }

									data[$(this).attr('name')] = $(this).val();
								});

								$.ajax({
                                    type: "POST",
                                    url: 'action/<?=$action?>.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.id) { // means, new record is added
                                            obj.closest('table').find('tr:last-child').attr('data-id', response.id);
                                            //obj.closest('table').find('tr:last-child td:first-child').text(response.id)
                                        }
                                        alert(response.message)
                                    }
                                });

								$(this).parents("tr").find(".add, .edit, .pwd_vis").toggle();
								$(this).closest("td").prev().html('******');
								$(".add-new").removeAttr("disabled");
							}
						});



						// Edit row on edit button click
						$(document).on("click", ".edit", function() {
									var obj = $(this);
									var id = $(this).closest('tr').attr('data-id');
									var data = {'pwd_vis': true, 'id': id}
									var ai = $(this).siblings('.pwd_vis').find('i');
								
    							$(this).parents("tr").find("td:not([data-editable=false])").each(function(k, v) {
										if($(this).closest('table').find('thead tr th').eq(k).attr('data-editable') != 'false') {
											var name = $(this).closest('table').find('thead tr th').eq(k).attr('data-name');
        							$(this).html(' <input type = "text" name="'+ name +'" class = "form-control" value = "' + $(this).text() + '" > ');        									
    								}
									});
									
									
									if(ai.text() == "visibility"){
										// replaces starts with password
										$.ajax({
															 type: "POST",
															 url: 'action/<?=$action?>.php',
															 data: data,
															 dataType:"json",
															 success: function(response){
																	 if(response.success) {
																		obj.closest("td").prev().find('input[name="password"]').val(response.message);
																	}
															 }
													 });
									}
								
									$(this).parents("tr").find(".add, .edit, .pwd_vis").toggle();
									$(".add-new").attr("disabled", "disabled");
								});

							// Delete row on delete button click
							$(document).on("click", ".delete", function() {
							    var obj = $(this);
							    var id = obj.parents("tr").attr('data-id');
							    var data = {'delete': true, 'id': id}
									
									if(confirm('Data source will be deleted ?')){
										
										if('<?=$action?>' == 'pglink'){
											let host = obj.closest("tr").children("td").eq(2).text();
											if((host == 'localhost') && confirm('Delete local database too ?')){
												data['drop'] = true;
											}
										}
										
							    	$.ajax({
                                    type: "POST",
                                    url: 'action/<?=$action?>.php',
                                    data: data,
                                    dataType:"json",
                                    success: function(response){
                                        if(response.success) { // means, new record is added
                                            obj.parents("tr").remove();
                                        }

                                        $(".add-new").removeAttr("disabled");
                                        alert(response.message);
                                    }
                                });
									}

							});
							
							// Change on password visibility
							$(document).on("click", ".pwd_vis", function() {
							    var obj = $(this);	// <a> with the icon
							    var id = obj.parents("tr").attr('data-id');
							    var data = {'pwd_vis': true, 'id': id}
									
									var ai = obj.find('i');
									
									if(ai.text() == "visibility"){
										$.ajax({
	                             type: "POST",
	                             url: 'action/<?=$action?>.php',
	                             data: data,
	                             dataType:"json",
	                             success: function(response){
	                                 if(response.success) {
																		ai.text("visibility_off");
								 										obj.attr("data-original-title", "Hide Password");
	 																	obj.closest("td").prev().html(response.message);
																	}
	                             }
	                         });
													 
									}else{
										ai.text("visibility");
										obj.attr("data-original-title", "Show Password");
										obj.closest("td").prev().html('******');
									}
							});
							
							// Show PG connection info
							$(document).on("click", ".conn_info", function() {
									var obj = $(this);	// <a> with the icon
									var id = obj.parents("tr").attr('data-id');
									var data = {'conn_info': true, 'id': id}
																	
									$.ajax({
													 type: "POST",
													 url: 'action/<?=$action?>.php',
													 data: data,
													 dataType:"json",
													 success: function(response){
															 if(response.success) {
																//alert(response.message);
																$('.modal-body').html(response.message);
																$('#conn_modal').modal('show');
															}
													 }
										 });
							});
						
						});
		</script>
</head>

<?php
					if($tab == 'pg'){					require('incl/links_pg.php');
		}else if($tab == 'gs'){					require('incl/links_gs.php');
		}else if($tab == 'import'){			require('incl/links_import.php');
		}else{													die('Error: Invalid tab!');
		}
		?>
			
			<div id="conn_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<!-- <button type="button" class="close" data-dismiss="modal">&times;</button>-->
						<h4 class="modal-title">Connection Information</h4>
					</div>
					<div class="modal-body" id="modal-body"><p>Connection string.</p></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary copy">Copy</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		
				<footer class="footer text-center"></footer>
		</div>
</div>

<script src="dist/js/sidebarmenu.js"></script>
<script src="dist/js/custom.js"></script>
</body>

</html>
