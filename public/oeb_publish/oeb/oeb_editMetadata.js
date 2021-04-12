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
function openForm(fileId, filename) {

    $('#editor_holder').show();
    $('.je-object__title label').html("<b>Edit metadata for file " +filename +"</b>")

    $.ajax({
        type: 'POST',
        url: "applib/oeb_publishAPI.php?action=getFileInfo",
        data: {"files" : fileId}

    }).done(function(data) {
        var fileinfo = JSON.parse(data);
        console.log(fileinfo);


        //set values 
        editor.getEditor("root.consolidated_oeb_data").setValue(fileinfo['path']);
        if (fileinfo['data_type'] == "OEB_data_model") {
            editor.getEditor("root.type").setValue("workflow_results"); //type of dataset
        } else editor.getEditor("root.type").setValue(fileinfo['data_type']);
        editor.getEditor("root.benchmarking_event_id").setValue(""); //API request
        editor.getEditor("root.participant_file").setValue(fileinfo['fileSource_path']); //Path participant file
        editor.getEditor("root.community_id").setValue("");
        editor.getEditor("root.tool_id").setValue(fileinfo['tool']); //participant tool id

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

}
$('#saveForm').on("click",function() {
    var formData = editor.getValue()
    dataForms.push(formData); 
    console.log(dataForms);
})

function submitForms(){
}


*/




/*

Extends the default string editor format with support for  autocomplete/suggestions using twitter typeahead + bloodhound.

Also adds support for setting "placeholder" through options.

*/

var roadEngine = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace("_id"),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: "https://openebench.bsc.es/api/scientific/access/Community?q=%QUERY",
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
			disable_edit_json: false,
			disable_properties: false
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
		}
	}
};

var element = document.getElementById("editor_holder");
var editor = new JSONEditor(element, options);



function openForm(fileId, filename) {

    $('#editor_holder').show();
}