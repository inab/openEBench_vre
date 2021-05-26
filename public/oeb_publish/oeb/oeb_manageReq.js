var table2;
$(document).ready(function() {
    getRoles().done(function(r) {
        var roles = JSON.parse(r)

        createTableRegisters();
        createTableApprover();
        $("#loading-datatable").hide();
        $(function () {   
            $('[data-toggle="popover"]').popover() 
        });
    })
})

/********************************FUNCTIONS****************************** */
function createTableRegisters(){
    table2 = $('#tableAllFiles').DataTable( {
        "autoWidth": false,
        "ajax": {
            url: 'applib/oeb_publishAPI.php?action=getSubmitRegisters',
            dataSrc: ''
        },
        "bFilter": false, 
        "bPaginate": false,
        //"bInfo": false,
        "bLengthChange": false,
        "bAutoWidth": true,
        "columns" : [
            { "data" : "id" }, //0
            { "data" : "files" }, //1
            { "data" : "requester_name" }, //2
            { "data" : "status" }, //3
            { "data" : "oeb_id"}, //4
            { "data" : "history_actions" }, //5
            { "data" : null} //6

        ],
        'columnDefs': [
            {
                "targets": 0,
                "title": '<th>Request id </th>'
                
            },
            {
                "targets": 1,
                "title": '<th>File Name </th>',
                render: function ( data, type, row ) {
                    result = "<ul>";
                    for (let index = 0; index < data.length; index++) {
                        result += "<li><a href='"+data[index]['nc_url']+"'target='_blank'>"+ data[index]['name']+"</a></li>";   
                        
                    }
                    result += "</ul>";
                    return result
                    //return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
                }
                
            },
            {
                "targets": 2,
                "title": '<th>Requester</th>',
                "className": "dt-center"
                
            },
            {
                "targets": 3,
                "title": '<th>Status </th>' ,
                "className": "dt-center",
                render: function ( data, type, row ) {
                    if (data == "error") {
                        return '<a href="javascript:viewLog(\''+row['id']+'\');">'+data+"</a>"
                    }else return data;
                }
            },
            {
                "targets": 4,
                "title": '<th>OEB dataset id </th>',
                "className": "dt-center",
                render: function ( data, type, row ) {
                    if (data == null) {
                        return "-"
                    }else return data;
                }

            },
            {
                "targets": 5,
                "title": '<th>Timestamp request </th>',
                render: function ( data, type, row ) {
                    return data[0]["timestamp"] ;
                }
            },
            {
                "targets": 6,
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
                
            },

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
        //"bInfo": false,
        "bLengthChange": false,
        "bAutoWidth": true,
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
                "targets": 1,
                "title": '<th>File Name </th>',
                render: function ( data, type, row ) {
                    result = "<ul>";
                    for (let index = 0; index < data.length; index++) {
                        result += "<li><a href='"+data[index]['nc_url']+"'target='_blank'>"+ data[index]['name']+"</a></li>";   
                        
                    }
                    result += "</ul>";
                    return result
                    //return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
                }
                
            },
            {
                "targets": 3,
                render: function ( data, type, row ) {
                    if (data == "error") {
                        return '<a href="javascript:viewLog(\''+row['id']+'\');">'+data+"</a>"
                    }else return data;
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
    
    $("#modalTitle").text("");
    $("#file").text("");
    
    $("#modalTitle").text("Are you sure you want to "+action+" that request?");
    $("#file").text(id);
    $("#actionDialog").modal('show'); 

    $("#acceptModal").click(function (){
        $("#actionDialog").modal('hide');
        $("#loading-datatable").show();
        $("#pendingReq").hide();
        $.ajax({
            type: "POST",
            url: baseURL + "/applib/oeb_publishAPI.php?action=proceedReq",
            data: "actionReq=" + action+"&reqId="+id
        }).done(function(data) {
            //no errors
            if(data["code"]=="200"){
                $("#loading-datatable").hide();
                $("#myError").removeClass("alert alert-danger");
				$("#myError").addClass("alert alert-info");
                table2.ajax.reload();
                $("#files").show();
                $("#myError").append("Data successfully "+action);
				$("#myError").show()

            } else {
                console.log("else");
                $("#loading-datatable").hide();
                table2.ajax.reload();
                $("#files").show();
				$("#myError").removeClass("alert alert-info");
				$("#myError").addClass("alert alert-danger");
				$("#myError").text(data["message"]);
				$("#myError").show();

            }
        //more errors
		}). fail(function(data) {
            $("#loading-datatable").hide();
            table2.ajax.reload();
            $("#files").show();
            $("#myError").removeClass("alert alert-info");
			$("#myError").addClass("alert alert-danger");
			$("#myError").append(data["responseJSON"]["message"]);
            $("#myError").append("</br>Please, try it later or report this message <a href='mailto:openebench-support@bsc.es'>openebench-support@bsc.es</a>. Check full report below, at your request's table.");
			$("#myError").show();
        });
    })
}
function showResultsPage(executionFolder, tool){
    location.href = 'tools/' + tool + '/output.php?execution=' + executionFolder;
}

function viewLog(reqId) {
    console.log("entra");
    
    $.ajax({
        type: "POST",
        url: baseURL + "/applib/oeb_publishAPI.php?action=getLog",
        data: "reqId=" + reqId,
        success: function(data) {
            $("#modalLog").modal();
            $("#modalContent").html("<pre>"+data+"</pre>")

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

