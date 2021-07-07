var table1;
var table2;

$(document).ready(function() {
    getRoles().done(function(r) {
        var roles = JSON.parse(r)

        createTableRegisters();
        createTableApprover();
        $("#loading-datatable").hide();
        //permisions depending on the role
        console.log(roles)
        if (roles['roles'].length == 0) {
            $("#beSelector").attr('disabled','disabled');
            $("#warning-notAllowed").show();
        }

    })
})

/********************************FUNCTIONS****************************** */
function createTableRegisters(){
    table1 = $('#tableAllFiles').DataTable( {
        "ajax": {
            url: 'applib/oeb_publishAPI.php?action=getSubmitRegisters',
            dataSrc: ''
        }, 
        "bPaginate": false,
        responsive: true,
        "bLengthChange": false,     
        rowCallback: function( row, data, index ) {
            if (window.location.href.indexOf(data['id']) > -1){
                $(row).css('background-color', '#fcfce0');
            }
        },
        "columns" : [
            { "data" : "id" }, //0
            { "data" : "benchmarking_event"},//1
            { "data" : "tool"},//2
            { "data" : "files" }, //3
            { "data" : "status" }, //4
            { "data" : "oeb_id"}, //5
            { "data" : "history_actions" }, //6
            { "data" : null} //7

        ],
        'columnDefs': [
            {
                "targets": 0,
                "title": '<th>Request id </th>',
                width: '10px',
                
                render: function ( data, type, row ) {
                    return  '<a id="'+data+'"></a>'+data+' <a id ="example" data-toggle="popover" data-trigger="hover" \
                    title="Title" container="body" data-content="And here"><i class="fa fa-info-circle"></i></a>'
                }
                
            },
            {
                "targets": 1,
                "title": '<th>Benchmarking event </th>',
                render: function ( data, type, row ) {
                    return data['be_name'];
                }
            },
            {
                "targets": 2,
                "title": '<th>Participant Tool </th>',
                render: function ( data, type, row ) {
                    return data['tool_name'];
                }
            },
            {
                "targets": 3,
                "title": '<th>File Name </th>',
                render: function ( data, type, row ) {
                    result = "<ul style ='padding-left: 4%;'>";
                    for (let index = 0; index < data.length; index++) {
                        result += "<li><a href='"+data[index]['nc_url']+"'target='_blank'>"+ data[index]['name']+"</a></li>";   
                        
                    }
                    result += "</ul>";
                    return result
                }
                
            },
            {
                "targets": 4,
                "title": '<th>Status </th>' ,
                "className": "dt-center",
                render: function ( data, type, row ) {
                    if (data == "error") {
                        return '<span class="badge badge-danger"><b>'+data+'</b></span><br><a href="javascript:viewLog(\''+row['id']+'\');">View Log</a>'
                    }else if (data == 'published'){
                        return '<span class="badge badge-success"><b>'+data+'</b></span>';
                    }else if (data == 'denied'){
                        return '<span class="badge badge-warning"><b>'+data+'</b></span>';
                    }else if (data == 'cancelled'){
                        return '<span class="badge badge-secondary"><b>'+data+'</b></span>';
                    }else return '<span class="badge badge-info"><b>'+data+'</b></span>';
                }
            },
            {
                "targets": 5,
                "title": '<th>OEB dataset id </th>',
                "className": "dt-center",
                render: function ( data, type, row ) {
                    if (data == null) {
                        return "-"
                    }else return '<a href="https://dev-openebench.bsc.es/scientific/'+row['community']+'" target="_blank">'+data+'</a>';
                }

            },
            {
                "targets": 6,
                "title": '<th>Creation date</th>',
                className: "hide_column",
                render: function ( data, type, row ) {
                    return convertTimestamp(data[0]['timestamp']['$date']['$numberLong']);
                }
            },
            {
                "targets": 7,
                "title": '<th>Actions </th>',
                render: function ( data, type, row ) {
                    result = "";
                    if (row['status'] == 'pending approval') {
                        result += '<div class="btn-group" style="float:left; position:absolute;">\
                        <button class="btn btn-xs blue-madison dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
                            <i class="fa fa-cogs"></i>\
                            <i class="fa fa-angle-down"></i>\
                        </button>\
                        <ul class="dropdown-menu pull-right" role="menu">\
                            <li><a href="javascript:actionTable2(\''+row['id']+'\',\'cancel\');"><i class="fa fa-times-circle" style = "color:#c0c0c0;"></i> Cancel request</a></li>\
                        </ul>\
                        </div>';
                    }
                     
                    result += '<div class="btn-group" style="float:left; position:absolute;margin-left:38px;">\
                            <button class="btn btn-xs purple-intense dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
						        <i class="fa fa-eye"></i>\
						        <i class="fa fa-angle-down"></i>\
		                    </button>\
                            <ul class="dropdown-menu pull-right" role="menu">\
						        <li><a href="javascript:showResultsPage(\'OpEBUSER5e301d61da6f8_5e5fc0fab53483.99018780\',\'QFO_6\');"><i class="fa fa-file-text"></i> View Results</a></li>\
	                        </ul>\
                        </div>'
                   return result;
                }
                
            }

        ],
        'order': [[1, 'asc']]
    });
}

