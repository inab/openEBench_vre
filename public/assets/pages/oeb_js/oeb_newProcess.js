//VARIABLES
var schema;
var editor;
var labels = [];
var uris = [];
var urlsArray = [];
var owners = [];
var ontologiesUsed = [];
var arrayOntologies = [];
var informationUsed = [];
var value;
var editorValue;
var urlJSON = "applib/oeb_processesAPI.php";
var processEdit;
var filePaths = [];

$(document).ready(function() {
  var currentURL = window.location["href"];
  var url = new URL(currentURL);
  var typeProcess = url.searchParams.get("typeProcess");

  var oeb_schema;
  if(typeProcess == "newValidation" || typeProcess == "validation") {
    oeb_schema = oeb_validation_schema;
  } else if (typeProcess == "newMetrics" || typeProcess == "metrics") {
    oeb_schema = oeb_metrics_schema;
  } else if (typeProcess == "newConsolidation" || typeProcess == "consolidation") {
    oeb_schema = oeb_consolidation_schema;
  }

  //get the JSON Schema
  $.getJSON(oeb_schema, function(data) {
    schema = data;
  }).done(function() {

    //get all the necessary information about the ontologies
    let [pathsArray, ancestorsArray, URLontologiesArray] = getInformation(schema);

    //it has to be the same number of ancestors that ontologies params because for each ontology there are one ancestor. 
    if (URLontologiesArray.length == ancestorsArray.length) {
      for (i = 0; i < URLontologiesArray.length; i++) {
        var url = "applib/oeb_processesAPI.php?action=getForm&urlOntology=" + URLontologiesArray[i] + "&ancestors=" + ancestorsArray[i];
        urlsArray.push(url);
      }
    }

    //do ajax petition for each ontology url of the schema 
    let ajaxPromises = $.map(urlsArray, function(url,idx) {
      return $.ajax({url:url, type:'POST'});
    });

    //when petitions finished
    $.when(...ajaxPromises).done(function() {
      for (x = 0; x < arguments.length; x++) {
        if (arguments[x][0][0]["label"] != "oeb_formats" && arguments[x][0][0]["label"] != "oeb_datasets") {
          labels = [];
          uris = [];
          ontologiesUsed = [];

          $.each(arguments[x][0], function(key, modelName) {
            labels.push(modelName['label']);
            uris.push(modelName['URI']);

            //asign the uris of ontologies in the enum
            pathsArray[x]['enum'] = uris;
            //assign the label of ontologies into the enum titles to see in the interfaces the labels but work with uris
            pathsArray[x]['options']['enum_titles'] = labels;
          });

          ontologiesUsed["URL"] = pathsArray[x]["ontology"];
          ontologiesUsed["label"] = labels;
          ontologiesUsed["URI"] = uris;

          arrayOntologies.push(ontologiesUsed);

        } else if (arguments[x][0][0]["label"] == "oeb_formats") {
          informationUsed = [];
          for (y = 0; y < arrayOntologies.length; y++) {
            if (arrayOntologies[y]["URL"] == "https://w3id.org/oebDataFormats") {
              informationUsed = arrayOntologies[y];

              //asign the uris of ontologies in the enum
              pathsArray[x]['enum'] = informationUsed["URI"];
              //assign the label of ontologies into the enum titles to see in the interfaces the labels but work with uris
              pathsArray[x]['options']['enum_titles'] = informationUsed["label"];
            }
          }
        } else if (arguments[x][0][0]["label"] == "oeb_datasets") {
          informationUsed = [];
          for (y = 0; y < arrayOntologies.length; y++) {
            if (arrayOntologies[y]["URL"] == "https://w3id.org/oebDatasets") {
              informationUsed = arrayOntologies[y];

              //asign the uris of ontologies in the enum
              pathsArray[x]['enum'] = informationUsed["URI"];
              //assign the label of ontologies into the enum titles to see in the interfaces the labels but work with uris
              pathsArray[x]['options']['enum_titles'] = informationUsed["label"];
            }
          }
        }
      }

      //INSERT THE OWNER
      var urlDefaultValues = "applib/oeb_processesAPI.php?action=getDefaultValues&owner";
      $.ajax({
        type: 'POST',
        url: urlDefaultValues,
        data: url
      }).done(function(data) {

        // Initialize the editor
        editor = new JSONEditor(document.getElementById("editor_holder"),{
          theme: 'bootstrap4',
          schema: schema,
          //do not have collapse, edit and properties options in the editor (are specific things of the web-based tool - JSONEditor)
          disable_collapse: true,
          disable_edit_json: true,
          disable_properties: true
        });
        //if there are oeb_community param in the process
        if (data["oeb_community"]) {
          //DATA only has OWNER. If we want another variable we have to implement in the API and get it
          var value = {
            "institution": data["Inst"],
            "author": data["Name"],
            "contact": data["_id"],
            "user": data["id"],
            "oeb_community": data["oeb_community"]
          };
        //if are not oeb community param
        } else {
          var value = {
            "institution": data["Inst"],
            "author": data["Name"],
            "contact": data["_id"],
            "user": data["id"],
            "oeb_community": ''
          };
        }
        
        //set the values internally in the form
        editor.getEditor("root.owner").setValue(value);

        //change name of the navbar of steps to see it like I want (the JSONEditor, actually, do not have the option to change that)
        $('a[href="##Nextflow-files,-Dockerfile-and-YML-file"]').text("STEP 2: Nextflow Files, Dockerfile and YML File");
        $('a[href="#Input-files-&-arguments"]').text("STEP 3: Testing Integration Test");
        $('a[href="#Output-results"]').text("STEP 4: Output results");

        for(var i = 0; i < $('input[type="file"]').length; i++) {
          var input = $('input[type="file"]');

          input.eq(i).attr('id', 'input_' + i);
          filePaths.push($("#input_"+i).parent().parent().attr("data-schemapath"));
        }

        //get the argument/action
        var currentURL = window.location["href"];
        var url = new URL(currentURL);
        var action = url.searchParams.get("action");
        var idProcess = url.searchParams.get("id");
        var type = url.searchParams.get("typeProcess");

        if (action == "editProcess") {
          $(".page-title").text("Edit process");
          $("#spanCreate").text("Edit process");
          $.ajax({
            type: 'POST',
            url: urlJSON,
            data: {'action': 'getProcess', 'id': idProcess}
          }).done(function(data) {
            processEdit = data;
            editor.setValue(data["data"]);

            //$('input[type="file"]').attr("disabled", "disabled");
            $("#edit").show();
            $("#editor_holder").append("<br><div>* The files will be the same than previous ones unless you change them and attach new ones.</div>");
          });
        } else {
          $("#submit").show();
        }


        //hide the spinner when are all working propertly
        $("#loading-datatable").hide();

        //ON CLICK SUBMIT 
        clickButton();
      });
      
    }).fail(function (jqXHR, textStatus) {
      console.log("ERROR");
    })
  });
});

