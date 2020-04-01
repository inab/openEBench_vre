//VARIABLES
var schema;
var editor;
var labels = [];
var uris = [];

//final information
var URLontologiesArray = [];
var ancestorsArray = [];
var pathsArray = [];

//params to get final information
var paths = [];
var urlsArray = [];
var listPaths = [];


$(document).ready(function() {
  $.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/oeb_wfs/processValidation_schema.json", function(data) {
     
    schema = data;
  })
  .done(function() {

    getInformation(schema);

    //it has to be the same number of ancestors that ontologies params because for each ontology there are one ancestor. 
    if (URLontologiesArray.length == ancestorsArray.length) {
      for (i = 0; i < URLontologiesArray.length; i++) {
        var url = "https://dev-openebench.bsc.es/vre/applib/oeb_processesAPI.php?urlOntology=" + URLontologiesArray[i] + "&ancestors=" + ancestorsArray[i];
        urlsArray.push(url);
      }
    } else {
      console.log("SCHEMA INCORRECT");
    }

    let ajaxPromises = $.map(urlsArray, function(url,idx) {
      return $.ajax(url);
    });
    
    $.when(...ajaxPromises).done(function() {

      $.ajax({
        url: url,
        type: 'POST',
        success: function(data){
          $.each(data, function(key, modelName) {
            label = modelName['label'];
            
            labels.push(modelName['label']);
            uris.push(modelName['URI']);
          });
          
          for (i = 0; i < pathsArray.length; i++) {
            pathsArray[i]['enum'] = uris;
            pathsArray[i]['options']['enum_titles'] = labels;
          }
  
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
  });
});

//to get the path
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

function getInformation(schema) {
    //find in the schema the word "ontology" and "ancestors" and add them to their respective arrays
    for(var [key, value, path] of traverse(schema)) {
    
      //if key is ontology is the param ontology to give to the final url
      if (key == "ontology") {
        URLontologiesArray.push(value);

        //GET PATH OF ONTOLOGY (WITHOUT THE PARAM ONTOLOGY AT THE END - THE ANCESTOR OF THAT -)
        listPaths = []
        for (paramPath of path) {
          if (paramPath != "ontology") {
            listPaths.push(paramPath);
          }
        }
        paths.push(listPaths);
      }
      //if key is ancestor is the param ancestor to give to the final url
      if (key == "ancestors") {
        ancestorsArray.push(value);
      }
    }

    for (i = 0; i < paths.length; i++) {
      var relSchema = schema;
      for (step of paths[i]) {
        relSchema = relSchema[step];
      }
      pathsArray.push(relSchema);
    }
}

