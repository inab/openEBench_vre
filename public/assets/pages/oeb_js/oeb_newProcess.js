$(document).ready(function() {
  var schema;
  var editor;
  var labels = [];
  var uris = [];

  $.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/oeb_wfs/processValidation_schema.json", function(data) {
     
    schema = data;
  })
  .done(function() {
    //var ontologyy = dom.select("//ontology")
    //console.log(ontologyy);
    
    //var variables = FIND DEL CAMPO "ONTOLOGY" EN EL SCHEMA Y QUE DEVUELVA EL PATH
    var pathOntology = schema["properties"]["inputs_meta"]["properties"]["input"]["properties"]["file_type"]["items"];
    
    var urlOntology = pathOntology["ontology"];
    var ancestors = pathOntology["ancestors"];

    var url = "https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?urlOntology=" + urlOntology + "&ancestors=" + ancestors;
    $.ajax({
      url: url,
      type: 'POST',
      success: function(data){
        $.each(data, function(key, modelName) {
          label = modelName['label'];
          
          labels.push(modelName['label']);
          uris.push(modelName['URI']);
        });
        
        pathOntology['enum'] = uris;
        pathOntology['options']['enum_titles'] = labels;

        //INICIALIZA EL FORMULARIO - NO HASTA QUE EL SELECT ESTA CARGADO
        initializer(); 
        // Set default options
        JSONEditor.defaults.options.theme = 'bootstrap3';
        
        // Initialize the editor
        editor = new JSONEditor(document.getElementById("editor_holder"),{
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
      }
    });
    

  })
  .fail(function() {
    console.log("NO funciona");
  });
});

