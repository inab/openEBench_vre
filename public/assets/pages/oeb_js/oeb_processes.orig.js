var schema;
var urlJSON = "applib/oeb_processesAPI.php";

//function to change the status of the process. 
function changeStatus(statusValue, processId) {
	var url = "applib/oeb_processesAPI.php?action=updateStatus&process=" + processId + "&status=" + statusValue;/*  */
	
	//if some of the parameters are null check it
	if (statusValue == "" || processId == "" || statusValue == null || processId == null) {
		$("#myError").removeClass("alert alert-info");
		$("#myError").addClass("alert alert-danger");
		$("#myError").text("Some param is null.");
		$("#myError").show();
	//ajax petition
	} else {
		$.ajax({
			type: 'POST',
			url: url,
			data: url
		}).done(function(data) {
			//no errors
			if(data["code"]=="200"){
				reload();
				$("#myError").removeClass("alert alert-danger");
				$("#myError").addClass("alert alert-info");
				$("#myError").text("Status changed successfully.");
				$("#myError").show();
			//errors
			} else {
				$("#myError").removeClass("alert alert-info");
				$("#myError").addClass("alert alert-danger");
				$("#myError").text(data["message"]);
				$("#myError").show();
			}
		//more errors
		}). fail(function() {
			$("#myError").removeClass("alert alert-info");
			$("#myError").addClass("alert alert-danger");
			$("#myError").text("The status cannot be updated.");
			$("#myError").show();
		});
	}
}

//the function of delete a process use ajax. The id is the id of the process
function deleteProcess(id) {
	if (confirm("Do you want to remove the process?")) {
		$.ajax({
			type: 'POST',
			url: urlJSON,
			data: {'action': 'deleteProcess', 'id': id}
		}).done(function(data) {
			//no errors
			if(data["code"]=="200"){
				reload();
				$("#myError").removeClass("alert alert-danger");
				$("#myError").addClass("alert alert-info");
				$("#myError").text("Process removed successfully.");
				$("#myError").show();
			//errors
			} else {
				$("#myError").removeClass("alert alert-info");
				$("#myError").addClass("alert alert-danger");
				$("#myError").text(data["message"]);
				$("#myError").show();
			}
		//more errors
		}).fail(function(data) {
			$("#myError").removeClass("alert alert-info");
			$("#myError").addClass("alert alert-danger");
			$("#myError").text(data["responseJSON"]["message"]);
			$("#myError").show();
		});
	}
}

function editProcess(id) {
	location.href = "oeb_management/oeb_process/oeb_newBlock.php?action=editProcess&id=" + id;
}

$(document).ready(function() {
	$("#myError").hide();
	
	var urlJSON = "applib/oeb_processesAPI.php";

	//the id has to be current in the petition. If not, returns information about the owner with the id given
	$('#validationTable').DataTable( {
		"ajax": {
			url: 'applib/oeb_processesAPI.php?action=getProcesses',
			dataSrc: ''
		},
		autoWidth: false,
		"columns" : [
			{ "data" : "data.title" },
			{ "data" : "publication_status" },
			{ "data" : "data.owner.author" },
			{ "data" : null }
		],
		"columnDefs": [
			//targets are the number of corresponding columns
			{ "title": "Title", "targets": 0 },
			{ "title": "Publication status", "targets": 1 },
			{ "title": "Owner", "targets": 2 },
			{ "title": "Actions", "targets": 3, "defaultContent": '' },
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
			//COLUMB ACTION => DELETE PROCESS 
			{render: function(data, type, row) {
				return '<div class="btn-group"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
				'<ul class="dropdown-menu pull-right" role="menu">' +
					'<li>' +
						'<a id="'+row._id+'" onclick="deleteProcess(id);"><i class="fa fa-trash"></i> Delete process</a>' +
					'</li>' +
					'<li>' +
					'<a id="'+row._id+'" onclick="editProcess(id);"><i class="fa fa-edit"></i> Modify process</a>' +
					'</li>' +
				'</ul></div>'
			}, "targets": 3}
		]
	});
	
	//button reload
	$("#processReload").click(function() {
		reload();
	});

	$("#newBlock").click(function() {
		location.href = "oeb_management/oeb_process/oeb_newBlock.php?action=createProcess";
	});

	//fake things about metrics and consolidation
	fakeThings();

});

//function that reload the validation table without relaod all the webpage
function reload() {
	$("#myError").hide();
	$.getJSON('applib/oeb_processesAPI.php?action=getProcesses', function() {
		var oTblReport;

		if ($.fn.dataTable.isDataTable('#validationTable')) {
			oTblReport = $('#validationTable').DataTable();
			oTblReport.ajax.reload();
		}

	});
}

//metrics and consolidation tables are empty and I need construct the datatable. This function is for this parts.
function fakeThings() {
	$('#metricTable').DataTable( {
		autoWidth: false,
		"columns" : [
			{ "data" : "" },
			{ "data" : "" },
			{ "data" : "" },
			{ "data" : "" },
			{ "data" : ""}
		],
		"columnDefs": [
			//targets are the number of corresponding columns
			{ "title": "owner", "targets": 0 },
			{ "title": "publication status", "targets": 1 },
			{ "title": "title", "targets": 2 },
			{ "title": "request date", "targets": 3 },
			{ "title": "request status", "targets": 4 }
		]
	});

	$('#consolidationTable').DataTable( {
		autoWidth: false,
		"columns" : [
			{ "data" : "" },
			{ "data" : "" },
			{ "data" : "" },
			{ "data" : "" },
			{ "data" : ""}
		],
		"columnDefs": [
			//targets are the number of corresponding columns
			{ "title": "owner", "targets": 0 },
			{ "title": "publication status", "targets": 1 },
			{ "title": "title", "targets": 2 },
			{ "title": "request date", "targets": 3 },
			{ "title": "request status", "targets": 4 }
		]
	});
}

