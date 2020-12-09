$(document).ready(function() {
  var currentURL = window.location["href"];

  //get schema
  $.getJSON(oeb_eudat_schema, function(data) {
      schema = data;
    }).done(function() {
      //user
      var getUserInfo = "applib/publishFormAPI.php?action=getUserInfo";

      //files
      fn = $("#files").val();
      var getFileInfo = "applib/publishFormAPI.php?action=getFileInfo";

      var param = {
        "files" : fn 
      };

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
        url: getFileInfo,
        data: param
      }).done(function(data) {
        var fileinfo = JSON.parse(data);
        console.log(fileinfo);

        //user info
        $.ajax({
          type: 'POST',
          url: getUserInfo,
          data: currentURL
        }).done(function(data) {
          
          var userinfo = JSON.parse(data);
          console.log(userinfo);
          
          //set the values internally in the form
          //creators TODO
          editor.getEditor("root.titles.0.title").setValue(fileinfo["path"].split("/").pop());
          editor.getEditor("root.contact_email").setValue(userinfo["Email"]);
          editor.getEditor("root.community_specific.12ed8b46-1e2f-4fd3-9963-29c112b92da1.oeb_id").setValue("OEBD555AZEAZEA");
          editor.getEditor("root.community_specific.12ed8b46-1e2f-4fd3-9963-29c112b92da1.oeb_community").setValue(userinfo["oeb_community"]);
          editor.getEditor("root.community_specific.12ed8b46-1e2f-4fd3-9963-29c112b92da1.type").setValue(fileinfo["data_type"]);
          

          //hide the spinner when are all working propertly
          $("#loading-datatable").hide();
          console.log(editor.getValue());

        });

      });

      
  });
  $('#submit').on("click",function() {
    var json = JSON.stringify(editor.getValue(),null,2);
    console.log(json);
    console.log(typeof(json))
    var obj = JSON.parse(json); 
    console.log(obj);
    console.log(typeof(obj))

    var parama = {
      "metadata" : obj 
    };

    $.ajax({
      type: 'POST',
      url: "applib/publishFormAPI.php?action=publish",
      data: parama
    }).done(function(data) {
      console.log(data);

    });
    
  });
});


  





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
