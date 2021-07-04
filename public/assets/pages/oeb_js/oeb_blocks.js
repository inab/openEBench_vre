var schema;
var urlJSON = "applib/oeb_blocksAPI.php";

//function to change the status of the block. 
function changeStatus(statusValue, blockId) {
	var url = "applib/oeb_blocksAPI.php?action=updateStatus&block=" + blockId + "&status=" + statusValue;/*  */
	
	//if some of the parameters are null check it
	if (statusValue == "" || blockId == "" || statusValue == null || blockId == null) {
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

//the function of delete a block use ajax. The id is the id of the block
function deleteBlock(id, typeBlock) {
	if (confirm("Do you want to remove the block?")) {
		$.ajax({
			type: 'POST',
			url: urlJSON,
			data: {'action': 'deleteBlock', 'id': id, 'type': typeBlock}
		}).done(function(data) {
			//no errors
			if(data["code"]=="200"){
				reload(typeBlock);
				$("#myError").removeClass("alert alert-danger");
				$("#myError").addClass("alert alert-info");
				$("#myError").text("Block removed successfully.");
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

function editBlock(id, typeBlock) {
	// id =>  block id; typeBlock => either "edit" or "submit" 
	location.href = "oeb_management/oeb_block/oeb_newBlock.php?action=editBlock&id=" + id + "&typeBlock="+typeBlock;
}

function acceptBlock(id, typeBlock) {
	if (confirm("Do you want to accept the block?")) {
		$.ajax({
			type: 'POST',
			url: urlJSON,
			data: {'action': 'acceptBlock', 'id': id}
		}).done(function(data) {
			//no errors
			if(data["code"]=="200"){
				reload(typeBlock);
				$("#myError").removeClass("alert alert-danger");
				$("#myError").addClass("alert alert-info");
				$("#myError").text("Block accepted successfully.");
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

function currentUser() {
	return $.ajax({
		type: 'POST',
		url: urlJSON,
		data: {'action': 'getUser', 'id': 'current'}
	});
}

function getCommunity() {
	return $.ajax({
		type: 'POST',
		url: urlJSON,
		data: {'action': 'getCommunity'}
	});
}

$(document).ready(function() {

	var communitiesUser;
	$.when(getCommunity()).done(function(communities){

		$("#myError").hide();

		$.when(currentUser()).done(function(user){
			console.log(communities);
			var types = ["validation", "metrics", "consolidation"];
			for(let i = 0; i < types.length; i++) {
				$('#'+types[i]+'Table').DataTable( {
					"ajax": {
						url: 'applib/oeb_blocksAPI.php?action=getBlocks&type='+types[i],
						dataSrc: ''
					},
					autoWidth: false,
					"columns" : [
						{ "data" : "data.name" },
						{ "data" : "data.description" },
						{ "data" : "data.owner.author" },
						{ "data" : "data.publication_status" },
						{ "data" : null },
						{ "data" : "workflows_in_use" },
						{ "data" : "creation_date" }
					],
					"columnDefs": [
						//targets are the number of corresponding columns
						{ "title": 'Name <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="All names of different blocks."><i class="icon-question"></i></a>', "targets": 0 },
						{ "title": 'Description <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="The description of the block."><i class="icon-question"></i></a>', "targets": 1 },
						{ "title": 'Owner <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="All owners of different blocks."><i class="icon-question"></i></a>', "targets": 2 },
						{ "title": 'Publication status <a href="https://openebench.readthedocs.io/en/dev/how_to/3_manage_events.html#how-to-organize-new-events" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="Publication status allows to share the block with all users depending on if it is private, public,... For more information click «?»"><i class="icon-question"></i></a>', "targets": 3 },
						{ "title": 'Management <a href="https://openebench.readthedocs.io/en/dev/how_to/3_manage_events.html#how-to-organize-new-events" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="Available actions for each block."><i class="icon-question"></i></a>', "targets": 4, "defaultContent": '' },
						{ "title": 'Workflows in use <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="All names of different blocks."><i class="icon-question"></i></a>', "targets": 5 },
						{ "title": 'Creation date <a href="javascript:;" target="_blank" class="tooltips" data-toggle="tooltip" data-trigger="hover" data-placement="top" title="All names of different blocks."><i class="icon-question"></i></a>', "targets": 6 },
						{ render: function (data, type, row) {

							//DATA:
							//status = 0; coming soon
							//status = 1; public
							//status = 2; private
							//status = 3; testing
							//status = 4; community available

							//ROW: all the block

							disabledValue = false;

							//choose the option that has to be selected
							selectComing = "";
							selectCommunity = "";
							selectPrivate = "";
							selectPublic = "";
							selectTesting = "";
							var span;

							if (data == 0) span = '<span value="0" class="label label-warning"><b>Coming soon</b></span>';
							else if (data == 1) span = '<span value="1" class="label label-primary"><b>Public</b></span>';
							else if (data == 2) span = '<span value="2" class="label label-danger"><b>Private</b></span>';
							else if (data == 3) span = '<span value="3" class="label label-info"><b>Testing</b></span>';
							else if (data == 4) span = '<span value="4" class="label label-success"><b>Community available</b></span>';

							if (user["Type"] == 0 || (user["Type"] == 1 && row["data"]["owner"]["user"] == user["id"])) {
								if(row["validation_status"] != "registered") {
									disabledValue = true;
								}
							} else return span;

							if (disabledValue) {
								var openSelect = '<select id="select_'+row._id+'" onChange="changeStatus(value, name)" name="'+row._id+'" disabled style="background-color:#D5D5D5">';
							} else {
								var openSelect = '<select id="select_'+row._id+'" onChange="changeStatus(value, name)" name="'+row._id+'">';
							}

							switch(data) {
								case 1: 
									return openSelect + 
										'<option value="1" selected>Public</option>'+
										'<option value="2">Private</option>'+
										'<option value="3">Testing</option>'+
										'<option value="4">Community available</option></select> ' +
										span;
								case 2: 
									return openSelect + 
										'<option value="1">Public</option>'+
										'<option value="2" selected>Private</option>'+
										'<option value="3">Testing</option>'+
										'<option value="4">Community available</option></select> ' +
										span;
								case 3: 
									return openSelect + 
										'<option value="1">Public</option>'+
										'<option value="2">Private</option>'+
										'<option value="3" selected>Testing</option>'+
										'<option value="4">Community available</option></select> ' +
										span;
								case 4: 
									var select = openSelect + 
										'<option value="1">Public</option>'+
										'<option value="2">Private</option>'+
										'<option value="3">Testing</option>'+
										'<option value="4" selected>Community available</option></select> ' + span;
										for(let i = 0; i < communities; i++) {
											'<input type="checkbox" id="'+communities[i]+'" value="Bike">' +
  											'<label for="vehicle1"> I have a bike</label><br></br>';
										}
								default:
									return openSelect +
										'<option value="1">Public</option>'+
										'<option value="2">Private</option>'+
										'<option value="3">Testing</option>'+
										'<option value="4">Community available</option>' +
										'</select>';

							};
						}, "targets": 3},
						//COLUMB MANAGEMENT => DELETE BLOCK 
						{render: function(data, type, row) {
							var divDevuelto = '';
							var actionType = '';
							
							// gestion del boton actions

							// actual user == tool developer AND actual user == owner block
							// block status != registered
							
							// actual user == admin
							// block status != registered
							if (user["Type"] == 0 || (user["Type"] == 1 && row["data"]["owner"]["user"] == user["id"])) {
								if (row["validation_status"] == "registered") {
									actionType = '<div class="btn-group dropdown" style="absolute: relative;"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
										'<ul class="dropdown-menu pull-right" role="menu">' +
											'<li>' +
											'<a id="'+row._id+'" name="'+types[i]+'" onclick="deleteBlock(id, name);"><i class="fa fa-trash"></i> Delete block</a>' +
											'</li>' +
										'</ul></div>';
								} else if (row["validation_status"] == "under_validation") {
									if (user["Type"] == 0) {
										actionType = '<div class="btn-group dropdown" style="absolute: relative;"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
											'<ul class="dropdown-menu pull-right" role="menu">' +
												'<li>' +
												'<a id="'+row._id+'" name="'+types[i]+'" onclick="acceptBlock(id, name);"><i class="fa fa-check"></i> Accept block</a>' +
												'</li>' +
												'<li>' +
												'<a id="'+row._id+'" name="'+types[i]+'" onclick="editBlock(id, name);"><i class="fa fa-edit"></i> Modify block</a>' +
												'</li>' +
												'<li>' +
												'<a id="'+row._id+'" name="'+types[i]+'" onclick="deleteBlock(id, name);"><i class="fa fa-trash"></i> Delete block</a>' +
												'</li>' +
											'</ul></div>';
									} else if (user["Type"] == 1 && row["data"]["owner"]["user"] == user["id"]) {
										actionType = '<div class="btn-group dropdown" style="absolute: relative;"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
											'<ul class="dropdown-menu pull-right" role="menu">' +
												'<li>' +
												'<a id="'+row._id+'" name="'+types[i]+'" onclick="editBlock(id, name);"><i class="fa fa-edit"></i> Modify block</a>' +
												'</li>' +
												'<li>' +
												'<a id="'+row._id+'" name="'+types[i]+'" onclick="deleteBlock(id, name);"><i class="fa fa-trash"></i> Delete block</a>' +
												'</li>' +
											'</ul></div>';
									}
								} else if (row["validation_status"] == "rejected") {
									actionType = '<div class="btn-group dropdown" style="absolute: relative;"><button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions <i class="fa fa-angle-down"></i></button>' +
									'<ul class="dropdown-menu pull-right" role="menu">' +
										'<li>' +
										'<a id="'+row._id+'" name="'+types[i]+'" onclick="editBlock(id, name);"><i class="fa fa-edit"></i> Modify block</a>' +
										'</li>' +
										'<li>' +
										'<a id="'+row._id+'" name="'+types[i]+'" onclick="deleteBlock(id, name);"><i class="fa fa-trash"></i> Delete block</a>' +
										'</li>' +
									'</ul></div>';
								}
							}
							
							// gestion del recuadro de color
							if(row["validation_status"] == "under_validation") divDevuelto = '<div class="note note-success" style="background-color:rgba(109, 91, 142,0.7);border-color:rgb(109, 91,142)"><p class="font-white"><b>UNDER VALIDATION</b>:<br> Waiting for integration test results.</p><br>' + actionType + '</div>';
							else if (row["validation_status"] == "registered") divDevuelto = '<div><div class="note bg-green-jungle"><p class="font-white"><b>ACCEPTED</b>:<br/>Block validated.</p><br>' + actionType + '</div>';
							else if (row["validation_status"] == "rejected") divDevuelto = '<div><div class="note note-danger"><p class="font-red"><b>REJECTED</b>:<br/>Block not accepted.</p><br>' + actionType + '</div>';

							return divDevuelto;

						}, "targets": 4}
						
					]
				});
			}

			$(".newBlock").click(function() {
				var typeBlock = $(this).attr('name');
				location.href = "oeb_management/oeb_block/oeb_newBlock.php?action=createBlock&typeBlock="+typeBlock;
			});

		});
	});

});

//function that reload the validation table without relaod all the webpage
function reload(type) {
	$("#myError").hide();
	$.getJSON('applib/oeb_blocksAPI.php?action=getBlocks&type='+type, function() {
		var oTblReport;

		if ($.fn.dataTable.isDataTable('#'+type+'Table')) {
			oTblReport = $('#'+type+'Table').DataTable();
			oTblReport.ajax.reload();
		}

	});
}