function createTableApprover(){
    table2 = $('#tableApprovals').DataTable( {
        "autoWidth": false,
        "ajax": {
            url: 'applib/oeb_publishAPI.php?action=getApprovalRequest',
            dataSrc: ''
        },
        "bFilter": false, 
        "bPaginate": false,
        "bLengthChange": false,
        "bAutoWidth": true,
        rowCallback: function( row, data, index ) {
            if (window.location.href.indexOf(data['id']) > -1){
                $(row).css('background-color', '#fcfce0');
            }
        },
        drawCallback: function() {
            $('[data-toggle="popover"]').popover({
                container: 'body'
            })
            
        },  
        "columns" : [
            { "data" : "id", "title": '<th>Request id </th>' }, //0
            { "data" : "files", "title": '<th>File name </th>' }, //1
            { "data" : "requester_name", "title": '<th>Requester</th>' }, //2
            { "data" : "status",  "title": '<th>Status</th>' }, //3
            { "data" : "oeb_id", "title": '<th>OEB dataset id</th>'}, //4
            { "data" : null, "title": '<th>Actions</th>' }, //5

        ],
        'columnDefs': [
            {
                "targets": 0,
                render: function ( data, type, row ) {
                    return '<a id="'+data+'"></a><div class="ellipsis">'+data+'</div><a id ="example"  data-html="true" data-toggle="popover" data-placement="top" data-trigger="click" \
                    title="'+data+'" data-content="<b>Request created</b>: '+convertTimestamp(row['history_actions'][0]['timestamp']['$date']['$numberLong'])+'"><i class="fa fa-info-circle"></i></a>'
                }
            },
            {
                "targets": 1,
                "title": '<th>File Name </th>',
                render: function ( data, type, row ) {
                    result = "<ul style='padding-left: 4%;'>";
                    for (let index = 0; index < data.length; index++) {
                        result += "<li><a href='"+data[index]['nc_url']+"'target='_blank'>"+ data[index]['name']+"</a></li>";   
                        
                    }
                    result += "</ul>";
                    return result
                }
                
            },
            {
                "targets": 3,
                "className": "dt-center",
                render: function ( data, type, row ) {
                    if (data == "error") {
                        return '<span class="badge badge-danger"><b>'+data+'</b></span></br><a href="javascript:viewLog(\''+row['id']+'\');">View log</a>'
                    }else if (data == 'published'){
                        return '<span class="badge badge-success"><b>'+data+'</b></span>';
                    }else if (data == 'denied'){
                        return '<span class="badge badge-warning"><b>'+data+'</b></span>';
                    }else if (data == 'cancelled'){
                        return '<span class="badge badge-secondary"><b>'+data+'</b></span>';
                    }else return '<span class="badge badge-info"><b>'+data+'</b></span>';
                }
            },
            {
                "targets": 4,
                "title": '<th>OEB dataset id </th>',
                "className": "dt-center",
                render: function ( data, type, row ) {
                    if (data == null) {
                        return "-"
                    }else return '<a href="https://dev-openebench.bsc.es/scientific/'+row['community']+'" target="_blank">'+data+'</a>';
                }

            },
            {
                "targets": 5,
                "title": '<th>Actions </th>',
                render: function ( data, type, row ) {
                    result = "";
                    if (row['status'] == 'pending approval') {
                        result += '<div class="btn-group" style="float:left; position:absolute;">\
                        <button class="btn btn-xs blue-madison dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
                            <i class="fa fa-cogs"></i>\
                            <i class="fa fa-angle-down"></i>\
                        </button>\
                        <ul class="dropdown-menu pull-right" role="menu">\
                            <li><a href="javascript:actionTable2(\''+row['id']+'\',\'approve\');"><i class="fa fa-check-circle" style = "color:#74b72e;"></i> Approve request</a></li>\
                            <li><a href="javascript:actionTable2(\''+row['id']+'\',\'deny\');"><i class="fa fa-times-circle" style = "color:#E00909;"></i> Deny request</a></li>\
                        </ul>\
                        </div>';
                    }
                     
                    result += '<div class="btn-group" style="float:left; position:absolute;margin-left:38px;">\
                            <button class="btn btn-xs purple-intense dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
						        <i class="fa fa-eye"></i>\
						        <i class="fa fa-angle-down"></i>\
		                    </button>\
                            <ul class="dropdown-menu pull-right" role="menu">\
						        <li><a href="javascript:showResultsPage(\'OpEBUSER5e301d61da6f8_5e5fc0fab53483.99018780\',\'QFO_6\');"><i class="fa fa-file-text"></i> View Results</a></li>\
	                        </ul>\
                        </div>'
                   return result;
                }
                
            }

        ],
        'order': [[1, 'asc']]

    });
        
}



