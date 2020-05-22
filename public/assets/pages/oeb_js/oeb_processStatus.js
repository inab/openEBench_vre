var schema;

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

function registerTool(id) {
	var urlJSON = "applib/oeb_processesAPI.php";
	var submit = document.getElementById("s" + id);
	var reject = document.getElementById("r" + id);
	
	if(confirm("Do you want to create a tool?")) {
		submit.setAttribute("disabled", true);
		reject.setAttribute("disabled", true);
		$.ajax({
			type: 'POST',
			url: urlJSON,
			data: {'action': 'createTool_fromWFs', 'id': id}
		}).done(function(data) {
			reload();
			console.log(data);
		});
	};
};

function rejectTool(id) {
	var urlJSON = "applib/oeb_processesAPI.php";
	var submit = document.getElementById("s" + id);
	var reject = document.getElementById("r" + id);

	if(confirm("Do you want to reject the workflow?")) {
		submit.setAttribute("disabled", true);
		reject.setAttribute("disabled", true);
		$.ajax({
			type: 'POST',
			url: urlJSON,
			data: {'action': 'reject_workflow', 'id': id}
		}).done(function(data) {
			reload();
			console.log(data);
		});
	};
}

$(document).ready(function() {
	$("#myError").hide();
	
	var urlJSON = "applib/oeb_processesAPI.php";

	//the id has to be current in the petition. If not, returns information about the owner with the id given
	$.ajax({
		type: 'POST',
		url: urlJSON,
		data: {'action': 'getUser', 'id': "current"}
	}).done(function(data) {
		var currentUser = data[0];
		$('#tblReportResultsDemographics').DataTable( {
			"ajax": {
				url: 'applib/oeb_processesAPI.php?action=list',
				dataSrc: ''
			},
			autoWidth: false,
			"columns" : [
				{ "data" : "data.owner.author" },
				{ "data" : "publication_status" },
				{ "data" : "data.title" },
				{ "data" : "request_date" },
				{ "data" : "request_status"}
			],
			"columnDefs": [
				//targets are the number of corresponding columns
				{ "title": "owner", "targets": 0 },
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
							return menu + " <span value='3' class='label label-info'><b>Testing</b></span>";
							break;
						case 4: 
							return menu + " <span value='4' class='label label-success'><b>Community available</b></span>";
							break;
						default: 
							return menu;
					}
				}, "targets": 1},
				{ render: function(data, type, row) {

					//FOR ADMINS
					//Submitted => the tool has been submitted by the community manager and the administrator has to accepted it
					//Registered => the administrator has admit the data and the VRE tool is automatically generated
					//Rejected => the administrator does not admit the data and the VRE tool is not created
					if (currentUser["Type"] == 0) {
						var buttonSubmit = '<a onclick="registerTool(name)" class="btn btn-block btn-sm green" name="'+row._id+'" id="s'+row._id+'"><i class="fa fa-check" aria-hidden="true"></i> Create VRE tool</a>';
						var buttonReject = '<a onclick="rejectTool(name)" class="btn btn-block btn-danger" name="'+row._id+'" id="r'+row._id+'"><i class="fa fa-ban" aria-hidden="true"></i> Reject workflow</a>';
						switch(data) {
							case "submitted":
								return '<div class="note note-success" style="background-color:rgba(109, 91, 142,0.7);border-color:rgb(109, 91,142)"><p class="font-white"><b>SUBMITTED</b>:<br> Waiting for VRE team response.</p></div>' + buttonSubmit + buttonReject;
								break;
							case "registered": 
								return '<div><div class="note bg-green-jungle"><p class="font-white"><b>ACCEPTED</b>:<br/>Tool successfully registed!</p></div>';
								break;
							case "rejected":
								return '<div><div class="note note-danger"><p class="font-red"><b>REJECTED</b>:<br/>Code not accepted</p></div>';
								break;
							default: 
								return "";
						} 

					//FOR COMMUNITY MANAGERS
					//FOR ADMINS
					//Submitted => the tool has been submitted by the community manager and the administrator has to accepted it
					//Registered => the administrator has admit the data and the VRE tool is automatically generated
					//Rejected => the administrator does not admit the data and the VRE tool is not created
					} else if (currentUser["Type"] == 1) {
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
					}
				}, "targets": 4}
			]
		});
	});
	
	$("#processReload").click(function() {
		reload();
	});
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

