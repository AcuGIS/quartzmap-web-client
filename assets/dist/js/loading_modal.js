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

});