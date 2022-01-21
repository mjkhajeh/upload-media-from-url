(function($) {
	$(document).ready(function(){
		$(".mjupurl_toggle").on('click', function(e) {
			e.preventDefault();
			$(this).next().slideToggle();
		});
		function isValidHttpUrl(str) {
			let url;
			try {
				url = new URL(str);
			} catch (_) {
				return false;  
			}
			return url.protocol === "http:" || url.protocol === "https:";
		}
		function newRow() {
			var index = $("#mjupurl_upload_table tbody tr").length,
				rowIndex = index+1,
				html = $("#mjupurl_template tbody").html().replace(/%row%/g, rowIndex).replace(/%index%/g, index);
			$(html).appendTo("#mjupurl_upload_table tbody");
		}
		$("#mjupurl_bulk_links_input").on('keyup change', function() {
			var links = $(this).val().split( "\n" );
			$(".mjupurl_url,.mjupurl_name").val("");
			links.forEach(function(link, index) {
				if(isValidHttpUrl(link)) {
					var rowIndex = index+1;
					var row = $(`#mjupurl_upload_table tbody tr:nth-child(${rowIndex})`);
					if(!row.length) newRow();
					link = link.split(" : ");
					row.find(".mjupurl_url").val(link[0]);
					if(link[1] !== 'undefined') {
						row.find(".mjupurl_name").val(link[1]);
					}
				}
			});
		});
		$(document).on('change', ".mjupurl_url,.mjupurl_name", function() {
			var links = [];
			$("#mjupurl_upload_table .mjupurl_url").each(function(index) {
				index++;
				var link = $(this).val();
				if(isValidHttpUrl(link)) {
					var name = $(`#mjupurl_upload_table tbody tr:nth-child(${index}) .mjupurl_name`).val();
					if(name) link = `${link} : ${name}`;
					links.push(link);
				}
			});
			$("#mjupurl_bulk_links_input").val(links.join("\n"));
		});
		$(".mjupurl_add_row").on('click', function(e) {
			e.preventDefault();
			newRow();
		});
	});
})(jQuery);