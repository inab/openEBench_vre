function changeStatus(statusValue, processId) {
	
	var url = "https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?process=" + processId + "&status=" + statusValue;/*  */
	
	if (statusValue === "" || processId === "") {
		$("#myError").text("Some param is null");
	} else {
		$.ajax({
			type: 'POST',
			url: url,
			data: url
		});
	}
	if(url["code"]!="200"){
		reload();
		$("#myError").removeClass("alert alert-danger");
		$("#myError").addClass("alert alert-info");
		$("#myError").text("Successfully process uploaded");
		$("#myError").show();
	} else {
		$("#myError").text(url["message"]);
		$("#myError").show();
	} 
}

$(document).ready(function() {
	$("#myError").hide();
	$('#tblReportResultsDemographics').DataTable( {
		"ajax": {
			url: 'https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?list',
			dataSrc: ''
		},
		autoWidth: false,
		"columns" : [
			{ "data" : "_id" },
			{ "data" : "status" },
			{ "data" : "inputs_meta.public_ref_dir.data_type" },
			{ "data" : "inputs_meta.public_ref_dir.file_type" },
			{ "data" : "owner"}
		],
		"columnDefs": [
			{ "title": "_id", "targets": 0 },
			{ "title": "status", "targets": 1 },
			{ "title": "data_type", "targets": 2 },
			{ "title": "fyle_type", "targets": 3 },
			{ "title": "owner", "targets": 4 },
			{ render: function (data, type, row) {
				//status = 0; private
				//status = 1; public
				//status = 2; coming soon
				var menu = '<select id="selectChange" onChange="changeStatus(value, name)" name="'+row._id+'"><option value="" disabled selected> status...</option><option value="0">Private</option><option value="1">Public</option><option value="2">Coming soon</option></select>';
				switch(data) {
					case 0: 
						return menu + " <span value='0' class='label label-danger'><b>Private</b></span>"; 
						break;
					case 1: 
						return menu + " <span value='1' class='label label-primary'><b>Public</b></span>"; 
						break;
					case 2: 
						return menu + " <span value='2' class='label label-warning'><b>Coming soon</b></span>";
						break;
					default: 
						return menu;
				}
			}, "targets": 1}
		]
	});

	$("#processReload").click(function() {
		reload();
	})
});

function reload() {
	$("#myError").hide();
	$.getJSON('https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?list=true', function() {
		var oTblReport;

		if ($.fn.dataTable.isDataTable('#tblReportResultsDemographics')) {
			oTblReport = $('#tblReportResultsDemographics').DataTable();
			oTblReport.ajax.reload();
		}

	});
}