function actionTable2(id, action) {
    var myForm = document.getElementById('confirm-form');
    myForm.actionReq.value = action;
    myForm.reqId.value = id;
    $("#modalTitle").text("");
    $("#file").text("");
    $("#inputDenyReason").hide();
    if (action == "deny"){
        $("#inputDenyReason").show();
    }
    
    $("#modalTitle").text("Are you sure you want to "+action+" that request?");
    $("#file").text(id);
    $("#actionDialog").modal('show'); 
}

$('#confirm-form').on('submit', function (e) {

    e.preventDefault();
    $("#actionDialog").modal('hide');
    $("#loading-datatable").show();
    $("#pendingReq").hide();

    $.ajax({
        type: "POST",
        url: baseURL + "/applib/oeb_publishAPI.php?action=proceedReq",
        data: $('#confirm-form').serialize()
    }).done(function(data) {
        //no errors
        $("#myError").removeClass("alert alert-danger");
        $("#myError").addClass("alert alert-info ");
        $("#myError").append(data['message']);
        $("#loading-datatable").hide();
        table1.ajax.reload();
        table2.ajax.reload();
        $("#files").show();
        $("#pendingReq").show();
        $("#myError").show();
        // errors
	}). fail(function(data) {
        $("#loading-datatable").hide();
        table1.ajax.reload();
        table2.ajax.reload();
        $("#files").show();
        $("#pendingReq").show();
        $("#myError").removeClass("alert alert-info");
		$("#myError").addClass("alert alert-danger");
		$("#myError").append(data["responseJSON"]["message"]);
        $("#myError").append("</br>Please, try it later or report this message <a href='mailto:openebench-support@bsc.es'>openebench-support@bsc.es</a>. Check full report below, at your request's table.");
		$("#myError").show();
    });

});
    
function showResultsPage(executionFolder, tool){
    location.href = 'tools/' + tool + '/output.php?execution=' + executionFolder;
}

function viewLog(reqId) {
    
    $.ajax({
        type: "POST",
        url: baseURL + "/applib/oeb_publishAPI.php?action=getLog",
        data: "reqId=" + reqId,
        success: function(data) {
            var data = JSON.parse(data);
            $("#modalLog").modal();
            var log ="";
            log += "=========== Request petition =======</br>"
            for (let i = 0; i < data.length; i++) {
                if (i == 1){
                    log += "=========== Do petition action =======</br>"
                    log += data[i]['log']['stderr']+"</br>";
                }else {
                    for (let j = 0; j < data[i]['log'].length; j++) {
                        log += data[i]['log'][j]+"</br>";
                    }
                } 
            }
            $("#modalContent").html("<pre>"+log+"</pre>")

        }
    });
   

}


//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: 'applib/oeb_publishAPI.php?action=getRole'
    })
}

/**
 * Converts unix time in human format
 * @param {*} timestamp 
 * @return the time in human format
 */
 function convertTimestamp(timestamp) {
    var d = new Date(timestamp * 1),	// Convert the passed timestamp to milliseconds
          yyyy = d.getFullYear(),
          mm = ('0' + (d.getMonth() + 1)).slice(-2),	// Months are zero based. Add leading 0.
          dd = ('0' + d.getDate()).slice(-2),			// Add leading 0.
          hh = d.getHours(),
          h = hh,
          min = ('0' + d.getMinutes()).slice(-2),		// Add leading 0.
          ampm = 'AM',
          time;
              
      if (hh > 12) {
          h = hh - 12;
          ampm = 'PM';
      } else if (hh === 12) {
          h = 12;
          ampm = 'PM';
      } else if (hh == 0) {
          h = 12;
      }
      
      // ie: 2013-02-18, 8:35 AM	
      time = yyyy + '-' + mm + '-' + dd + ', ' + h + ':' + min + ' ' + ampm;
          
      return time;
  }

