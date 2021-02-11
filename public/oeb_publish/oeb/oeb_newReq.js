var arrayOfFiles = [];
var table1;

$(document).ready(function() {

    //get user roles
    getRoles().done(function(r) {
        var roles = JSON.parse(r)
        
        //TODO: get number of communities
        var communities = new Array("Quest for Orthologs", "TCGA", "CIBERER"); 
        var files = new Array("");

        createTable();
        
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
                    var obj = {};
                    var id = "id";
                    var val0 = $('td:first-child input', this).prop('value');
                    var filename = "filename";
                    var val1 = $('td:nth-child(2) b', this).prop('id').split("/").pop();

                    obj[id] = val0;
                    obj[filename] = val1;
                    
                    //arrayOfFiles.push($('td:first-child input', this).prop('value'));
                    arrayOfFiles.push(obj);
                    console.log(arrayOfFiles)
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
                                                value['filename'] + 
                                            '</div>\
                                        </div>\
                                    </div>\
                                </div>\
                                <div class="col2"> \
                                    <div class="label label-sm label-danger" style="float: right;padding:0">\
                                        <a href="javascript:removeFromList(\''+value['id']+'\');" title="Clear file from list" class="btn btn-icon-only red" style="width: 25px;height: 25px;padding-top: 1px;"><i class="fa fa-times-circle"></i></a>' +
                                    '</div>\
                                </div>\
                            </li>'
                    
                    $('#list-files-submit').append(li);
                    $("#actions-files").show();
                    $("#desc-files-submit").hide();
                });
            }
        })
    
    })

})


/****FUNCTIONS****/

function createTable(){
    table1 = $('#communityTable').DataTable( {
        "ajax": {
            url: 'applib/publishFormAPI.php?action=getAllFiles&type=participant',
            dataSrc: ''
        },
        
        "columns" : [
            {"data" : "_id"}, //0
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
                "className": "dt-center",
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
                        return "<b id ="+data+">"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop() +" <i class=\"fa fa-exclamation-triangle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"File already submitted\" style='color: #F4D03F'></i>";
                    }
                    return "<b id ="+data+">"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
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
    var list = "";
    $("#filesAboutToSubmit tr").remove();
    for (let index = 0; index < arrayOfFiles.length; index++) {
        list += '<tr><td style="vertical-align: middle;"><div class="label label-sm label-info"><i class="fa fa-file"></i></div>&nbsp<span class="text-info" style="font-weight:bold;">'+arrayOfFiles[index]['filename']+'</span></td><td><input type="text" class="form-control" id = "msg'+index+'" placeholder="Enter your message..."></td></tr>'
        
        
    }
    
    $("#filesAboutToSubmit").append(list);
    $("#reqSubmitDialog").modal('show'); 
    
    
    $("#submitModal").click(function (){
        $("#reqSubmitDialog").modal('hide'); 
        
        for (let index = 0; index < arrayOfFiles.length; index++) {
            $.ajax({
                type: "POST",
                url: baseURL + "/applib/publishFormAPI.php",
                data: {fileId: arrayOfFiles[index]['id'], msg:$('#msg'+index).val()},
                success: function(data) {
                    if (data == '1') {
                        setTimeout(function() { 
                            $("#alert").append(createAlert(arrayOfFiles[index]['filename'],"success"));
                            //refresh table1
                            table1.ajax.reload();
                            $.each($('tbody tr '), function() {
                                if($('td:first-child input[type="checkbox"]', this).prop('checked')) {
                                    $('td:first-child input', this).removeAttr("checked");
                                    
                                }
                            })
                           
                            
                        }, 500);
                        
                    } else if (data == '0') {
                        setTimeout(function() {
                            location.href = 'workspace/';
                            alert("files not correctly submited");
                            $("#alert").append(createAlert(arrayOfFiles[index]['filename'],"notComplete"));
                        }, 500);
                        
                    }
                }
            });
        }
        $("#tableMyFiles" ).click();
    });
    
}


//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: 'applib/publishFormAPI.php?role'
    })
}

function createAlert ($fileName, $action) {
    if ($action == "success") {
        return  '<div class="alert alert-success alert-dismissible fade in">\
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>\
                <strong>File '+$fileName+' correctly request!</strong> To manage your request: <a href="/vre/oeb_publish/oeb/oeb_manageReq.php" class="alert-link">click here!</a>\
                </div>'

    } else {
        return '<div class="alert alert-danger alert-dismissible fade in">\
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>\
    <strong>File '+$fileName+' no correctly request!</strong> \
    </div>'

    }
}

