var arrayOfFiles = [];
var table1;
var table2;

$(document).ready(function() {

    //get user roles
    getRoles().done(function(r) {
        var roles = JSON.parse(r)
        
        //TODO: get number of communities
        var communities = new Array("Quest for Orthologs", "TCGA", "CIBERER"); 
        var files = new Array("");

        createTable();
        createTableRegisters();
        
        //permisions depending on the role
        if (roles === null) {
            $("#communitySelector").attr('disabled','disabled');
            $("#warning-notAllowed").show();
        } else if (roles.find(a =>a.includes("contributor"))===undefined && roles.find(a =>a.includes("manager"))===undefined && roles.find(a =>a.includes("owner"))===undefined) {
            $("#communitySelector").attr('disabled','disabled');
            $("#warning-notAllowed").show();
        }else {
            
        }
        
        //refresh list each time table is clicked
        $("#tableMyFiles" ).on( "click", function() {
            arrayOfFiles = [];

            $("#list-files-submit").empty();
            
            $.each($('tbody tr '), function() {
                //check if inputcheckbox is checked
                if($('td:first-child input[type="checkbox"]', this).prop('checked')) {
                    //get id
                    arrayOfFiles.push($('td:first-child input', this).prop('value'));
                }
            });

            var li;
            if (arrayOfFiles.length === 0 ) {
                $("#actions-files").hide();
                $("#desc-files-submit").show();

            } else {
                //create list of files to submit
                $.each( arrayOfFiles, function( index, value ){
                    li = '<li > \
                                <div class="col1"> \
                                    <div class="cont"> \
                                        <div class="cont-col1">\
                                            <div class="label label-sm label-info">\
                                                <i class="fa fa-file"></i>\
                                            </div>\
                                        </div>\
                                        <div class="cont-col2">\
                                            <div class="desc">\
                                                <span class="text-info" style="font-weight:bold;">' +
                                                value + 
                                            '</div>\
                                        </div>\
                                    </div>\
                                </div>\
                                <div class="col2"> \
                                    <div class="label label-sm label-danger" style="float: right;padding:0">\
                                        <a href="javascript:removeFromList(\''+value+'\');" title="Clear file from list" class="btn btn-icon-only red" style="width: 25px;height: 25px;padding-top: 1px;"><i class="fa fa-times-circle"></i></a>' +
                                    '</div>\
                                </div>\
                            </li>'
                    
                    $('#list-files-submit').append(li);
                    $("#actions-files").show();
                    $("#desc-files-submit").hide();
                });
            }
        })
    



        function createTable(){
            table1 = $('#communityTable').DataTable( {
                "ajax": {
                    url: 'applib/publishFormAPI.php?action=getAllFiles&type=participant',
                    dataSrc: ''
                },
                "columns" : [
                    {"data" : "_id" }, //0
                    { "data" : "path" }, //1
                    { "data" : "datatype_name" }, //2
                    { "data" : "status" }, //3
                    { "data" : "oeb_id" }, //4
                    { "data" : "current_status" }, //5 --> to hide
                    { "data" : "challenge_status" }, //6
                    { "data" : "oeb_id" } //7


                ],
                'columnDefs': [
                    {
                        'targets': 0,
                        'title': '<th><input name="select_all" value="1" id="example-select-all" type="checkbox" /></th>',
                        'searchable': false,
                        'orderable': false,
                        render: function ( data, type, row ) {
                            if(row['current_status'] == 'approved' || row['current_status'] == 'pending approval'){
                                return '<input type="checkbox" name = "check" value="'+ data + '" disabled>';
                            }
                            return '<input type="checkbox" name = "check" value="'+ data + '">';
                        }
                    },
                    {
                        "targets": 1,
                        "title": '<th>Filename <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Execution and file name"></i></th>',
                        render: function ( data, type, row ) {
                            
                            if(row['current_status'] == 'pending approval'){
                                return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop() +" <i class=\"fa fa-exclamation-triangle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"File already submitted\" style='color: #F4D03F'></i>";
                            }
                            return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
                        }
                    },
                    {
                        "targets": 2,
                        "title": '<th>Data type <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Type of data file"></i></th>'
                    },
                    {
                        "targets": 3,
                        "title": '<th>Challenge name <i class="icon-question" data-toggle="tooltip" data-placement="top" title="OpenEBench identifier"></i></th>',
                        render: function ( data, type, row ) {
                            return 'QFO challenge 6'
                            
                        }
        
                    },
                    {
                        "targets": 4,
                        "title": '<th>Date <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                        render: function ( data, type, row ) {
                                return "20-Nov-2020"
                            
                        }
                    },
                    {
                        "targets": 5,
                        className: "hide_column",
                        "title": '<th>Status petition  <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                        render: function ( data, type, row ) {
                            if (!data) {
                                return "not submited"
                            }
                            return data
                        
                        }
                        
                    },
                    {
                        "targets": 6,
                        "title": '<th>Challenge status  <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                        render: function ( data, type, row ) {
                                return "Open"
                            
                        }
                    },
                    {
                        "targets": 7,
                        "title": '<th>OEB id  <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                        render: function ( data, type, row ) {
                                return ""
                            
                        }
                    }

                ],
                'order': [[1, 'asc']]
        
            });
            
        }

        function createTableRegisters(){
            table2 = $('#tableAllFiles').DataTable( {
                "autoWidth": false,
                "ajax": {
                    url: 'applib/publishFormAPI.php?action=getSubmitRegisters',
                    dataSrc: ''
                },
                "columns" : [
                    {"data" : "_id" }, //0
                    { "data" : "file_path" }, //1
                    { "data" : "requester_name" }, //2
                    { "data" : "current_status" }, //3
                    { "data" : "timestamp_request" }, //4
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
                            return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
                        }
                        
                    },
                    {
                        "targets": 2,
                        "title": '<th>Requester</th>'
                        
                    },
                    {
                        "targets": 3,
                        "title": '<th>Status </th>',
                        render: function ( data, type, row ) {
                            return showReqFlow(row['_id'])
   
                            
                        }
                        
                    },
                    {
                        "targets": 4,
                        className: "hide_column",
                        "title": '<th>Timestamp request </th>',

                        render: function ( data, type, row ) {
                            return null
                            
                        }
                        
                    },
                    {
                        "targets": 5,
                        "title": '<th>Actions </th>',
                        render: function ( data, type, row ) {
                            if(row['current_status'] == 'approved'){
                                return ""
                            }
                            return '<div class="btn-group" style="margin-left: auto; margin-right: auto;">\
                            <button class="btn btn-xs blue-madison dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">\
                                    <i class="fa fa-cogs"></i>\
                                    <i class="fa fa-angle-down"></i>\
                            </button>\
                          <ul class="dropdown-menu pull-right" role="menu">\
                                <li><a href="javascript:void(0)" onclick="actionTable2(\''+row['_id']+'\',\'approve\');"><i class="fa fa-check-circle" style = "color:#74b72e;"></i> Approve request</a></li>\
                                <li><a href="javascript:void(0)" onclick="actionTable2(\''+row['_id']+'\',\'deny\');"><i class="fa fa-times-circle" style = "color:#E00909;"></i> Deny request</a></li>\
                                <li><a href="javascript:void(0)" onclick="actionTable2(\''+row['_id']+'\',\'cancel\');"><i class="fa fa-times-circle" style = "color:#c0c0c0;"></i> Cancel request</a></li></ul>\
                        </div>'
                            
                        }

                        
                    },

                ],
                'order': [[1, 'asc']]
        
            });
            

        }
    })

})


