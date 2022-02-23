var valid = false;
const CONTROLLER = 'applib/oeb_publishAPI.php'
var currentURL = window.location["href"];
$(document).ready(function () {
	//files
	var filesObj = JSON.parse(files)[0]

	$.ajax({
		type: 'POST',
		url: "applib/oeb_publishAPI.php?action=getFileInfo",
		data: { "files": filesObj["id"] }

	}).done(function (data) {
		var fileinfo = JSON.parse(data);
		$.ajax({
			type: 'POST',
			url: CONTROLLER + "?action=getOEBdata",
			data: { "benchmarkingEvent": filesObj["benchmarkingEvent_id"] }

		}).done(function (data) {
			var OEBinfo = JSON.parse(data);
			$.ajax({
				type: 'POST',
				url: CONTROLLER + "?action=getUserInfo",
				data: currentURL
        	}).done(function(data) {
          
          	var userinfo = JSON.parse(data);

			

			//get schema
			$.getJSON(oeb_submission_schema, function (data) {
				schema = data;
			}).done(function () {

				//create jsonEditor obj
				editor = new JSONEditor(document.getElementById("editor_holder"), {
					theme: 'bootstrap4',
					ajax: true,
					schema: schema,

					//do not have collapse, edit and properties options in the 
					//editor (are specific things of the web-based tool - JSONEditor)
					disable_collapse: true,
					disable_edit_json: true,
					disable_properties: true,
					remove_empty_properties: true
				});
				window.JSONEditor.defaults.callbacks = {
					"autocomplete": {
						
						//2. Contacts list
						"search_zza": function search(contacts_editor, input) {
							var url = CONTROLLER + '?action=getContacts&community_id=' + OEBinfo['community_id'];

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
					},
					"selectize": {
						render: {
							option: function(jseditor_editor2, data, escape) {
								return '<div>' +
									'<span class="title">' +
										'<span class="name">' + escape(data.name) + '</span>' +
									'</span>' +
								'</div>';
							}
						},
						load: function(jseditor_editor2, query, callback) {
							if (!query.length) return callback();
							$.ajax({
								url: CONTROLLER + '?action=getTools',
								type: 'POST',
								error: function() {
									callback();
								},
								success: function(res) {
									callback(JSON.parse(res));
								}
							});
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
				editor.getEditor("root.data_contacts.0").setValue(userinfo['Email']);
				$(".form-text:eq(6)" ).append(". <b>If your tool does not appear in list, contact with: \
					</b><a href=\"mailto:"+mail_support_oeb+"\">"+mail_support_oeb+"</a>.");
				
				//css
				$('[data-schemapath="root.data_contacts"] h3 label').append('<span style="color:red;"> *</span>')
				$('[data-schemapath="root.tool_selection"] label').append('<span style="color:red;"> *</span>')
				$('label[class="required"]').append('<span style="color:red;"> *</span>')
				

				$("#loading-datatable").hide();

				editor.on('change', function () {
					// Validate the editor's current value against the schema
					$('#sendForm').prop('disabled', true);
					var errors = editor.validate();
					var toolId = $('select.selectized,input.selectized').val();
					var contacts = editor.getEditor('root.data_contacts');
					
					$("#toolSubmit").hide();
					if (errors.length == 0 && contacts.getValue()[0] != "" && toolId !="") {
						// It's valid!, enable de button
						valid = true;
						$('#sendForm').prop('disabled', false);
						
					}
						
				})
			});
		});

		});
	});

	$('#sendForm').on("click", function () {
		if (valid) {
			var toolId = $('select.selectized,input.selectized').val();
			var contactsList = []
			
			editor.getEditor("root.tool_id").setValue(toolId);
			var formData = JSON.stringify(editor.getValue(),null,4);
			$('select[multiple="multiple"],input.selectized').each(function(){
				contactsList.push($(this).val());
			});
			editor.getEditor("root.data_contacts").setValue(contactsList);
			
			//check tool can be submitted 
			$.ajax({
				type: 'POST',
				url: CONTROLLER + "?action=toolToSubmit",
				data: { "metadata": formData }

			}).done(function(data) {
				if (data == '0'){
					$("#myModal").modal();
					$("#summaryContent").html("<pre>"+formData+"</pre>")

					//when submit on modal is clicked
					$('#submitModal').on("click",function() {
						$("#closeModal").trigger("click");
						$("#formMetadata").hide();
						$("#loading-datatable").show();
				
						$.ajax({
							type: 'POST',
							url: CONTROLLER + "?action=requestPublish",
							data: { "fileId": filesObj["id"], "metadata": formData }
						}).done(function(data) {
							//no errors
							$("#myError").removeClass("alert alert-danger");
							$("#myError").addClass("alert alert-info");
							$("#myError").append("<h4><b>OpenEBench data publication request successfully created: </b></h4>");
							$("#loading-datatable").hide();
							$("#step3").addClass("active");
							var reqID = data['message'][0].match(/vre-oebreq_.+/);
							var listLog = "";
							for (let index = 0; index < data['message'].length; index++) {
								listLog += "<li>"+data['message'][index]+"<i class='fa fa-check'></i></li>";
							}
							$("#myError").append("<ul>"+listLog+"</ul");
							$("#myError").append("<br><br>"+timeStamp());
							$("#viewRequests").attr("onclick", 'location.href="oeb_publish/oeb/oeb_manageReq.php#'+reqID+'"')
							$("#finalBanner").show();

						//more errors
						}). fail(function(data) {
							$("#myError").removeClass("alert alert-info");
							$("#myError").addClass("alert alert-danger");
							$("#loading-datatable").hide();
							$("#step3").addClass("active");
							var listLog = "";
							for (let index = 0; index < data.responseJSON['message'].length-1; index++) {
								listLog += "<li>"+data.responseJSON['message'][index]+"<i class='fa fa-check'></i></li>";
							}
							listLog += "<li>"+data.responseJSON['message'][data.responseJSON['message'].length-1]+"<i class='fa fa-times-circle'></i></li>";
							$("#myError").append("<ul>"+listLog+"</ul");
							$("#myError").append("<br><br>"+timeStamp());
							$("#finalBanner").show();
					
						});
				
					});

				} else{
					$("#toolSubmit span").html(editor.getEditor('root.tool_selection').getValue())
					$("#toolSubmit").show();
				}
			
			})

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
