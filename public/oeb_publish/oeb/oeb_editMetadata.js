var valid = false;

$(document).ready(function () {
	//files
	var filesObj = JSON.parse(files)[0]
	console.log(filesObj);

	$.ajax({
		type: 'POST',
		url: "applib/oeb_publishAPI.php?action=getFileInfo",
		data: { "files": filesObj["id"] }

	}).done(function (data) {
		var fileinfo = JSON.parse(data);
		$.ajax({
			type: 'POST',
			url: "applib/oeb_publishAPI.php?action=getOEBdata",
			data: { "benchmarkingEvent": filesObj["benchmarkingEvent_id"] }

		}).done(function (data) {
			var OEBinfo = JSON.parse(data);

			//get schema
			$.getJSON(oeb_submission_schema, function (data) {
				schema = data;
			}).done(function () {

				//create jsonEditor obj
				editor = new JSONEditor(document.getElementById("editor_holder"), {
					theme: 'bootstrap4',
					ajax: true,
					schema: schema,

					//do not have collapse, edit and properties options in the editor (are specific things of the web-based tool - JSONEditor)
					disable_collapse: true,
					disable_edit_json: true,
					disable_properties: true
				});
				window.JSONEditor.defaults.callbacks = {
					"autocomplete": {
						// https://autocomplete.trevoreyre.com/#/javascript-component

						// Setup API calls
						//1. Tools list
						"search_za": function search(jseditor_editor, input) {
							var url = 'https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getTools';

							return new Promise(function (resolve) {
								if (input.length < 1) {
									return resolve([]);
								}

								fetch(url).then(function (response) {
									return response.json();
								}).then(function (data) {
									var r = data.filter(tool => {
										let nameValid = false;

										if (input && input != "") {
											if (tool.name.toLowerCase().indexOf(input.toLowerCase()) != -1) {
												nameValid = true;
											}
										} else {
											nameValid = true;
										}
										return nameValid
									})

									resolve(r);
								});
							});
						},
						"renderResult_za": function (jseditor_editor, result, props) {
							return ['<li ' + props + '>',
							'<div class="eiao-object-snippet">' + result.name + ' <small>' + result._id + '<small></div>',
								'</li>'].join('');
						},
						"getResultValue_za": function getResultValue(jseditor_editor, result) {
							return result._id;
						},

						//2. Contacts list
						"search_zza": function search(contacts_editor, input) {
							var url = 'https://dev-openebench.bsc.es/vre/applib/oeb_publishAPI.php?action=getContacts&community_id=' + OEBinfo['community_id'];

							return new Promise(function (resolve) {
								if (input.length < 1) {
									return resolve([]);
								}

								fetch(url).then(function (response) {
									return response.json();
								}).then(function (data) {
									var c = data.filter(contact => {
										let contactValid = false;

										if (input && input != "") {
											if (contact._id.toLowerCase().indexOf(input.toLowerCase()) != -1) {
												contactValid = true;
											}
										} else {
											contactValid = true;
										}
										return contactValid
									})

									resolve(c);
								});
							});
						},
						"renderResult_zza": function (contacts_editor, result, props) {
							return ['<li ' + props + '>',
							'<div class="eiao-object-snippet">' + result._id,
								'</li>'].join('');
						},
						"getResultValue_zza": function getResultValue(contacts_editor, result) {
							return result._id;
						}
					}
				}


				//set values 
				$('.je-object__title label').html("<b>Edit metadata for file " + fileinfo["path"].split("/").pop() + "</b>");

				editor.getEditor("root.consolidated_oeb_data").setValue(fileinfo['path']);

				editor.getEditor("root.benchmarking_event_id").setValue(filesObj["benchmarkingEvent_id"]);
				editor.getEditor("root.participant_file").setValue(fileinfo['fileSource_path']); //Path participant file
				editor.getEditor("root.community_id").setValue(OEBinfo['community_id']);
				editor.getEditor("root.workflow_oeb_id").setValue(filesObj['tool']);
				editor.getEditor("root.data_version").setValue("1");

				//css
				$('label[class="required"]').append('<span style="color:red;"> *</span>')


				$("#loading-datatable").hide();
				editor.on('change', function () {
					// Validate the editor's current value against the schema
					var errors = editor.validate();

					if (errors.length) {
						// errors is an array of objects, each with a `path`, `property`, and `message` parameter
						// `property` is the schema keyword that triggered the validation error (e.g. "minLength")
						// `path` is a dot separated path into the JSON object (e.g. "root.path.to.field")
						console.log(errors);
					}
					else {
						// It's valid!, enable de button
						valid = true;
						$('#sendForm').prop('disabled', false);


					}
				})
			});

		});
	});


	$('#sendForm').on("click", function () {
		if (valid) {
			var formData = JSON.stringify(editor.getValue());
			$("#formMetadata").hide();
			$("#loading-datatable").show();
			$.ajax({
				type: 'POST',
				url: "applib/oeb_publishAPI.php?action=requestPublish",
				data: { "fileId": filesObj["id"], "metadata": formData }
			}).done(function(data) {
				//no errors
				if(data["code"]=="200"){
					$("#loading-datatable").hide();
					$("#step3").addClass("active");
					$("#myError").removeClass("alert alert-danger");
					$("#myError").addClass("alert alert-info");
					$("#myError").append("<h4><b>New request successfully created: </b></h4><a href='vre/oeb_publish/oeb/oeb_manageReq.php'>"+data['message']['petition']+"</a></br></br>");
					$("#myError").append(data['message']["email"]+"<br><br>"+timeStamp());
					$("#myError").show();
					console.log(data);
					//errors
				} else {
					$("#loading-datatable").hide();
					$("#step3").addClass("active");
					$("#myError").removeClass("alert alert-info");
					$("#myError").addClass("alert alert-danger");
					$("#myError").text(data["message"]);
					$("#myError").show();
				}
				
			//more errors
			}). fail(function(data) {
				$("#loading-datatable").hide();
				$("#step3").addClass("active");
				$("#myError").removeClass("alert alert-info");
				$("#myError").addClass("alert alert-danger");
				$("#myError").text(data["responseJSON"]["message"]);
				$("#myError").show();
				
			});
				

		

		}
	})
})



/**
 * timeStamp function. Gets the current day and time with readeable format
 * @listens none
 * @param none
 * @return {string} - the timestamp
 */
function timeStamp() {
	var currentdate = new Date();
	return datetime = "Time: " + currentdate.getDate() + "/"
		+ (currentdate.getMonth() + 1) + "/"
		+ currentdate.getFullYear() + " @ "
		+ currentdate.getHours() + ":"
		+ currentdate.getMinutes() + ":"
		+ currentdate.getSeconds();
};
