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
            schema: schema,
  
            //do not have collapse, edit and properties options in the editor (are specific things of the web-based tool - JSONEditor)
            disable_collapse: true,
            disable_edit_json: true,
            disable_properties: true
        });
    });

});


/**********FUNCTIONS ************** */

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
        if (fileinfo['data_type'] == "assessment") {
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
