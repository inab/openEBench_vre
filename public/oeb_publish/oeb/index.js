var arrayOfFiles = [];

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
            var table = $('#communityTable').DataTable( {
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
                    { "data" : "community" } //5

                ],
                'columnDefs': [
                    {
                        'targets': 0,
                        'title': '<th><input name="select_all" value="1" id="example-select-all" type="checkbox" /></th>',
                        'searchable': false,
                        'orderable': false,
                        'render': function (data, type, full, meta){
                            return '<input type="checkbox" name = "check" value="'+ data + '">';
                        }
                    },
                    {
                        "targets": 1,
                        "title": '<th>Filename <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Execution and file name"></i></th>',
                        render: function ( data, type, row ) {
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
                        "title": '<th>Community  <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                        render: function ( data, type, row ) {
                                return "QFO"
                            
                        }
                    }
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
    confirm("Are you sure you want to request to publish this files?");

}

//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: 'applib/publishFormAPI.php?role'
    })
}
