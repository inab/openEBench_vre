//var a = document.getElementById("a");
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
var urlJSON = "applib/oeb_blocksAPI.php";
var blockEdit;
var filePaths = [];

$(document).ready(function() {
    //Function for the circles of steps (1, 2 and 3) to change the color when they are select
    colorSteps();

    //insert the blocks in the select of validation
    insertBlocksSelect("validation");

    //insert the blocks in the select of metrics
    insertBlocksSelect("metrics");

    //insert the blocks in the select of consolidation
    insertBlocksSelect("consolidation");

    insertEditor();
    //on click submit
    $('#submit').on("click",function() {
        //initialize the div of errors
        $("#divErrors").text();

        //get the value selected in select of each block
        var nameWorkflow = $.trim($("#nameWorkflow").val());
        var validationBlock = $.trim($("#validationSelect").val());
        var metricsBlock = $.trim($("#metricsSelect").val());
        var consolidationBlock = $.trim($("#consolidationSelect").val());

        //validate is the selects are empty
        if (!validationBlock) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The validation block is empty.");
            $("#divErrors").show();
        } else if (!metricsBlock) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The metric block is empty.");
            $("#divErrors").show();
        } else if (!consolidationBlock) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The consolidation block is empty.");
            $("#divErrors").show();
            //validate if the name is empty
        } else if (!nameWorkflow) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The name is required.");
            $("#divErrors").show();
        } else {
            //validate is the name has strange characters
            if(!nameWorkflow.match(/^[a-zA-Z0-9._]+$/)) {
                $("#divErrors").removeClass(" alert alert-info");
                $("#divErrors").addClass(" alert alert-danger");
                $("#divErrors").text(" The name only admits a-z, A-Z, 0-9, . and _");
                $("#divErrors").show();
            } else {
                //if has no errors, set the workflow into MongoDB
                setWorkflow();
            }
        }
    });
});

//circle colors
function colorSteps() {
    $("#first").on("click", function() {
        $("#secondActive").removeClass(" active");
        $("#thirdActive").removeClass(" active");
    });

    $("#second").on("click", function() {
        $("#thirdActive").removeClass(" active");
        $("#secondActive").addClass(" active");
    });

    $("#third").on("click", function() {
        $("#secondActive").addClass(" active");
        $("#thirdActive").addClass(" active");
    });
}

//create the validation select
function insertBlocksSelect(typeBlock) {

  $.ajax({
        type: 'POST',
        url: urlJSON,
        data: {'action': 'getBlockSelect', 'type': typeBlock}
    }).done(function(data) {
        //type validation
        if (typeBlock == "validation") {
            if (data.length != 0) {
                var sel = $('<select class="form-control" id="validationSelect">').appendTo('#validation');
                for (let x = 0; x < data.length; x++) {
                    if (data[x]["data"]["type"] == "validation") {
                        $(data[x]).each(function() {
                            sel.append($("<option>").attr('value',data[x]["_id"]).text(data[x]["data"]["name"]));
                        });
                    } 
                }
            } else {
                //if are not validation blocks the select is disabled
                var sel = $('<select class="form-control" id="validationSelect" disabled>').appendTo('#validation');
            }
        }

        //type metrics
        if (typeBlock == "metrics") {
            if (data.length != 0) {
                var sel = $('<select class="form-control" id="metricsSelect">').appendTo('#metrics');
                for (let x = 0; x < data.length; x++) {
                    if (data[x]["data"]["type"] == "metrics") {
                        $(data[x]).each(function() {
                            sel.append($("<option>").attr('value',data[x]["_id"]).text(data[x]["data"]["name"]));
                        });
                    } 
                }
            } else {
                //if are not metrics blocks the select is disabled
                var sel = $('<select class="form-control" id="metricsSelect" disabled>').appendTo('#metrics');
            }
        }

        //type consolidation
        if (typeBlock == "consolidation") {
            if (data.length != 0) {
                var sel = $('<select class="form-control" id="consolidationSelect">').appendTo('#consolidation');
                for (let x = 0; x < data.length; x++) {
                    if (data[x]["data"]["type"] == "consolidation") {
                        $(data[x]).each(function() {
                            sel.append($("<option>").attr('value',data[x]["_id"]).text(data[x]["data"]["name"]));
                        });
                    } 
                }
            } else {
                //if are not metrics blocks the select is disabled
                var sel = $('<select class="form-control" id="consolidationSelect" disabled>').appendTo('#consolidation');
            }
        }

    });
}

