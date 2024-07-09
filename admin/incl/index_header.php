<header>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <link  href="../../assets/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="../../assets/dist/js/bootstrap.bundle.min.js"></script>

				
  <div class="navbar navbar-dark bg-dark shadow-sm" style="background-color:#50667f!important">
    <div class="container">
      <a href="#" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
  <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z"/>
  <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
</svg>        <strong> &nbsp;QuartzMap</strong>
      </a>
			<?php 
			if(isset($_SESSION[SESS_USR_KEY])){
						if($_SESSION[SESS_USR_KEY]->accesslevel == 'Admin') { ?>
			<a href="../../admin/index.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Administration</a>
			<a href="javascript:void(0);" target="_self" id="view_features"	data-id="<?=MAP_ID?>"	style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">View Metadata</a>
      <a href="../../logout.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Log Out</a>
			<?php } else { ?>
			<a href="javascript:void(0);" target="_self" id="view_features"	data-id="<?=MAP_ID?>"	style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">View Metadata</a>
			<a href="../../index.php" target="_self" style="text-decoration:none; color: #fff!important; font-size: 1.25rem; font-weight: 300;">Back to Dashboard</a>
			<?php }
			} ?>
    </div>
  </div>
</header>

<style>
.sidebar {
	max-width: 300px;
	background: white;
	max-height: 400px;
	overflow-x: hidden;
	overflow-y: auto;
	display: none;
}
.sidebar .close {
		position: absolute;
		right: 0;
}

#loading {
  position: fixed;
  display: block;
  width: 100%;
  height: 100%;
  top: 30%;
  left: 40%;
  text-align: center;
  opacity: 0.7;
  background-color: #fff;
  z-index: 99;
}

#loading-image {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 100;
}


.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: fit-content;
    pointer-events: auto;
    background-clip: padding-box;
    border-radius: 20px;
    outline: 0;
    color: black;
    padding: 25px!important;
}


.toolbtns > div {
    background-color: white;
    width: 24px;
    height: 24px;
    padding: 6px;
    cursor: pointer;
}
*, ::after, ::before {
    box-sizing: content-box;
}

</style>

<script>
var table_visible = false;
<?php if(HAS_SENTINEL) { ?>
var leftSentinels = [];
var rightSentinels = [];
<?php } ?>

$(document).ready(function() {
	
	$('#loading').hide();
	
	$(document).on("click", ".copy", function() {
		var obj = $("#conn-modal-body");
		var temp = $("<input>");
		$("body").append(temp);
		temp.val(obj.text()).select();
		temp.focus();
		document.execCommand("copy");
		temp.remove();
	});

	$(document).on("click", "#fg-permalink", function() {
			//href = /apps/57/index.php#11/41.8036/-87.6407
			let path = window.location.href.split('/apps/')[1];	// 57/index.php#11/41.8036/-87.6407
			let loc = path.split('#');
			let map_id = loc[0].split('/');
			$.get('../../admin/action/permalink.php?id=' + map_id[0] + '&loc=' + loc[1], function (data){
				const response = $.parseJSON(data);
				if(response.success){
					var url = window.location.origin + '/' + response.url;
					$('#conn-modal-body').html( '<a href="' + url + '" target="_blank" style="text-decoration: none !important;">' + url +'</a>');
					$('#conn_modal').modal('show');
				}
			});
	});
	
	$(document).on("click", "#fg-infobox", function() {
		$('#infobox_modal').modal('show');
	});
	
	$(document).on("click", "#dt-link", function() {
		const d = (table_visible) ? 1 : 2;
		
		$("#map").height($(window).height() / d);
		map.invalidateSize();
		table_visible = !table_visible;
	});
	
	$(document).on("click", "#view_features", function() {
		var obj = $(this);
		var data = {'features': true, 'id': obj.attr('data-id'), 'from_map': true}
		$.ajax({
				type: "POST",
				url: '../../admin/action/features.php<?=$permalink?>',
				data: data,
				dataType:"json",
				success: function(response){
					if(response.success){
						$('#qgis-modal-body').html(response.html);
						$('#qgis_modal').modal('show');
					}
				}
		});
	});
	
});
</script>

<?php if(SHOW_DATATABLES) { ?>
	<script>
	
	var saved_lg_name = null;
	var saved_layer_id = null;
	
	const on_focus_style = {
				color: "#efefef",
				fillColor: "#efefef",
				opacity: 1.0,
				fillOpacity: 1.0,
				weight: 1
			};

function unfocusLayer(){
	if(saved_lg_name != null){
		var lg_name = saved_lg_name;
		var l = window[lg_name].getLayer(saved_layer_id);
		
		var lg_style = {
			color: window[lg_name].options.color,
			fillColor: window[lg_name].options.fillColor,
			opacity: window[lg_name].options.opacity,
			fillOpacity: window[lg_name].options.fillOpacity,
			weight: window[lg_name].options.weight
		};
		
		l.setStyle(lg_style);
		
		saved_lg_name = null;
		saved_layer_id = null;
	}
}

function focusLayer(lg_name, layer_id){
	var l = window[lg_name].getLayer(layer_id);
	
	unfocusLayer();
	saved_lg_name	 = lg_name;
	saved_layer_id = layer_id;
	
	if(l.getLatLng){
		map.flyTo(l.getLatLng());	
	}else{
		var bounds = l.getBounds();
		map.fitBounds(bounds);					// Fit the map to the polygon bounds
		map.panTo(bounds.getCenter());	// Or center on the polygon
	}
	l.setStyle(on_focus_style);
}
	</script>
	
	<script src="../../assets/dist/js/tbl2CSV.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<?php }

	if(!empty(QGIS_LAYOUT)) { ?>
	<link rel="stylesheet" href="../../assets/dist/locationfilter/locationfilter.css">
	<?php } ?>

<div id="loading">
	<img id="loading-image" src="../../assets/images/ajax-loader.gif" alt="Loading..." />
</div>
	
	<link rel="stylesheet" href="../../assets/dist/css/maps.css?<?=filemtime('../../assets/dist/css/maps.css')?>">
	<link rel="stylesheet" href="thismap.css?<?=filemtime('thismap.css')?>">

<div id="qgis_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<p>QGIS Metadata</p>
			</div>
			
			<div class="modal-body" id="qgis-modal-body"><p>QGIS Metadata</p></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
