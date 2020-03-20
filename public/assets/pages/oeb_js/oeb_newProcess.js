$(document).ready(function() {
  var schema;

  $.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/oeb_wfs/processValidation_schema.json", function(data) {
    
    initializer();  
    schema = data;
  })
  .done(function() {
    console.log("funciona");

    var variable = schema["properties"]["inputs_meta"]["properties"]["input"]["properties"]["file_type"]["items"]["ontology"];
    //console.log(schema["properties"]["inputs_meta"]["properties"]["public_ref_dir"]["properties"]["file_type"]["items"]["ontology"]);
    
    var url = "https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?urlOntology=" + variable;
    
    $.ajax({
      url: url,
      type:'POST',
      success: function(data) {
        console.log(data);
      } 
    });
    

    // Set default options
    JSONEditor.defaults.options.theme = 'bootstrap3';
    
    // Initialize the editor
    var editor = new JSONEditor(document.getElementById("editor_holder"),{
      theme: 'bootstrap3',
      schema: schema,
    });
    
    // Validate
    var errors = editor.validate();
    if(errors.length) {
      // Not valid
    }
    
    // Listen for changes
    editor.on("change",  function() {
      // Do something...
    });
  })
  .fail(function() {
    console.log("NO funciona");
  });
});

