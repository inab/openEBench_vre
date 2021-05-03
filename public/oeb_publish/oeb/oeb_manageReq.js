var table2;
$(document).ready(function() {
    getRoles().done(function(r) {
        var roles = JSON.parse(r)

        createTableRegisters();
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
        "columns" : [
            { "data" : "id" }, //0
            { "data" : "files" }, //1
            { "data" : "requester_name" }, //2
            { "data" : "status" }, //3
            { "data" : "history_actions" }, //4
            { "data" : null} //5

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
                "title": '<th>Requester</th>'
                
            },
            {
                "targets": 3,
                "title": '<th>Status </th>' 
            },
            {
                "targets": 4,
                "title": '<th>Timestamp request </th>',
                render: function ( data, type, row ) {
                    return data[0]["timestamp"] ;
                }
            },
            {
                "targets": 5,
                "title": '<th>Actions </th>',
                render: function ( data, type, row ) {
                    
                    if(row['current_status'] == 'approved'){
                        return ""
                    }
                    return '<div class="btn-group" style="float:left; position:absolute;">\
                            <button class="btn btn-xs blue-madison dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
                                <i class="fa fa-cogs"></i>\
                                <i class="fa fa-angle-down"></i>\
                            </button>\
                            <ul class="dropdown-menu pull-right" role="menu">\
                                <li><a href="javascript:actionTable2(\''+row['id']+'\',\'approve\');"><i class="fa fa-check-circle" style = "color:#74b72e;"></i> Approve request</a></li>\
                                <li><a href="javascript:actionTable2(\''+row['id']+'\',\'deny\');"><i class="fa fa-times-circle" style = "color:#E00909;"></i> Deny request</a></li>\
                                <li><a href="javascript:actionTable2(\''+row['id']+'\',\'cancel\');"><i class="fa fa-times-circle" style = "color:#c0c0c0;"></i> Cancel request</a></li>\
                            </ul>\
                            </div>\
                            <div class="btn-group" style="float:left; position:absolute;margin-left:38px;">\
                            <button class="btn btn-xs purple-intense dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
						        <i class="fa fa-eye"></i>\
						        <i class="fa fa-angle-down"></i>\
		                    </button>\
                            <ul class="dropdown-menu pull-right" role="menu">\
						        <li><a href="javascript:showResultsPage(\'OpEBUSER5e301d61da6f8_5e5fc0fab53483.99018780\',\'QFO_6\');"><i class="fa fa-file-text"></i> View Results</a></li>\
	                        </ul>\
                        </div>'
                   
                }
                
            },

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
        $.ajax({
            type: "POST",
            url: baseURL + "/applib/oeb_publishAPI.php?action=proceedReq",
            data: "actionReq=" + action+"&reqId="+id+"&msg="+$('#messageAction').val(),
            success: function(data) {
                if (data == '1') {
                    setTimeout(function() { 
                        table2.ajax.reload();
                        //refresh table1
                        table1.ajax.reload();
                        
                    }, 500);
                    
                } else if (data == '0') {
                    setTimeout(function() {
                        location.href = 'workspace/';
                        alert("files not correctly submited");
                    }, 500);
                    
                }
            }
        });
    })
}
function showResultsPage(executionFolder, tool){
    location.href = 'tools/' + tool + '/output.php?execution=' + executionFolder;
}

function showReqFlow(reqId) {
    console.log("entra");
    /*
    $.ajax({
        type: "POST",
        url: baseURL + "/applib/oeb_publishAPI.php",
        data: "flowOf=" + reqId,
        success: function(data) {
            if (data == '1') {
                setTimeout(function() { 
                    //TODO
                    
                    
                }, 500);
                
            } else if (data == '0') {
                setTimeout(function() {
                    alert("Not available");
                }, 500);
                
            }
        }
    });
    */
    return '<button type="button" data-toggle="popover" title="Popover title" data-content="And here some amazing content. It very engaging. Right?">'+reqId+'</button>'


}


//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: 'applib/oeb_publishAPI.php?action=getRole'
    })
}

