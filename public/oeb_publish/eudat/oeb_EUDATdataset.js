const CONTROLLER = 'applib/oeb_publishAPI.php'

$(document).ready(function() {
  var valid = false;
  var currentURL = window.location["href"];

  //get schema
  $.getJSON(oeb_eudat_schema, function(data) {
      schema = data;
    }).done(function() {

      //files
      fn = $("#files").val();
     

      //create jsonEditor obj
      editor = new JSONEditor(document.getElementById("editor_holder"),{
        theme: 'bootstrap4',
        schema: schema,
  
        //do not have collapse, edit and properties options in the editor (are specific things of the web-based tool - JSONEditor)
        disable_collapse: true,
        disable_edit_json: true,
        disable_properties: true
      });

      //AJAXs petition to get info
      //file info
      $.ajax({
        type: 'POST',
        url: CONTROLLER + "?action=getFileInfo",
        data: {"files" : fn}
      }).done(function(data) {
        var fileinfo = JSON.parse(data);

        //user info
        $.ajax({
          type: 'POST',
          url: CONTROLLER + "?action=getUserInfo",
          data: currentURL
        }).done(function(data) {
          
          var userinfo = JSON.parse(data);

          //set values internally in the form
          editor.getEditor("root.titles.0.title").setValue(fileinfo["path"].split("/").pop());
          editor.getEditor("root.contact_email").setValue(userinfo["Email"]);
          $("#Creators .json-editor-btn-add").trigger("click");
          editor.getEditor("root.creators.0.creator_name").setValue(userinfo["Name"]+" "+userinfo["Surname"]);
          
          
          //OEB data
          editor.getEditor("root.community_specific.176df0a6-8c8e-424d-a5c1-34b1d84a580c.oeb_id").setValue(fileinfo['OEB_dataset_id']);
          editor.getEditor("root.community_specific.176df0a6-8c8e-424d-a5c1-34b1d84a580c.oeb_community").setValue(userinfo["oeb_community"]);
          
          //data type
          if (fileinfo["data_type"] == "OEB_data_model"){
            editor.getEditor("root.community_specific.176df0a6-8c8e-424d-a5c1-34b1d84a580c.oeb_type").setValue("participant_assessments");
          } else if (fileinfo["data_type"] == "participant"){
            editor.getEditor("root.community_specific.176df0a6-8c8e-424d-a5c1-34b1d84a580c.oeb_type").setValue("participant");
          }
          editor.getEditor("root.community_specific.176df0a6-8c8e-424d-a5c1-34b1d84a580c.oeb_dataset_version").setValue("1.0");

          //css form
          $("[id*=contact_email]").next().next().append('<b> Warning: data is going to be public!</b>');
          $('label[class="required"]').append('<span style="color:red;"> *</span>')
          //hide labels
          $('.card-header li a[href="#Resource-Type"]').parent().hide();
          $('.card-header li a[href="#Disciplines"]').parent().hide();
          $('.card-header li a[href="#Keywords"]').parent().hide();
          $('.card-header li a[href="#Languages"]').parent().hide();
          $('.card-header li a[href="#community_specific"]').parent().hide();

          //hide items
          $('#Titles .card-body h3 label').hide();
          $('#Descriptions .card-body h3 label').hide();
          $('#Creators .card-body h3 label').hide();
          $('div[data-schemapath="root.disciplines"] > div label').hide();
           
          //populate lisence uri
          editor.watch('root.license.license',function() {
            if (editor.getEditor("root.license.license").getValue() == "Apache License 2.0") {
              editor.getEditor("root.license.license_uri").setValue("http://www.apache.org/licenses/LICENSE-2.0")
            }else if (editor.getEditor("root.license.license").getValue() == "GNU Public License v3.0") {
              editor.getEditor("root.license.license_uri").setValue("https://www.gnu.org/licenses/gpl-3.0.html")
            } else editor.getEditor("root.license.license_uri").setValue("http://creativecommons.org/licenses/by/4.0/")
          });
          

          //hide the spinner when are all working propertly
          $("#loading-datatable").hide();
          
          editor.on('change',function() {

            //custom validation fields
            JSONEditor.defaults.custom_validators.push(function(schema, value, path) {
              var errors = [];
             
              if(schema.format==="date") {
                  // Errors must be an object with `path`, `property`, and `message`
                  errors.push({
                    path: path,
                    property: 'format',
                    message: 'Dates must be in the format "YYYY-MM-DD"'
                  });

              }
              return errors;
            });

            // Get an array of errors from the validator
            var errors = editor.validate();
            var indicator = document.getElementById('valid_indicator');
            
            // Not valid
            if(errors.length || $("input[id*=title]").val()==""|| $("input[id*=description]").val()=="" || $("input[id*=creator_name]").val()=="") {
              editor.options.show_errors = "always";
              console.log(errors)
              $(".errorClass").text("There are errors in some fields of the form.");
              $(".errorClass").removeClass(" alert alert-info");
              $(".errorClass").addClass(" alert alert-danger");
              document.getElementById('submit').disabled = true; 
              $(".errorClass").show();
            }
            // Valid
            else {
              $(".errorClass").hide();
              document.getElementById('submit').disabled = false; 
              valid = true;
            }
            return valid;
          });
          

          

        });
      });
  });

  $('#submit').on("click",function() {
    if(valid){
      var json = JSON.stringify(editor.getValue(),null,4);
      $("#myModal").modal();
      $("#summaryContent").html("<pre>"+json+"</pre>")
      
      //when submit on modal is clicked
      $('#submitModal').on("click",function() {
        fn = $("#files").val();
        $("#closeModal").trigger("click");
        $("#formMetadata").hide();
        $("#loading-datatable").show();

        $.ajax({
          type: 'POST',
          url: "applib/oeb_publishAPI.php?action=publish",
          data: {"metadata" : json, "fileId": fn}
        }).done (function(data) {
            //no errors
              $("#loading-datatable").hide();
              $("#result").removeClass("alert alert-danger");
              $("#result").addClass("alert alert-info");
              $("#step3").addClass("active");
              $("#result").append("<h4>Data successfully published!</h4>\
                <p style=\"font-size:1.1em;\">\
                Registered D.O.I.: <b>"+data["message"]+"</b><br/>"+timeStamp()+
                "</p><br><a href=\""+b2share_host+"records/"+data["message"]+
                "\" target=\"_blank\" class=\"btn green\"> EUDAT record</a>");
              $("#result").show();
              //button back display
              $("#back").show();

        //more errors
		    }). fail(function(data) {
            $("#loading-datatable").hide();
            $("#result").removeClass("alert alert-info");
            $("#result").addClass("alert alert-danger");
            $("#result").append(data["responseJSON"]["message"]);
            $("#result").append("</br>Please, try it later or report this message \
              <a href='mailto:openebench-support@bsc.es'>openebench-support@bsc.es</a>.");
            $("#result").show();
            //button back display
            $("#back").show();
        }); 
      })
    }
  });

  $('#back').on("click",function() {
    location.href = 'vre/oeb_publish/eudat/';
  });
  
});

//function to validate the errors
function validateErr() {
  var errors = editor.validate();

  if(errors.length != 0) {
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



/**
 * timeStamp function. Gets the current day and time with readeable format
 * @listens none
 * @param none
 * @return {string} - the timestamp
 */
function timeStamp() {
  var currentdate = new Date(); 
  return datetime = "Time: " + currentdate.getDate() + "/"
              + (currentdate.getMonth()+1)  + "/" 
              + currentdate.getFullYear() + " @ "  
              + currentdate.getHours() + ":"  
              + currentdate.getMinutes() + ":" 
              + currentdate.getSeconds();
};