//when click the button submit
function clickButton() {
  
  $('#submit').on("click",function() {
    console.log(editor.getValue());
    //if there are errors = true, no errors = false
    var errors = validateErr("submit");

    var fichero = $("#input_0");

    //if there are not errors
    if (!errors) {
      //take the value of the editor (the things that are write in inputs and internally)
      var json = JSON.stringify(editor.getValue(),null,2);

      //inserted into db
      insertJSON(json, "submit");
    } 

    $(".errorClass").show();
  });

  $('#edit').on("click",function() {
    console.log(editor.getValue());
    //if there are errors = true, no errors = false
    var errors = validateErr("edit");

    //if there are not errors
    if (!errors) {
      //take the value of the editor (the things that are write in inputs and internally)
      processEdit["data"] = editor.getValue();
      var json = JSON.stringify(processEdit,null,2);

      //inserted into db
      insertJSON(json, "edit");
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

function insertJSON(processJSONForm, buttonAction) {

  $.ajax({
    type: 'POST',
    url: urlJSON,
    data: {'action': 'setProcess', 'processForm': processJSONForm, 'buttonAction': buttonAction, 'filePaths': filePaths}
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

//function to validate the errors
function validateErr(clickButton) {
  var errors = editor.validate();
  var inputArray = [];
  var countFiles = 0;

  if(errors.length != 0) {
    console.log("ERRORS: ");
    console.log(errors);
    fileError = 0;
    //if there are errors show the errors on click submit

    $(".form-control-file").css({"color": "#333"});

    for(var i = 0; i < $('input[type="file"]').length; i++) {
      var path = $("#input_"+i).parent().parent().attr("data-schemapath");
      inputArray.push(path);

      for(let j = 0; j < errors.length; j++) {
        if (errors[j]["path"] == path) {
          $("#input_"+i).css({"color": "red"});
        }
      }
    }

    if (clickButton == "edit") {
      for (let i = 0; i < errors.length; i++) {
        for (let j = 0; j < inputArray.length; j++) {
          if (errors[i]["path"] == inputArray[j]) {
            countFiles++;
          }
        }
      }
    }

    if (countFiles == inputArray.length) {
      return false;
    }

    editor.options.show_errors = "always";
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