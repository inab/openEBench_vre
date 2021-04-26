var arrayOfFiles = [];
var table1;

$(document).ready(function() {

    $("#selectAll").click();

    //get user roles
    getRoles().done(function(r) {
        var roles = JSON.parse(r)
        
        //TODO: get number of communities
        var communities = new Array("Quest for Orthologs", "TCGA", "CIBERER"); 

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
            
            $.each($('tbody tr td:first-child input[type="radio"]:checked', this), function() {
                var obj = {};
                obj['id'] = $(this).val();
                obj['benchmarkingEvent_id'] = $(this).parents('tr').first().children(":nth-child(4)").children("p").prop("id");
    
                //arrayOfFiles.push($('td:first-child input', this).prop('value'));
                arrayOfFiles.push( obj);
       
            });
            console.log(arrayOfFiles);
            if (arrayOfFiles.length >0) {
                $('#btn-selected-files').prop("disabled", false)
            } else {
                $('#btn-selected-files').prop("disabled", true)
            }
        })
    
    })
    

})


/****FUNCTIONS****/

function createTable(){
    table1 = $('#communityTable').DataTable( {
        "ajax": {
            url: 'applib/oeb_publishAPI.php?action=getAllFiles&type[]=OEB_data_model',
            dataSrc: ''
        },
        
        "columns" : [
            {"data" : "_id"}, //0
            { "data" : "files" }, //1
            { "data" : "path" }, //1
            { "data" : "benchmarking_event" }, //2
            { "data" : "oeb_challenges" }, //3
            { "data" : "mtime" }, //4
            { "data" : "current_status" }, //5 --> to hide
            { "data" : "challenge_status" }, //6
            { "data" : "oeb_id" } //7


        ],
        'columnDefs': [
            {
                'targets': 0,
                "className": "dt-center",
                'searchable': false,
                'orderable': false,
                render: function ( data, type, row ) {
                    r = '<input type="radio" name = "'+data+'" value="'+row.files['participant']['id']+'"></br></br>\
                    <input type="radio" name = "'+data+'" value="'+row.files['consolidated']['id']+'">'
                    return r;
                }
            },
            {
                "targets": 1,
                "title": '<th>Files <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Type of data file"></i></th>',
                render:function ( data, type, row ) {
                    r = data['participant']['path']+'</br></br>';
                    r += data['consolidated']['path'].split("/").reverse()[0];

                    return r;
                }
            },
            {
                "targets": 2,
                "title": '<th>Execution workflow <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Execution and file name"></i></th>',
                render: function ( data, type, row ) {
                    
                    if(row['current_status'] == 'pending approval'){
                        return "<b id ="+data+">"+data.split("/").reverse()[1]+"</b>/" +" <i class=\"fa fa-exclamation-triangle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"File already submitted\" style='color: #F4D03F'></i>";
                    }
                    return "<b id ="+data+">"+data.split("/").reverse()[1]+"</b>/";
                }
            },
            
            {
                "targets": 3,
                "title": '<th>Benchmarking Event <i class="icon-question" data-toggle="tooltip" data-placement="top" title="OpenEBench benchmarking event in which the dataset was used or the metrics produced"></i></th>',
                render:function ( data, type, row ) {
                    return '<p id ="'+data['be_id']+'">'+data['be_name']+'</p>';
                }

            },
            {
                "targets": 4,
                "title": '<th>Benchmarking Challenges<i class="icon-question" data-toggle="tooltip" data-placement="top" title="OpenEBench benchmarking challenges included in the metrics"></i></th>',
                render: function ( data, type, row ) {
                    if (data != undefined && data.length != 0){
                        
                        listChallenges = '<ul id = "ul-challenges">';
                       for (let index = 0; index < data.length; index++) {
                        listChallenges += '<li>'+data[index]+'</li>';
                           
                       }
                       listChallenges += '</ul>';
                       listChallenges += '<div id ="plusShow" style="text-align: center;"><span>...</span><i class="fa fa-plus" style="color: green;float: right;" onclick="showChallenges()"></i></div>';
                       
                       return listChallenges;
                    } else return '';
                    
                }

            },
            {
                "targets": 5,
                "title": '<th>Date <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                render: function ( data, type, row ) {
                    return convertTimestamp(data['$date']['$numberLong']);
                        //return data['sec'].toLocalTime();
                    
                }
            },
            {
                "targets": 6,
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
                "targets":7,
                "title": '<th>Benchmark event status  <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                render: function ( data, type, row ) {
                        return "Open"
                    
                }
            },
            {
                "targets": 8,
                "title": '<th>OEB id  <i class="icon-question" data-toggle="tooltip" data-placement="top" title="Identifier of EUDAT/B2SHARE"></i></th>',
                render: function ( data, type, row ) {
                        return ""
                    
                }
            }

        ],
        'order': [[1, 'asc']]

    });
    
}

