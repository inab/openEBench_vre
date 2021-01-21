 
function currentUser() {
	return $.ajax({
		type: 'POST',
		url: 'applib/publishFormAPI.php?action=getAllFiles'
	});
}

$(document).ready(function() {
    $.when(currentUser()).done(function(user){
        console.log(user)
    })
    //create tabs
    //TODO: get number of communities

    var communities = new Array("Quest for Orthologs", "TCGA", "CIBERER"); 
    var files = new Array("")

    //create tabs
    for (let index = 0; index < communities.length; index++) {
        var tabTitles = $('<li class="uppercase"><a href="#communities'+index+'" data-toggle="tab">'+communities[index]+' </a></li>');
        $("#tabs").append(tabTitles);
        $(".tab-content").append('<div class="tab-pane" id="communities'+index+'"></div>');
        $("#communities"+index).append(createListFiles());

        

        $("#communities"+index).append('\
                    <div class="row"> \
                        <div class="col-md-12 col-sm-12"> \
                            <div class="portlet light portlet-fit bordered">\
                                <div class="portlet-body">\
                                    <table id="community'+index+'" class="table table-striped table-hover table-bordered" width="100%"></table>\
                                </div> \
                            </div> \
                        </div> \
                    </div>');

        createTable(index);

    }

    //active pane the first one
    $("#tabs li:first").addClass("active");
    $("#communities0").addClass("active");


    //refresh list each time tab panel is clicked
    $( ".tab-pane.active" ).on( "click", function() {
        var community = this.getAttribute("id");
        //console.log(community);

        $("#list-files-run-tools").empty();
        var arrayOfFiles = [];
        $.each($('tbody tr '), function() {
            //check if inputcheckbox is checked
            if($('td:first-child input[type="checkbox"]', this).prop('checked')) {
                //get id
                arrayOfFiles.push(($('td:nth-child(2)', this)).prop('innerText'));
            }
            
        });
        var li;
        if (arrayOfFiles.length === 0 ) {
            $("#actions-files").hide();

        } else {
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
                
                $('#list-files-run-tools').append(li);
                $("#actions-files").show();
              
               
            });
            
        }
        
        //console.log(arrayOfFiles);
    })



    function createListFiles(){
        var listContainer = '<div class="row"> \
                                <div class="col-md-12 col-sm-12"> \
                                    <div class="portlet light portlet-fit bordered">\
                                        <div class="portlet-body">\
                                            <h4 style="font-weight: bold; color: #666;">List of files</h4>\
                                            <div>List of datasets to be published.</div><br/> \
                                            <ul class="feeds" id="list-files-run-tools"></ul><br><br> \
                                            <div id="actions-files"  style="float:right; display: none;">\
                                                <div class="btn-group">\
											        <a class="btn btn-sm blue-madison" href="javascript:;">Subtmit</a>\
                                                </div>\
                                                <div class="btn-group">\
											        <a class="btn btn-sm blue-madison" style="background-color:#e7505a; border-color: #e7505a;" href="javascript: removeFromList(\'all\');">Clear all files</a>\
                                                </div>\
                                            </div> \
                                        </div>\
                                    </div>\
                                </div>\
                            </div>\
                            <br>'
        return listContainer;
    }

   




    function createTable(numTab){
        var table = $('#community'+numTab).DataTable( {
            "ajax": {
                url: 'applib/publishFormAPI.php?action=getAllFiles&type=participant',
                dataSrc: ''
            },
            "columns" : [
                {"data" : "_id" }, //0
                { "data" : "_id" }, //1
                { "data" : "path" }, //2
                { "data" : "datatype_name" }, //3
                { "data" : "status" }, //4
                { "data" : "oeb_id" } //5
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
                    "targets": 4,
                    "title": '<th>OEB ID <i class="icon-question" data-toggle="tooltip" data-placement="top" title="OpenEBench identifier"></i></th>',
                    render: function ( data, type, row ) {
                        return 'Available to publish'
                        
                    }
    
                },
                {
                    "targets": 2,
                    "title": '<th>Filename <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Execution and file name"></i></th>',
                    render: function ( data, type, row ) {
                        return "<b>"+data.split("/").reverse()[1]+"</b>/"+data.split("/").pop();
                    }
                },
                {
                    "targets": 3,
                    "title": '<th>Data type <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Type of data file"></i></th>'
                },

                {
                    "targets": 1,
                    
                    className: "hide_column"
    
                },
    
                {
                    "targets": 5,
                    "title": '<th>Eudat DOI <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                    render: function ( data, type, row ) {
                            return "OEBD0020000EC2"
                        
                    }
                }
            ],
             'order': [[1, 'asc']]
    
          });
        
    }


})

// remove files from the list
function removeFromList  (option) {

    if (option == "all"){
        //remove all files
        $.each($('tbody tr '), function() {
            $('input', this).prop('checked', false); 
            $( ".tab-pane.active" ).click();
        })  


    }else {
        // remove file
        $.each($('tbody tr '), function() {
            if($('input[value="'+option+'"]', this)) {
                
                $('input[value="'+option+'"]', this).prop('checked', false); 
                $( ".tab-pane.active" ).click();
                
            }

        })

    }
    
    
}