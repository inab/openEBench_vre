function changeStatus(statusValue, processId) {
	var url = "applib/oeb_processesAPI.php?action=updateStatus&process=" + processId + "&status=" + statusValue;/*  */
	
	if (statusValue == "" || processId == "" || statusValue == null || processId == null) {
		$("#myError").removeClass("alert alert-info");
		$("#myError").addClass("alert alert-danger");
		$("#myError").text("Some param is null.");
		$("#myError").show();
	} else {
		$.ajax({
			type: 'POST',
			url: url,
			data: url
		}).done(function(data) {
			if(data["code"]=="200"){
				reload();
				$("#myError").removeClass("alert alert-danger");
				$("#myError").addClass("alert alert-info");
				$("#myError").text("Status changed successfully.");
				$("#myError").show();
			} else {
				$("#myError").removeClass("alert alert-info");
				$("#myError").addClass("alert alert-danger");
				$("#myError").text(data["message"]);
				$("#myError").show();
			}
		}). fail(function() {
			$("#myError").removeClass("alert alert-info");
			$("#myError").addClass("alert alert-danger");
			$("#myError").text("The status cannot be updated.");
			$("#myError").show();
		});
	}
}

$(document).ready(function() {
	$("#myError").hide();
	
	var urlJSON = "applib/oeb_processesAPI.php";

	$.ajax({
		type: 'POST',
		url: urlJSON,
		data: {'action': 'getUser'}
	}).done(function(data) {

		//FOR ADMINS
		if (data[0]["Type"] == 0) {
			$('#tblReportResultsDemographics').DataTable( {
				"ajax": {
					url: 'applib/oeb_processesAPI.php?action=list',
					dataSrc: ''
				},
				autoWidth: false,
				"columns" : [
					{ "data" : "_id" },
					{ "data" : "publication_status" },
					{ "data" : "data.title" },
					{ "data" : "request_date" },
					{ "data" : "request_status"}
				],
				"columnDefs": [
					{ "title": "id", "targets": 0 },
					{ "title": "publication status", "targets": 1 },
					{ "title": "title", "targets": 2 },
					{ "title": "request date", "targets": 3 },
					{ "title": "request status", "targets": 4 },
					{ render: function (data, type, row) {
						//status = 0; private
						//status = 1; public
						//status = 2; coming soon
						//status = 3; testing
						//status = 4; community available
						var menu = '<select id="selectChange" onChange="changeStatus(value, name)" name="'+row._id+'"><option value="" disabled selected> status...</option><option value="0">Private</option><option value="1">Public</option><option value="2">Coming soon</option><option value="3">Testing</option><option value="4">Community available</option></select>';
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
							case 3: 
								return menu + " <span value='3' class='label label-default'><b>Testing</b></span>";
								break;
							case 4: 
								return menu + " <span value='4' class='label label-success'><b>Community available</b></span>";
								break;
							default: 
								return menu;
						}
					}, "targets": 1},
					{ render: function(data, type, row) {
						switch(data) {
							case "submitted":
								return '<div style="padding-top:30px;"><div class="note note-success" style="background-color:rgba(109, 91, 142,0.7);border-color:rgb(109, 91,142)"><p class="font-white"><b>SUBMITTED</b>:<br> Waiting for VRE team response.</p></div>' +
								'<div style="margin: 10px 0;"></div>'+
								'<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline green dropdown-toggle" data-toggle="dropdown">Status<i class="fa fa-angle-down"></i></button>' +
								'<ul class="dropdown-menu pull-right" role="menu">' +
									'<li><a href="aa">Submitted</a></li>' +
									'<li><a href="a1">Registered</a></li>' +
									'<li><a href="a4">Rejected</a></li>' +
								+ '</ul></div></div>';
								break;
							case "registered": 
								return '<div><div class="note bg-green-jungle"><p class="font-white"><b>ACCEPTED</b>:<br/>Tool successfully registed!</p></div>' +
								'<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline green dropdown-toggle" data-toggle="dropdown">Status <i class="fa fa-angle-down"></i></button>' +
								'<ul class="dropdown-menu pull-right" role="menu">' +
									'<li><a href="aa">Submitted</a></li>' +
									'<li><a href="a1">Registered</a></li>' +
									'<li><a href="a4">Rejected</a></li>' +
								+ '</ul></div></div>';
								break;
							case "rejected":
								return '<div><div class="note note-danger"><p class="font-red"><b>REJECTED</b>:<br/>Code not accepted</p></div>' +
								'<div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline green dropdown-toggle" data-toggle="dropdown">Status <i class="fa fa-angle-down"></i></button>' +
								'<ul class="dropdown-menu pull-right" role="menu">' +
									'<li><a href="aa">Submitted</a></li>' +
									'<li><a href="a1">Registered</a></li>' +
									'<li><a href="a4">Rejected</a></li>' +
								+ '</ul></div></div>';
								break;
							default: 
								return "";
						}
					}, "targets": 4}
				]
			});

		//FOR COMMUNITY MANAGERS
		} else if (data[0]["Type"] == 1) {
			$('#tblReportResultsDemographics').DataTable( {
				"ajax": {
					url: 'applib/oeb_processesAPI.php?action=list',
					dataSrc: ''
				},
				autoWidth: false,
				"columns" : [
					{ "data" : "_id" },
					{ "data" : "publication_status" },
					{ "data" : "data.title" },
					{ "data" : "request_date" },
					{ "data" : "request_status"}
				],
				"columnDefs": [
					{ "title": "id", "targets": 0 },
					{ "title": "publication status", "targets": 1 },
					{ "title": "title", "targets": 2 },
					{ "title": "request date", "targets": 3 },
					{ "title": "request status", "targets": 4 },
					{ render: function (data, type, row) {
						//status = 0; private
						//status = 1; public
						//status = 2; coming soon
						//status = 3; testing
						//status = 4; community available
						console.log(data);
						var menu = '<select id="selectChange" onChange="changeStatus(value, name)" name="'+row._id+'"><option value="" disabled selected> status...</option><option value="0">Private</option><option value="1">Public</option><option value="2">Coming soon</option><option value="3">Testing</option><option value="4">Community available</option></select>';
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
							case 3: 
								return menu + " <span value='3' class='label label-default'><b>Testing</b></span>";
								break;
							case 4: 
								return menu + " <span value='4' class='label label-success'><b>Community available</b></span>";
								break;
							default: 
								return menu;
						}
					}, "targets": 1},
					{ render: function(data, type, row) {
						switch(data) {
							case "submitted":
								return '<div class="note note-success" style="background-color:rgba(109, 91, 142,0.7);border-color:rgb(109, 91,142)"><p class="font-white"><b>SUBMITTED</b>:<br/> Waiting for VRE team response.</p></div>'
								break;
							case "registered": 
								return '<div class="note bg-green-jungle"><p class="font-white"><b>ACCEPTED</b>:<br/>Tool successfully registed!</p></div>';
								break;
							case "rejected":
								return '<div class="note note-danger"><p class="font-red"><b>REJECTED</b>:<br/>Code not accepted</p></div>';
								break;
						}
					}, "targets": 4}
				]
			});
		}
	});

	$("#processReload").click(function() {
		reload();
	})
});

function reload() {
	$("#myError").hide();
	$.getJSON('applib/oeb_processesAPI.php?action=list', function() {
		var oTblReport;

		if ($.fn.dataTable.isDataTable('#tblReportResultsDemographics')) {
			oTblReport = $('#tblReportResultsDemographics').DataTable();
			oTblReport.ajax.reload();
		}

	});
}