function showChallenges() {

        //$('#ul-challenges li:hidden').slice(0, 2).show();
        $('#ul-challenges li:hidden').show();
        /*
        if ($('#ul-challenges li').length == $('#ul-challenges li:visible').length) {
            $('#plusShow').hide();
        }
        */
       $('#plusShow').hide();
};





function submitFiles() {
    var myForm = $('#files-form');
    $('#filesInput').val(JSON.stringify(arrayOfFiles));
    myForm.submit();
    return false;
}



/*

// remove files from the list
function submit() {
    var list = "";
    $("#filesAboutToSubmit tr").remove();
    for (let index = 0; index < arrayOfFiles.length; index++) {
        list += '<tr><td style="vertical-align: middle;"><div class="label label-sm label-info"><i class="fa fa-file"></i></div>&nbsp<span class="text-info" style="font-weight:bold;">'+arrayOfFiles[index]['filename']+'</span></td><td><input type="text" class="form-control" id = "msg'+index+'" placeholder="Enter your message..."></td></tr>'
        
        
    }
    
    $("#filesAboutToSubmit").append(list);
    $("#reqSubmitDialog").modal('show'); 

   
    

    $("#submitModal").click(function (){
        $("#reqSubmitDialog").modal('hide'); 
        console.log(arrayOfFiles);
        
        //window.location.href = "oeb_publish/oeb/oeb_editMetadata.php?files="+arrayOfFiles;


        /*
        for (let index = 0; index < arrayOfFiles.length; index++) {
            $.ajax({
                type: "POST",
                url: baseURL + "/applib/oeb_publishAPI.php?action=request",
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
                            alert("files not correctly submited");
                            $("#alert").append(createAlert(arrayOfFiles[index]['filename'],"notComplete"));
                        }, 500);
                        return false;
                        
                        
                    }
                }
            });
        }
        window.location.href = "oeb_publish/oeb/oeb_editMetadata.php";
        
        $("#tableMyFiles" ).click();
        
    });
    
}

*/
//get petition to get user roles
function getRoles() {
    return $.ajax({
        type: 'POST',
        url: 'applib/oeb_publishAPI.php?action=getRole'
    })
}

function createAlert ($fileName, $action) {
    if ($action == "success") {
        return  '<div class="alert alert-success alert-dismissible fade in">\
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>\
                <strong>File '+$fileName+' correctly requested!</strong> To manage your request: <a href="/vre/oeb_publish/oeb/oeb_manageReq.php" class="alert-link">click here!</a>\
                </div>'

    } else {
        return '<div class="alert alert-danger alert-dismissible fade in">\
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>\
    <strong>File '+$fileName+' no correctly request!</strong> \
    </div>'

    }
}
/***********************FILTERS******************************** */
$("#selectAll").click(function () {
    $("#selectConsolidated").removeClass('active');
    $("#selectParticipant").removeClass('active');
    $("#selectAll").addClass('active');
    $("#communityTable").find("tbody").find("tr").show();
});

$("#selectParticipant").click(function () {
    $("#selectAll").removeClass('active');
    $("#selectConsolidated").removeClass('active');
    $("#selectParticipant").addClass('active');
    var rows = $("#communityTable").find("tbody").find("tr").hide();
    rows.filter(":contains('Input: data to evalute')").show();
});

$("#selectConsolidated").click(function () {
    $("#selectAll").removeClass('active');
    $("#selectParticipant").removeClass('active');
    $("#selectConsolidated").addClass("active");
    var rows = $("#communityTable").find("tbody").find("tr").hide();
    rows.filter(":contains('Output: OEB data')").show();
});


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

  $('#button-challe').click(function () {
      alert("hola")
    $('#ul-challenges').classList.toggle('hidden');
  });