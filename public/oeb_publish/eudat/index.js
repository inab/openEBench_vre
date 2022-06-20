var arrayOfFiles = [];
var table;
const CONTROLLER = 'applib/oeb_publishAPI.php'

$(document).ready(function () {
  if (arrayOfFiles.length === 0) {
    $("#message").text("No file selected");
    $("#btn-run-files").attr("disabled","disabled");

  }
    //build table
    var types = ['participant'];
    table = $("#filesTable").DataTable({
      ajax: {
        url: CONTROLLER + "?action=getAllFiles",
        data: ({
          type: JSON.stringify(types),
        }),
        "dataSrc": ""
      },
      "bPaginate": false,
      "bFilter": true, 
      rowCallback: function( row, data, index ) {
        console.log(data)
        if(data['OEB_dataset_id'] == null){
          $(row).css('color', 'silver');
        }
      },
      
      initComplete: function () {
            	
        this.api().columns([2]).every( function () {
            var column = this;
            var select = $('<select class="form-control" style="width: 30%;">\
              <option value="">All dataset types </option></select>')
                .appendTo( $('#selectType').empty() )
                .on( 'change', function () {
                    var val = $.fn.dataTable.util.escapeRegex(
                        $(this).val()
                    );
                    column
                        .search( val ? '^'+val+'$' : '', true, false )
                        .draw();
                } );

            column.cells('', column[0]).render('display').sort().unique().each( function ( d, j ){

                select.append( '<option value="'+d+'">'+d+'</option>' )
            } );
        } );
    },
      columns: [
        { data: null }, //0
        { data: "path" }, //1
        { data: "data_type" }, //2
        { data: "OEB_dataset_id" }, //3
        { data: "oeb_eudatDOI" }, //4
      ],
      drawCallback: function() {
        $('[data-toggle="popover"]').popover({
            container: 'body'
        })
      },
      columnDefs: [
        {
        targets: 0,
        searchable: false,
        orderable: false,
        render: function ( data, type, row ) {
          if (row['OEB_dataset_id']){
            return '<input type="radio" name="file" value="' +row['_id'] +'">'
          } else return '<input disabled type="radio" name="file" value="' +row['_id'] +'">'
          
        },
      },
      {
        targets: 3,
        render: function (data, type, row) {
          if (data != null) {
            return (
              '<a href="'+server+'/scientific/" target="_blank">' + data + "</a>"
            );
          } else
            return '-';
        },
      },
      {
        targets: 1,
        render: function (data, type, row) {
          if (row['OEB_dataset_id']){
            return "<b>" + data.split("/").reverse()[1] +"</b>/" + data.split("/").pop()
          } else {
            return "<b>" + data.split("/").reverse()[1] +"</b>/" + data.split("/")
                .pop()+"<a data-html='true' data-toggle='popover' data-placement='top' data-trigger='click' \
                data-content='<b>Dataset is not published in OpenEBench. </b>\
                <a href =\"oeb_publish/oeb/oeb_newReq.php\"> Publish to OEB first</a>'\
                <i class='fa fa-exclamation-triangle' style='color: #F4D03F'></i></a></br>"
          }
        },
      },
      {
        targets: 4,
        render: function (data, type, row) {
          if (data != null) {
            return (
              '<a href="'+b2share_host+'records/' + data.split(".").splice(-1).toString() + '" target="_blank">' + data + "</a>"
            );
          } else return "-";
        },
      },
    ],
    order: [[3, "desc"]],
  });

  

  // Handle click on checkbox to set state of "Select all" control
  $("#filesTable tbody").on("change", 'input[type="checkbox"]', function () {
    // If checkbox is not checked
    if (!this.checked) {
      var el = $("#example-select-all").get(0);
      // If "Select all" control is checked and has 'indeterminate' property
      if (el && el.checked && "indeterminate" in el) {
        // Set visual state of "Select all" control
        // as 'indeterminate'
        el.indeterminate = true;
      }
    }
  });
  $("#filesTable").on("click", function () {
    arrayOfFiles = []
    $("#message").text("");
    var ele = document.getElementsByName('file');
    for(i = 0; i < ele.length; i++) {
      if(ele[i].checked){
        arrayOfFiles.push(ele[i].value)
        console.log(ele[i].value)
      }
    }
    if (arrayOfFiles.length === 0) {
      $("#message").text("No file selected");
      $("#btn-run-files").attr("disabled","disabled");

    } else $("#btn-run-files").prop('disabled', false);
  });

  $("#btn-run-files").on("click", function () {
    if (arrayOfFiles.length != 0) {
        location.href = "oeb_publish/eudat/oeb_EUDATdataset.php?files=" + arrayOfFiles;
    }
  });         
  
  $.ajax({
    type: "POST",
    url: CONTROLLER + "?action=getRole",
    }).done(function(data) {
      result = JSON.parse(data);
      console.log(result)
      if (result['roles'].length === 0){
        $("#myError").addClass("alert alert-warning");
        $("#myError").append("<h4><b>You are not allowed </b></h4>");
        $("#myError").append("<p>You don't have the proper permissions for \
            publishing benchmarking datasets to OpenEBench. Only owners, managers \
            and challenges contributors are allowed.</p>")
        $("#myError").append('You can request that permision sending a ticket to \
            helpdesk: <a href="/vre/helpdesk/?sel=roleUpgrade">click here!</a></p>');
        $("#myError").show();
        $("input").attr('disabled','disabled');

      } 
       if (!result.hasOwnProperty("tokenEudat")){
				$("#myError").addClass("alert alert-warning");
        $("#myError").append("<h4><b>No B2share token is set </b></h4>");
        $("#myError").append("<p>You haven't set any token for B2share remote \
            repository. A Eudat token is necessary to upload files in B2share.</p>")
        $("#myError").append('<p>You can set it in your user profile on linked \
            accounts section: <a href="/vre/user/usrProfile.php#tab_1_4#linkedEudat">User profile</a></p>');
        $("#myError").show();
        $("input").attr('disabled','disabled');
      }
  });

});
