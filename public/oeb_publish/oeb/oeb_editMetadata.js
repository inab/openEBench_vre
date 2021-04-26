var valid = false;



$(document).ready(function() {
	//files
	var filesObj =  JSON.parse(files)[0]

	//get schema
    $.getJSON(oeb_submission_schema, function(data) {
        schema = data;
    }).done(function() {

		//create jsonEditor obj
        editor = new JSONEditor(document.getElementById("editor_holder"),{
            theme: 'bootstrap4',
            ajax: true,
            schema: schema,
  
            //do not have collapse, edit and properties options in the editor (are specific things of the web-based tool - JSONEditor)
            disable_collapse: true,
            disable_edit_json: true,
            disable_properties: true
        });


		$.ajax({
			type: 'POST',
			url: "applib/oeb_publishAPI.php?action=getFileInfo",
			data: {"files" : filesObj["id"]}

		}).done(function(data) {
			var fileinfo = JSON.parse(data)
			console.log(fileinfo);
			
			$.ajax({
				type: 'POST',
				url: "applib/oeb_publishAPI.php?action=getOEBdata",
				data: {"benchmarkingEvent" : filesObj["benchmarkingEvent_id"]}
	
			}).done(function(data) {
				var OEBinfo = JSON.parse(data);


			//set values 
			$('.je-object__title label').html("<b>Edit metadata for file " +fileinfo["path"].split("/").pop() +"</b>");
			
			editor.getEditor("root.consolidated_oeb_data").setValue(fileinfo['path']);
			if (fileinfo['data_type'] == "OEB_data_model") {
				editor.getEditor("root.type").setValue("workflow_results"); //type of dataset
			} else editor.getEditor("root.type").setValue(fileinfo['data_type']);
			editor.getEditor("root.benchmarking_event_id").setValue(filesObj["benchmarkingEvent_id"]); 
			editor.getEditor("root.participant_file").setValue(fileinfo['fileSource_path']); //Path participant file
			editor.getEditor("root.community_id").setValue(OEBinfo['community_id']);
			editor.getEditor("root.tool_id").setValue(fileinfo['tool']); //participant tool id
			editor.getEditor("root.workflow_oeb_id").setValue(OEBinfo['oeb_workflow']);

			//css
			$('label[class="required"]').append('<span style="color:red;"> *</span>')

		});
		$("#loading-datatable").hide();
		editor.on('change',function() {
			// Validate the editor's current value against the schema
			var errors = editor.validate();

			if(errors.length) {
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


	$('#sendForm').on("click",function() {
		if(valid){
			var formData = JSON.stringify(editor.getValue());
			$("#formMetadata").hide();
			$("#loading-datatable").show();
			$.ajax({
				type: 'POST',
				url: "applib/oeb_publishAPI.php?action=requestPublish",
				data: {"fileId" : filesObj["id"], "metadata": formData}
			}).done (function(data) {
				$("#loading-datatable").hide();
				$("#step3").addClass("active");
				$("#result").html("<h4><b>Data successfully request!</b></h4>\
				<p style=\"font-size:1.1em;\">Nexcloud URLs: <br><pre>"+data+"</pre><br/>"+timeStamp()+"</p><br>");
                $("#result").show();
				console.log(data);

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
				+ (currentdate.getMonth()+1)  + "/" 
				+ currentdate.getFullYear() + " @ "  
				+ currentdate.getHours() + ":"  
				+ currentdate.getMinutes() + ":" 
				+ currentdate.getSeconds();
  };
  
  
  





/*
//save form data: array of objects forms
var dataForms = new Array();

$(document).ready(function() {

    //get schema
    $.getJSON(oeb_submission_schema, function(data) {
        schema = data;
    }).done(function() {

        //create jsonEditor obj
        editor = new JSONEditor(document.getElementById("editor_holder"),{
            theme: 'bootstrap4',
            ajax: true,
            schema: schema,
  
            //do not have collapse, edit and properties options in the editor (are specific things of the web-based tool - JSONEditor)
            disable_collapse: true,
            disable_edit_json: true,
            disable_properties: true
        });
    });

});


/**********FUNCTIONS ************** */
/*




*/


/*

Extends the default string editor format with support for  autocomplete/suggestions using twitter typeahead + bloodhound.

Also adds support for setting "placeholder" through options.

*/


/*
var roadEngine = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace("_id"),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		type: 'POST',
		url: "https://dev-openebench.bsc.es/api/scientific/access/Community?q=%QUERY",
		wildcard: "%QUERY"

	}
		
	
});
roadEngine.clearRemoteCache();
roadEngine.initialize();

JSONEditor.defaults.editors.typeahead = class myeditor extends JSONEditor.defaults.editors.string {
    build() {
            super.build()
			if (!this.input) return;

			if (window.jQuery && window.jQuery.fn && window.jQuery.fn.typeahead) {
				var defaults = {
					config: {
						autoselect: true,
						hint: true,
						highlight: false,
						minLength: 2
					},
					datasets: {
						source: "",
						limit: 10
					}
				};
				
				var self = this;
				var options = $.extend(true, {}, defaults, this.options.typeahead);
				$(this.input)
					.typeahead(options.config, options.datasets)
					.on("typeahead:asyncrequest", function() {
						// Enable spinner
						$(self.input)
							.parent()
							.addClass("input-loading");
					})
					.on("typeahead:asynccancel typeahead:asyncreceive", function() {
						// Disable spinner
						$(self.input)
							.parent()
							.removeClass("input-loading");
					});

				// Change parent (wrapper) display from inline-block to block
				$(this.input)
					.parent()
					.css("display", "block");
			}

		
		}
	}


JSONEditor.defaults.resolvers.unshift(function(schema) {
	if (schema.type === "string" && schema.format === "typeahead") {
		return "typeahead";
	}
});

var options = {
	theme: "bootstrap4",
	schema: {
		"id": "https://github.com/inab/submission-form-json-schemas",
	"$schema": "http://json-schema.org/draft-07/schema#",
	"title": "Schema that defines the set of fields to be filled for a submission to OpenEBench database",
	"type": "object",
		options: {
			disable_edit_json: true,
			disable_properties: true
		},
		"properties": {
		
			"consolidated_oeb_data": {
			"title": "Consolidated dataset path",
			"description": "Path to the consolidated dataset, coming from an OpenEBench standardized benchmarking workflow (e.g.https://github.com/inab/TCGA_benchmarking_workflow",
			"type": "string",
			"readonly": true
		},
		"visibility": {
			"title": "Datasets visibility",
			"description": "The desired visibility of the submitted datasets, which must be acknowledged by the APIs",
			"type": "string",
			"enum": ["public", "community", "challenge", "participant"]
		},
		"type": {
			"title": "Dataset type",
			"description": "Type of dataset to be submitted. See official benchmarking data model for more info",
			"type": "string",
			"mingLength": 1,
			"enum": ["public_reference", "metrics_reference", "input", "participant", "workflow_results"]
		},
		"benchmarking_event_id": {
			"title": "Benchmarking event",
			"description": "The unique id of the benchmarking event the dataset belongs to, as is returned by the API",
			"type": "string",
			"pattern": "^OEBE[0-9]{3}[A-Z0-9]{7}$"
		},
		"participant_file": {
			"title": "Participant file associated",
			"description": "Path to the participant file associated with the consolidated results",
			"type": "string"
		},
		"community_id": {
			"title": "OEB community",
			"description": "The unique id of the community where the data should be uploaded. Should come from VRE workflow",
			"type": "string",
			"pattern": "^OEBC[0-9]{3}$"
		},
		"tool_id": {
			"title": "Participant Tool",
			"description": "The id of the tool used to generate the dataset. Should be selected by uploader, using API",
			"type": "string",
			"pattern": "^OEBT[0-9]{3}[A-Z0-9]{7}$"
		},
		"data_version": {
			"title": "Version",
			"description": "Version (or release date) of the dataset",
			"minLength": 1,
			"type": "string"
		},
		"workflow_oeb_id": {
			"title": "OEB Workflow",
			"description": "The id of the workflow (as a tool) used to compute the assessment metrics. Should be associated to VRE tool",
			"type": "string",
			"pattern": "^OEBT[0-9]{3}[A-Z0-9]{7}$"
		},
		"data_contacts": {
			"title": "Contacts",
			"description": "Emails of the dataset contact(s). Should be registered in Mongo and OIDC",
			"type": "array",
			"minItems": 1,
			"uniqueItems": true,
			"items": {
				"type": "string"
			}
		},
			test1: {
				type: "object",
				title: " ",
				options: {
					disable_collapse: true,
					disable_edit_json: true,
					disable_properties: true
				},
				properties: {
					autotext: {
						type: "string",
						format: "typeahead",
						title: "OEB community",
						description:
							"The unique id of the community where the data should be uploaded. Should come from VRE workflow",
						options: {
							typeahead: {
								config: {
									autoselect: true,
									highlight: true,
									hint: true,
									minLength: 3
								},
								datasets: {
									name: "communities_id",
									display: "_id",
									source: roadEngine.ttAdapter()
								}
							}
						}
					}
				}
			}

		},
		"required": [
			"consolidated_oeb_data",
			"visibility",
			"type",
			"benchmarking_event_id",
			"participant_file",
			"community_id",
			"tool_id",
			"data_version",
			"workflow_oeb_id",
			"data_contacts"
		],
		"additionalProperties": false,
		"show_errors": "interaction"
	}
};

var element = document.getElementById("editor_holder");
var editor = new JSONEditor(element, options);



function openForm(fileId, filename, benchmarking_event) {

    $('#editor_holder').show();
	$('.je-object__title label').html("<b>Edit metadata for file " +filename +"</b>")

    $.ajax({
        type: 'POST',
        url: "applib/oeb_publishAPI.php?action=getFileInfo",
        data: {"files" : fileId}

    }).done(function(data) {
		var fileinfo = JSON.parse(data);
		$.ajax({
			type: 'POST',
			url: "applib/oeb_publishAPI.php?action=getOEBdata",
			data: {"benchmarkingEvent" : benchmarking_event}
	
		}).done(function(data) {
			var OEBinfo = JSON.parse(data);
			console.log(OEBinfo)


        //set values 
        editor.getEditor("root.consolidated_oeb_data").setValue(fileinfo['path']);
        if (fileinfo['data_type'] == "OEB_data_model") {
            editor.getEditor("root.type").setValue("workflow_results"); //type of dataset
        } else editor.getEditor("root.type").setValue(fileinfo['data_type']);
        editor.getEditor("root.benchmarking_event_id").setValue(benchmarking_event); 
        editor.getEditor("root.participant_file").setValue(fileinfo['fileSource_path']); //Path participant file
        editor.getEditor("root.community_id").setValue(OEBinfo['community_id']);
        editor.getEditor("root.tool_id").setValue(fileinfo['tool']); //participant tool id
		editor.getEditor("root.workflow_oeb_id").setValue(OEBinfo['oeb_workflow']);

        //css
        $('label[class="required"]').append('<span style="color:red;"> *</span>')

    });
    editor.on('change',function() {
         // Validate the editor's current value against the schema
        var errors = editor.validate();

        if(errors.length) {
        // errors is an array of objects, each with a `path`, `property`, and `message` parameter
        // `property` is the schema keyword that triggered the validation error (e.g. "minLength")
        // `path` is a dot separated path into the JSON object (e.g. "root.path.to.field")
        console.log(errors);
        }
        else {
        // It's valid!, enable de button
        $('#saveForm').prop('disabled', false);


        }
    })
})
}
*/