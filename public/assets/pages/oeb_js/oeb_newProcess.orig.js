$(document).ready(function() {
  var schema;
  var editor;
  var labels = [];
  var uris = [];

  $.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/oeb_wfs/processValidation_schema.json", function(data) {
     
    schema = data;
  })
  .done(function() {
    
    var url = [];
    //that's all... no magic, no bloated framework
    for(var [key, value, path] of traverse(schema)) {
      // do something here with each key and value
      if (key == "ontology") {
        var prueba = "schema";
        for (aa of path) {
          prueba += '["'+aa+'"]';
        }
        url.push(prueba);
      }
    }
    console.log(url);

    $.when( $.ajax( "/page1.php" ), $.ajax( "/page2.php" ) )
    .then( myFunc, myFailure ); 

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

function* traverse(schema) {
  const memory = new Set();
  function * innerTraversal (schema, path=[]) {
    if(memory.has(schema)) {
      // we've seen this object before don't iterate it
      return;
    }
    // add the new object to our memory.
    memory.add(schema);
    for (var i of Object.keys(schema)) {
      const itemPath = path.concat(i);
      yield [i,schema[i],itemPath]; 
      if (schema[i] !== null && typeof(schema[i])=="object") {
        //going one step down in the object tree!!
        yield* innerTraversal(schema[i], itemPath);
      }
    }
  }
    
  yield* innerTraversal(schema);
}

