//VARIABLES
var schema;
var editor;
var labels = [];
var uris = [];
var urlsArray = [];
var owners = [];
var value;
var editorValue;
var urlJSON = "applib/oeb_processesAPI.php";

$(document).ready(function() {
  //$.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/oeb_wfs/processValidation_schema.json", function(data) {
  $.getJSON("https://raw.githubusercontent.com/inab/OpEB-VRE-schemas/workflow_schema/oeb_workflow_schema_prueba.json", function(data) {
     
    schema = data;
  })
  .done(function() {
    var baseURL = $("#base-url").val();

    let [pathsArray, ancestorsArray, URLontologiesArray] = getInformation(schema);

    //it has to be the same number of ancestors that ontologies params because for each ontology there are one ancestor. 
    if (URLontologiesArray.length == ancestorsArray.length) {
      for (i = 0; i < URLontologiesArray.length; i++) {
        var url = "applib/oeb_processesAPI.php?action=getForm&urlOntology=" + URLontologiesArray[i] + "&ancestors=" + ancestorsArray[i];
        urlsArray.push(url);
      }
    } else {
      console.log("SCHEMA INCORRECT");
    }

    let ajaxPromises = $.map(urlsArray, function(url,idx) {
      return $.ajax({url:url, type:'POST'});
    });

    $.when(...ajaxPromises).done(function() {
      for (x = 0; x < arguments.length; x++) {
        labels = [];
        uris = [];
        totals = [];
        $.each(arguments[x][0], function(key, modelName) {
          labels.push(modelName['label']);
          uris.push(modelName['URI']);

          pathsArray[x]['enum'] = uris;
          pathsArray[x]['options']['enum_titles'] = labels;
        });
      };

      //INSERT THE OWNER AND SCHEMA
      var urlDefaultValues = "applib/oeb_processesAPI.php?action=getDefaultValues&owner&_schema";
      $.ajax({
        type: 'POST',
        url: urlDefaultValues,
        data: url
      }).done(function(data) {
        //INICIALIZA EL FORMULARIO - NO HASTA QUE EL SELECT ESTA CARGADO
        //initializer(); 

        // Initialize the editor
        editor = new JSONEditor(document.getElementById("editor_holder"),{
          theme: 'bootstrap4',
          schema: schema,
          disable_collapse: true,
          disable_edit_json: true,
          disable_properties: true
        });

        var defaultVariables = ["_schema", "owner"];

        //DATA only has _SCHEMA AND OWNER. If we want another variable we have to implement in the API and get it
        for (x = 0; x < defaultVariables.length; x++) {
          if (defaultVariables[x] == "owner") {
            var value = {
              "institution": data["owner"]["institution"],
              "author": data["owner"]["author"],
              "contact": data["owner"]["contact"],
              "user": data["owner"]["user"]
            };
            editor.getEditor("root.owner").setValue(value);
          } else {
            editor.getEditor("root." + defaultVariables[x]).setValue(data[defaultVariables[x]]);
          }
        }

        //change name of the navbar 
        $('a[href="#Generic-keywords"]').text("STEP 2: Generic keywords");
        $('a[href="#Custom-keywords"]').text("STEP 3: Custom keywords");
        $('a[href="#Nextflow-files"]').text("STEP 4: Nextflow files");
        $('a[href="#Input-files-&-arguments"]').text("STEP 5: Input files & arguments");
        
        //$('div[data-schemapath="root.inputs_meta.public_ref_dir.value"] input').attr("accept", ".tar");
        $("#loading-datatable").hide();
        $("#submit").show();

        //ON CLICK SUBMIT 
        clickSubmit();

        editorValue = editor.getValue();
      });
    }).fail(function (jqXHR, textStatus) {
      console.log("ERROR");
    })
  });
});

function clickSubmit() {
  
  $('#submit').on("click",function() {
    //var encodeFile = encodeURIComponent(file);

    //if there are errors = true, no errors = false
    var errors = validateErr();

    //if there are not errors
    if (!errors) {
      //give the value to editorValue
      var json = JSON.stringify(editor.getValue(),null,2);

      //insert into db
      insertJSON(json);
    } 

    $(".errorClass").show();
  });
}

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
  //final information
  var URLontologiesArray = [];
  var ancestorsArray = [];
  var pathsArray = [];

  //params to get final information
  var paths = [];
  var listPaths = [];

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

  return [pathsArray, ancestorsArray, URLontologiesArray];
}

function insertJSON(processJSONForm) {

  $.ajax({
    type: 'POST',
    url: urlJSON,
    data: {'action': 'setProcess', 'processForm': processJSONForm}
  }).done(function(data) {
    if(data['code'] == 200) {
      window.location.href = baseURL + "oeb_management/oeb_process/oeb_processes.php";
      
      $(".errorClass").removeClass(" alert alert-danger");
      $(".errorClass").addClass(" alert alert-info");
      $(".errorClass").text("Workflow inserted successfully.");
      console.log("WORKFLOW INSERTED IN MONGODB.");

    } else {
      $(".errorClass").text(data['message']);
      $(".errorClass").removeClass(" alert alert-info");
      $(".errorClass").addClass(" alert alert-danger");
    }
  }).fail(function(data) {
    var error = JSON.parse(data['responseText']);
    $(".errorClass").text(error['message']);
    $(".errorClass").removeClass(" alert alert-info");
    $(".errorClass").addClass(" alert alert-danger");
  });
}

function validateErr() {
  var errors = editor.validate();

  if(errors.length != 0) {
    console.log("ERRORS: ");
    console.log(errors);
    fileError = 0;
    editor.options.show_errors = "always";

    for(let j = 0; j < errors.length; j++) {
      if (errors[j]["path"] == "root.inputs_meta.public_ref_dir.value") {
        fileError = 1;
      }
    }

    //the error file do not works propertly (count as an error but do not look red color) so I do manually
    if(fileError) {
      $(".form-control-file").css({"color": "red"});
    } else {
      $(".form-control-file").css({"color": "#333"});
    }

    $(".errorClass").text("There are errors in some fields of the form.");
    $(".errorClass").removeClass(" alert alert-info");
    $(".errorClass").addClass(" alert alert-danger");

    // Fire a change event to force revalidation
    editor.onChange();
    return true;
  } else {
    return false;
  }
} 