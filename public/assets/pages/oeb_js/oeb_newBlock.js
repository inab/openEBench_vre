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
var urlJSON = "applib/oeb_blocksAPI.php";
var blockEdit;
var filePaths = [];

$(document).ready(function() {
  var currentURL = window.location["href"];
  var url = new URL(currentURL);
  var typeBlock = url.searchParams.get("typeBlock");

  //typeBlock ==> for type

  //get the JSON Schema
  $.getJSON(oeb_block_schema, function(data) {
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
        $('a[href="#Nextflow-files"]').text("STEP 2: Nextflow Files");

        //get the argument/action
        var currentURL = window.location["href"];
        var url = new URL(currentURL);
        var action = url.searchParams.get("action");
        var idBlock = url.searchParams.get("id");

        if (action == "editBlock") {
          $(".page-title").text("Edit block");
          $("#spanCreate").text("Edit block");
          $.ajax({
            type: 'POST',
            url: urlJSON,
            data: {'action': 'getBlock', 'id': idBlock}
          }).done(function(data) {
            blockEdit = data;
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
        clickButton(typeBlock);
      });
      
    }).fail(function (jqXHR, textStatus) {
      console.log("ERROR");
    })
  });
});

//when click the button submit
function clickButton(typeBlock) {
  
  $('#submit').on("click",function() {
    console.log(editor.getValue());
    //if there are errors = true, no errors = false
    var errors = validateErr();

    //if there are not errors
    if (!errors) {
      //take the value of the editor (the things that are write in inputs and internally)
      var json = JSON.stringify(editor.getValue(),null,2);

      //inserted into db
      insertJSON(json, typeBlock, "submit");
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

function insertJSON(blockJSONForm, typeBlock, buttonAction) {

  $.ajax({
    type: 'POST',
    url: urlJSON,
    data: {'action': 'setBlock', 'blockForm': blockJSONForm, 'typeBlock': typeBlock, 'buttonAction': buttonAction }
  }).done(function(data) {
    if(data['code'] == 200) {
      window.location.href = baseURL + "oeb_management/oeb_block/oeb_blocks.php";
      
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
    var errorMessage = "";
    if(typeof(error['message']) == "object") {
      error['message'].forEach(element => errorMessage = errorMessage + element + "<br>");
      $(".errorClass").html(errorMessage);
      $(".errorClass").removeClass(" alert alert-info");
      $(".errorClass").addClass(" alert alert-danger");
    } else {
      $(".errorClass").text(error['message']);
      $(".errorClass").removeClass(" alert alert-info");
      $(".errorClass").addClass(" alert alert-danger");
    }
  });
}

//function to validate the errors
function validateErr() {
  var errors = editor.validate();

  if(errors.length != 0) {
    console.log("ERRORS: ");
    console.log(errors);
    fileError = 0;

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