/****FUNCTIONS****/

// remove files from the list
function removeFromList (option) {

    if (option == "all"){
        //remove all files
        $.each($('tbody tr '), function() {
            $('input', this).prop('checked', false); 
            $( "#tableMyFiles" ).click();
        })  


    }else {
        // remove file
        $.each($('tbody tr '), function() {
            if($('input[value="'+option+'"]', this)) {
                
                $('input[value="'+option+'"]', this).prop('checked', false); 
                $( "#tableMyFiles" ).click();
                
            }

        })

    }
}

// remove files from the list
function submit() {
    console.log(arrayOfFiles);
    var r = confirm("Are you sure you want to request to publish this files?");
    if(r){
        for (let index = 0; index < arrayOfFiles.length; index++) {
            $.ajax({
                type: "POST",
                url: baseURL + "/applib/publishFormAPI.php",
                data: "fileId=" + arrayOfFiles[index],
                success: function(data) {
                    if (data == '1') {
                        setTimeout(function() { 
                            table2.ajax.reload();
                            //refresh table1
                            table1.ajax.reload();
                            $.each($('tbody tr '), function() {
                                if($('td:first-child input[type="checkbox"]', this).prop('checked')) {
                                    $('td:first-child input', this).removeAttr("checked");
                                }
                            })
                            $("#tableMyFiles" ).click();
                            
                        }, 500);
                        
                    } else if (data == '0') {
                        setTimeout(function() {
                            location.href = 'workspace/';
                            alert("files not correctly submited");
                        }, 500);
                        
                    }
                }
            });
        
        }
    } 


}


//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: 'applib/publishFormAPI.php?role'
    })
}


function actionTable2(id, action) {
    var r = confirm("Are you sure you want to "+action+" that request?");
    //add input text message
    if(r) {
        $.ajax({
            type: "POST",
            url: baseURL + "/applib/publishFormAPI.php",
            data: "actionReq=" + action+"&reqId="+id,
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

    }

}

function showReqFlow(reqId) {
    console.log("entra");
    /*
    $.ajax({
        type: "POST",
        url: baseURL + "/applib/publishFormAPI.php",
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
    return '<a href="javascript:void(0)" data-container="body" data-toggle="popover" data-placement="top" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">\
    hola\
  </a>'


}