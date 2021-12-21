var arrayOfFiles = [];
var table1;
var be_selected = $("#beSelector").val();//selected benchmarking event
var allowed;
const CONTROLLER = 'applib/oeb_publishAPI.php'

$(document).ready(function() {
    //get user roles
    getRoles().done(function(r) {
        var roles = JSON.parse(r)
        console.log(roles)
        table1 = createSelectableTable();

        //permisions depending on the role
        if (roles['roles'].length == 0) {
            $("#beSelector").attr('disabled','disabled');
            $("#warning-notAllowed").show();
            $('input').attr('disabled','disabled');
        }
        $("#beSelector").on('change', function() {
            be_selected = $("#beSelector").val();
            allowed = true;
            $("#errorNotAllowed").hide();
            $("#availableFiles").show()
            $('input').attr('disabled',false);
            table1.ajax.reload();
            
            if (!roles['roles'].includes(be_selected)){
                $("#errorNotAllowed a").attr("href", "/vre/helpdesk/?sel=roleUpgrade&BE="+be_selected)
                $("#errorNotAllowed").show();
                allowed = false;
            }
                  
            
        })
    })
    
    //refresh list each time table is clicked
    $("#tableMyFiles" ).on( "click", function() {
        arrayOfFiles = [];
        
        $.each($('tbody tr td:first-child input[type="radio"]:checked', this), function() {
            var obj = {};
            obj['id'] = $(this).val();
            obj['benchmarkingEvent_id'] = $(this).parents('tr').first().children(":nth-child(4)").children("p").prop("id");
            obj['tool'] = $(this).parents('tr').first().children(":nth-child(4)").children("input").prop("id");

            arrayOfFiles.push(obj);
   
        });
        if (arrayOfFiles.length >0) {
            $('#btn-selected-files').prop("disabled", false)
        } else {
            $('#btn-selected-files').prop("disabled", true)
        }
    })
})


/****FUNCTIONS****/

