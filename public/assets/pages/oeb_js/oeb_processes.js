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
				reload(data["message"]);
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
function deleteProcess(id, typeProcess) {
	if (confirm("Do you want to remove the process?")) {
		$.ajax({
			type: 'POST',
			url: urlJSON,
			data: {'action': 'deleteProcess', 'id': id}
		}).done(function(data) {
			//no errors
			if(data["code"]=="200"){
				reload(typeProcess);
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

function editProcess(id, typeProcess) {
	location.href = "oeb_management/oeb_process/oeb_newProcess.php?action=editProcess&id=" + id + "&typeProcess="+typeProcess;
}

function currentUser() {
	return $.ajax({
		type: 'POST',
		url: urlJSON,
		data: {'action': 'getUser', 'id': 'current'}
	});
}

$(document).ready(function() {
	
	$("#myError").hide();

	$.when(currentUser()).done(function(user){
	
		var types = ["validation", "metrics", "consolidation"];
		for(let i = 0; i < types.length; i++) {
			$('#'+types[i]+'Table').DataTable( {
				"ajax": {
					url: 'applib/oeb_processesAPI.php?action=getProcesses&type='+types[i],
					dataSrc: ''
				},
				autoWidth: false,
				"columns" : [
					{ "data" : "data.name" },
					{ "data" : "data.publication_status" },
					{ "data" : "validation_status" },
					{ "data" : "data.owner.author" },
					{ "data" : null }
				],
				"columnDefs": [
					//targets are the number of corresponding columns
					{ "title": 'Name <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="All names of different processes."><i class="icon-question"></i></a>', "targets": 0 },
					{ "title": 'Publication status <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="Publication status allows to share the process with all users depending on if it is private, public,..."><i class="icon-question"></i></a>', "targets": 1 },
					{ "title": 'Status <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="Status of the process in relation to nextflow."><i class="icon-question"></i></a>', "targets": 2 },
					{ "title": 'Owner <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="All owners of different processes."><i class="icon-question"></i></a>', "targets": 3 },
					{ "title": 'Actions <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="Available actions for each process."><i class="icon-question"></i></a>', "targets": 4, "defaultContent": '' },
					{ render: function (data, type, row) {
						//status = 0; coming soon
						//status = 1; public
						//status = 2; private
						//status = 3; testing
						//status = 4; community available
						var menu = '<select onChange="changeStatus(value, name)" name="'+row._id+'"><option value="" disabled selected> status...</option><option value="0">Coming soon</option><option value="1">Public</option><option value="2">Private</option><option value="3">Testing</option><option value="4">Community available</option></select>';
						switch(data) {
							case 0: 
								return menu + " <span value='0' class='label label-warning'><b>Coming soon</b></span>"; 
								break;
							case 1: 
								return menu + " <span value='1' class='label label-primary'><b>Public</b></span>"; 
								break;
							case 2: 
								return menu + " <span value='2' class='label label-danger'><b>Private</b></span>";
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
						if (user["Type"] == 1) {
							if(row["data"]["owner"]["user"] == user["id"]) {
								return '<div class="btn-group"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
								'<ul class="dropdown-menu pull-right" role="menu">' +
									'<li>' +
										'<a id="'+row._id+'" name="'+row.data.type+'" onclick="deleteProcess(id, name);"><i class="fa fa-trash"></i> Delete process</a>' +
									'</li>' +
									'<li>' +
									'<a id="'+row._id+'" name="'+row.data.type+'" onclick="editProcess(id, name);"><i class="fa fa-edit"></i> Modify process</a>' +
									'</li>' +
								'</ul></div>';
							} else {
								return '<div class="btn-group"></div>';
							}
						} else if (user["Type"] == 0) {
							return '<div class="btn-group"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
								'<ul class="dropdown-menu pull-right" role="menu">' +
									'<li>' +
										'<a id="'+row._id+'" name="'+row.data.type+'" onclick="deleteProcess(id, name);"><i class="fa fa-trash"></i> Delete process</a>' +
									'</li>' +
									'<li>' +
									'<a id="'+row._id+'" name="'+row.data.type+'" onclick="editProcess(id, name);"><i class="fa fa-edit"></i> Modify process</a>' +
									'</li>' +
								'</ul></div>';
						} else {
							return '<div class="btn-group"></div>';
						}
					}, "targets": 4},
					{ render: function(data, type, row) {
						//FOR ADMINS
						//Submitted => the tool has been submitted by the community manager and the administrator has to accepted it
						//Registered => the administrator has admit the data and the VRE tool is automatically generated
						//Rejected => the administrator does not admit the data and the VRE tool is not created
						switch(data) {
							case "under_validation":
								return '<div class="note note-success" style="background-color:rgba(109, 91, 142,0.7);border-color:rgb(109, 91,142)"><p class="font-white"><b>UNDER VALIDATION</b>:<br> Waiting for integration test results.</p></div>';
								break;
							case "registered": 
								return '<div><div class="note bg-green-jungle"><p class="font-white"><b>ACCEPTED</b>:<br/>Process validated.</p></div>';
								break;
							case "rejected":
								return '<div><div class="note note-danger"><p class="font-red"><b>REJECTED</b>:<br/>Process not accepted.</p></div>';
								break;
							default: 
								return "";
						} 
		
					}, "targets": 2},
				]
			});
		}

		$(".newProcess").click(function() {
			var typeProcess = $(this).attr('id');
			location.href = "oeb_management/oeb_process/oeb_newProcess.php?action=createProcess&typeProcess="+typeProcess;
		});

	});

});

//function that reload the validation table without relaod all the webpage
function reload(type) {
	$("#myError").hide();
	$.getJSON('applib/oeb_processesAPI.php?action=getProcesses&type='+type, function() {
		var oTblReport;

		if ($.fn.dataTable.isDataTable('#'+type+'Table')) {
			oTblReport = $('#'+type+'Table').DataTable();
			oTblReport.ajax.reload();
		}

	});
}

