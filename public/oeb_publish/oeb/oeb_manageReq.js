var table2;
$(document).ready(function() {

    //get user roles
    getRoles().done(function(r) {
        var roles = JSON.parse(r);
        createTableRegisters();
    
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

    function getRoles() {
        return $.ajax({
            type: 'POST',
            url: 'applib/publishFormAPI.php?role'
        })
    }

    


})

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