function createSelectableTable(){
    return $('#communityTable').DataTable( {
        "ajax": {
            url: CONTROLLER+'?action=getAllFiles',
            data: ({
                type: JSON.stringify(['OEB_data_model']),
              }),
            "dataSrc": ""
        },
        "bFilter": false, 
        "bPaginate": false,
        "bLengthChange": false,
        "bAutoWidth": true,
        //filter for BE selected
        rowCallback: function( row, data, index ) {
            if (data['benchmarking_event']['be_id'] != be_selected) {
                $("#availableFiles").show()
                $(row).hide();
            }
            if(data['current_status'] == 'pending approval' || 
                data['current_status'] == 'published'){
                $("#availableFiles").show()
                $(row).css('color', 'silver');
            }
            if (be_selected == "") {
                $("#errorNotAllowed").hide()
                $("#availableFiles").hide()
            }
            if (!allowed) {
                $(row).css('color', 'silver');
                $('input',row).prop('disabled', true)
            }

            
        },
        drawCallback: function() {
            $('[data-toggle="popover"]').popover({
                container: 'body'
            })
            
        },
        "columns" : [
            {"data" : "_id"}, //0
            { "data" : "files" }, //1
            { "data" : "path" }, //2
            { "data" : "benchmarking_event" }, //3
            { "data" : "oeb_challenges" }, //4
            { "data" : "mtime" }, //5
            { "data" : "current_status" }, //6 --> to hide
            { "data" : "challenge_status" }, //7
            { "data" : "oeb_id" } //8


        ],
        'columnDefs': [
            {
                'targets': 0,
                "className": "dt-center",
                'searchable': false,
                'orderable': false,
                render: function ( data, type, row ) {
                    if(row['current_status'] == 'pending approval' || 
                        row['current_status'] == 'published'){
                        res = '</br><input disabled type="radio" \
                            name = "'+data+'" value="'+row.files['consolidated']['id']+'">'
                       
                    } else {
                        res = '</br><input  type="radio" name = "'+
                            data+'" value="'+row.files['consolidated']['id']+'">'
                    }
                    return res;
                }
            },
            {
                "targets": 1,
                "title": '<th>Execution files <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="Files in execution folder"></i></th>',
                render:function ( data, type, row ) {
                    if(row['current_status'] == 'pending approval' || row['current_status'] == 'published'){
                        r = "<div><b>"+row['path'].split("/").reverse()[1]+"/</b><a data-html='true' data-toggle='popover' data-placement='top' data-trigger='click' \
                        data-content='<b>Files already submitted: </b><a href =\"oeb_publish/oeb/oeb_manageReq.php#"+row['req_id']+"\">View request</a>' <i class='fa fa-exclamation-triangle' style='color: #F4D03F'></i></a></br>"
                        
                        
                    }else r = "<b>"+row['path'].split("/").reverse()[1]+"/</b></br>";  
                    r += data['consolidated']['path'].split("/").reverse()[0];

                    return r;
                }
            },
            {
                "targets": 2,
                className: "hide_column",
                "title": '<th>Execution workflow <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="Execution and file name"></i></th>',
                render: function ( data, type, row ) {
                    
                    if(row['current_status'] == 'pending approval'){
                        return "<b id ="+data+">"+data.split("/").reverse()[1]+"</b>/" +" <i class=\"fa fa-exclamation-triangle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"File already submitted\" style='color: #F4D03F'></i>";
                    }
                    return "<b id ="+data+">"+data.split("/").reverse()[1]+"</b>/";
                }
            },
            
            {
                "targets": 3,
                "title": '<th>Benchmarking Event <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="OpenEBench benchmarking event in which the dataset was used or the metrics produced"></i></th>',
                render:function ( data, type, row ) {
                    return '<p id ="'+data['be_id']+'">'+data['be_name']+'</p><input id="'+data['workflow_id']+'" type="hidden">';
                }

            },
            {
                "targets": 4,
                "title": '<th>Benchmarking Challenges <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="OpenEBench benchmarking challenges evaluated in the execution"></i></th>',
                render: function ( data, type, row, meta ) {
                    if (data != undefined && data.length != 0){
                        
                        listChallenges = '<ul class = "ul-challenges style="padding-left:4%;" id = "ul-challenges'+meta.row+'">';
                       for (let index = 0; index < data.length; index++) {
                        listChallenges += '<li>'+data[index]+'</li>';
                           
                       }
                       listChallenges += '</ul>';
                       if (data.length >3 ){
                        listChallenges += '<div id ="plusShow" style="text-align: center;"><span>...</span>\
                            <i class="fa fa-plus" style="color: green;float: right;" onclick="showChallenges('+meta.row+')"></i></div>';
                        listChallenges += '<div id ="minusShow" style="display:none"><i class="fa fa-minus" \
                            style="color: red;float: right;" onclick="hideChallenges('+meta.row+')"></i></div>';
                       }
                       
                       return listChallenges;
                    } else return '';
                    
                }

            },
            {
                "targets": 5,
                "title": '<th>Date <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="Execution date"></i></th>',
                render: function ( data, type, row ) {
                    return convertTimestamp(data['$date']['$numberLong']);
                }
            },
            {
                "targets": 6,
                className: "hide_column",
                "title": '<th>Status petition  <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title=""></i></th>',
                render: function ( data, type, row ) {
                    if (!data) {
                        return "not submited"
                    }
                    return data
                
                }
                
            },
            {
                "targets":7,
                "title": '<th>Event status  <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="Benchmarking event status, if it is still open to submit datasets or not"></i></th>',
                render: function ( data, type, row ) {
                        return "Open"
                    //TODO (for now, events date are irrellevant)
                }
            },
            {
                "targets": 8,
                "title": '<th>Published on OEB <i class="icon-question" data-toggle="tooltip" \
                    data-placement="top" title="If datasets are already published in OpenEBench"></i></th>',
                render: function ( data, type, row ) {
                    if (data) return data ;
                    else return false;
                    
                }
            }

        ],
        'order': [[1, 'asc']]

    });
}

function showChallenges(numRow) {
        $('#ul-challenges'+numRow+' li:hidden').show();
        if ($('#ul-challenges'+numRow+' li').length == $('#ul-challenges'+numRow+' li:visible').length) {
            $('#plusShow').hide();
            $('#minusShow').show();
        }
}
function hideChallenges(numRow){
    $('#ul-challenges'+numRow+' li:nth-child(n+4)').hide();
    $('#plusShow').show();
    $('#minusShow').hide();

}



function submitFiles() {
    var myForm = $('#files-form');
    $('#filesInput').val(JSON.stringify(arrayOfFiles));
    myForm.submit();
    return false;
}

//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: CONTROLLER+'?action=getRole'
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

