$(document).ready(function() {
  $('#formats2').multiselect();
  $("#formats").hide();
  var schema;
  var editor;
  $.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/oeb_wfs/processValidation_schema.json", function(data) {
     
    schema = data;
  })
  .done(function() {

    var variable = schema["properties"]["inputs_meta"]["properties"]["input"]["properties"]["file_type"]["items"]["ontology"];
    //console.log(schema["properties"]["inputs_meta"]["properties"]["public_ref_dir"]["properties"]["file_type"]["items"]["ontology"]);
    
    var url = "https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?urlOntology=" + variable;
    $.ajax({
      url: url,
      type: 'POST',
      success: function(data){
          //Log the data to the console so that
          //you can get a better view of what the script is returning.
          $.each(data, function(key, modelName){
            //Use the Option() constructor to create a new HTMLOptionElement.
            var option = new Option(modelName, modelName);
            //Convert the HTMLOptionElement into a JQuery object that can be used with the append method.
            $(option).html(modelName);
            //Append the option to our Select element.
            $("#formats").append(option);
          });
          $('#formats').multiselect();

          //INICIALIZA EL FORMULARIO - NO HASTA QUE EL SELECT ESTA CARGADO
          initializer(); 
          // Set default options
          JSONEditor.defaults.options.theme = 'bootstrap3';
          
          // Initialize the editor
          editor = new JSONEditor(document.getElementById("editor_holder"),{
            theme: 'bootstrap3',
            schema: schema,
          });
          
          //Change the text of the default "loading" option.
      }
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