//function set workflow into MongoDB
function setWorkflow(json) {
    //get the block id selected and the name for the workflow
    var validation = $("#validationSelect").val();
    var metrics = $("#metricsSelect").val();
    var consolidation = $("#consolidationSelect").val();

    //ajax petition with all the necessary information about the workflow
    $.ajax({
        type: 'POST',
        url: urlJSON,
        data: {'action': 'setWorkflow', 'json': json, 'validation': validation, 'metrics': metrics, 'consolidation': consolidation}
    }).done(function(data) {
        //no errors
        if (data["code"] == 200) {
            $("#divErrors").removeClass(" alert alert-danger");
            $("#divErrors").addClass(" alert alert-info");
            $("#divErrors").text("Workflow inserted successfully");
            $("#divErrors").show();
            location.href="oeb_management/oeb_block/oeb_workflows.php";
        //errors
        } else {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(data["message"]);
            $("#divErrors").show(); 
        }
    //more errors
    }).fail(function(data) {
        $("#divErrors").removeClass(" alert alert-info");
        $("#divErrors").addClass(" alert alert-danger");
        $("#divErrors").text(data["responseJSON"]["message"]);
        $("#divErrors").show();
    });
}

function insertEditor() {
    $(".steps").hide();
    $("#loading-datatable").show();
    //get the JSON Schema
  $.getJSON(oeb_workflow_schema, function(data) {
    schema = data;
  }).done(function() {

    //get all the necessary information about the ontologies
    let [pathsArray, ancestorsArray, URLontologiesArray] = getInformation(schema);

    //it has to be the same number of ancestors that ontologies params because for each ontology there are one ancestor. 
    if (URLontologiesArray.length == ancestorsArray.length) {
      for (i = 0; i < URLontologiesArray.length; i++) {
        var url = "applib/oeb_blocksAPI.php?action=getForm&urlOntology=" + URLontologiesArray[i] + "&ancestors=" + ancestorsArray[i];
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
      var urlDefaultValues = "applib/oeb_blocksAPI.php?action=getDefaultValues&owner";
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
        //if there are oeb_community param in the block
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
        $('a[href="#Generic-keywords"]').text("STEP 2: Generic Keywords");
        $('a[href="#Custom-keywords"]').text("STEP 3: Custom Keywords");
        $('a[href="#Input-files-&-arguments"]').text("STEP 4: Input Files");
        $('a[href="#Output-results"]').text("STEP 5: Output Files");
        $("#submit").show();
        //hide the spinner when are all working propertly
        $("#loading-datatable").hide();
        $(".steps").show();

        //ON CLICK SUBMIT 
        clickButton();
      });
      
    }).fail(function (jqXHR, textStatus) {
      console.log("ERROR");
    })
  });
}

//when click the button submit
function clickButton() {
  
    $('#submit').on("click",function() {
      console.log(editor.getValue());
      //if there are errors = true, no errors = false
      var errors = validateErr();

      var validation = $("#validationSelect").val();
      var metrics = $("#metricsSelect").val();
      var consolidation = $("#consolidationSelect").val();
  
      if (!validation) {
        $("#divErrors").removeClass(" alert alert-info");
        $("#divErrors").addClass(" alert alert-danger");
        $("#divErrors").text("Validation select is empty");
        $("#divErrors").show();
      } else if (!metrics) {
        $("#divErrors").removeClass(" alert alert-info");
        $("#divErrors").addClass(" alert alert-danger");
        $("#divErrors").text("Metrics select is empty.");
        $("#divErrors").show();
      } else if (!consolidation) {
        $("#divErrors").removeClass(" alert alert-info");
        $("#divErrors").addClass(" alert alert-danger");
        $("#divErrors").text("Consolidation select is empty.");
        $("#divErrors").show();
      } else {
        $("#divErrors").removeClass(" alert alert-danger");
        $("#divErrors").addClass(" alert alert-info");
        $("#divErrors").text("Selects are correct.");
        $("#divErrors").show();
      }

      //if there are not errors
      if (!errors && validation && metrics && consolidation) {
        //take the value of the editor (the things that are write in inputs and internally)
        var json = JSON.stringify(editor.getValue(),null,2);
        console.log(json);
        //inserted into db
        setWorkflow(json);
      } 
  
      $(".errorClass").show();
    });
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

//function to validate the errors
function validateErr() {
  var errors = editor.validate();

  if(errors.length != 0) {
    console.log("ERRORS: ");
    console.log(errors);

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