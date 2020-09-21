//var a = document.getElementById("a");
var urlJSON = "applib/oeb_processesAPI.php";

$(document).ready(function() {
    //Function for the circles of steps (1, 2 and 3) to change the color when they are select
    colorSteps();

    //insert the blocks in the select of validation
    insertProcessesSelect("validation");

    //insert the blocks in the select of metrics
    insertProcessesSelect("metrics");

    //insert the blocks in the select of consolidation
    insertProcessesSelect("consolidation");

    //on click submit
    $('#submit').on("click",function() {
        //initialize the div of errors
        $("#divErrors").text();

        //get the value selected in select of each process
        var nameWorkflow = $.trim($("#nameWorkflow").val());
        var validationProcess = $.trim($("#validationSelect").val());
        var metricsProcess = $.trim($("#metricsSelect").val());
        var consolidationProcess = $.trim($("#consolidationSelect").val());

        //validate is the selects are empty
        if (!validationProcess) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The validation process is empty.");
            $("#divErrors").show();
        } else if (!metricsProcess) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The metric process is empty.");
            $("#divErrors").show();
        } else if (!consolidationProcess) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The consolidation process is empty.");
            $("#divErrors").show();
            //validate if the name is empty
        } else if (!nameWorkflow) {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(" The name is required.");
            $("#divErrors").show();
        } else {
            //validate is the name has strange characters
            if(!nameWorkflow.match(/^[a-zA-Z0-9._]+$/)) {
                $("#divErrors").removeClass(" alert alert-info");
                $("#divErrors").addClass(" alert alert-danger");
                $("#divErrors").text(" The name only admits a-z, A-Z, 0-9, . and _");
                $("#divErrors").show();
            } else {
                //if has no errors, set the workflow into MongoDB
                setWorkflow();
            }
        }
    });
});

//circle colors
function colorSteps() {
    $("#first").on("click", function() {
        $("#secondActive").removeClass(" active");
        $("#thirdActive").removeClass(" active");
    });

    $("#second").on("click", function() {
        $("#thirdActive").removeClass(" active");
        $("#secondActive").addClass(" active");
    });

    $("#third").on("click", function() {
        $("#secondActive").addClass(" active");
        $("#thirdActive").addClass(" active");
    });
}

//create the validation select
function insertProcessesSelect(typeProcess) {
    $.ajax({
        type: 'POST',
        url: urlJSON,
        data: {'action': 'getProcessSelect', 'type': typeProcess}
    }).done(function(data) {
        //type validation
        if (typeProcess == "validation") {
            if (data.length != 0) {
                var sel = $('<select class="form-control" id="validationSelect">').appendTo('#validation');
                for (let x = 0; x < data.length; x++) {
                    if (data[x]["data"]["type"] == "validation") {
                        $(data[x]).each(function() {
                            sel.append($("<option>").attr('value',data[x]["data"]["name"]).text(data[x]["data"]["name"]));
                        });
                    } 
                }
            } else {
                //if are not validation processes the select is disabled
                var sel = $('<select class="form-control" id="validationSelect" disabled>').appendTo('#validation');
            }
        }

        //type metrics
        if (typeProcess == "metrics") {
            if (data.length != 0) {
                var sel = $('<select class="form-control" id="metricsSelect">').appendTo('#metrics');
                for (let x = 0; x < data.length; x++) {
                    if (data[x]["data"]["type"] == "metrics") {
                        $(data[x]).each(function() {
                            sel.append($("<option>").attr('value',data[x]["data"]["name"]).text(data[x]["data"]["name"]));
                        });
                    } 
                }
            } else {
                //if are not metrics processes the select is disabled
                var sel = $('<select class="form-control" id="metricsSelect" disabled>').appendTo('#metrics');
            }
        }

        //type consolidation
        if (typeProcess == "consolidation") {
            if (data.length != 0) {
                var sel = $('<select class="form-control" id="consolidationSelect">').appendTo('#metrics');
                for (let x = 0; x < data.length; x++) {
                    if (data[x]["data"]["type"] == "consolidation") {
                        $(data[x]).each(function() {
                            sel.append($("<option>").attr('value',data[x]["data"]["name"]).text(data[x]["data"]["name"]));
                        });
                    } 
                }
            } else {
                //if are not metrics processes the select is disabled
                var sel = $('<select class="form-control" id="consolidationSelect" disabled>').appendTo('#consolidation');
            }
        }

    });
}

//function set workflow into MongoDB
function setWorkflow() {
    //get the process id selected and the name for the workflow
    var validation = $("#validationSelect").val();
    var metrics = $("#metricsSelect").val();
    var consolidation = $("#consolidationSelect").val();
    var nameWF = $("#nameWorkflow").val();

    //ajax petition with all the necessary information about the workflow
    $.ajax({
        type: 'POST',
        url: urlJSON,
        data: {'action': 'setWorkflow', 'nameWF': nameWF, 'validation': validation, 'metrics': metrics, 'consolidation': consolidation}
    }).done(function(data) {
        //no errors
        if (data["code"] == 200) {
            $("#divErrors").removeClass(" alert alert-danger");
            $("#divErrors").addClass(" alert alert-info");
            $("#divErrors").text("Workflow inserted successfully");
            $("#divErrors").show();
            location.href="oeb_management/oeb_block/oeb_workflows.php";
        //errors
        } else {
            $("#divErrors").removeClass(" alert alert-info");
            $("#divErrors").addClass(" alert alert-danger");
            $("#divErrors").text(data["message"]);
            $("#divErrors").show(); 
        }
    //more errors
    }).fail(function(data) {
        $("#divErrors").removeClass(" alert alert-info");
        $("#divErrors").addClass(" alert alert-danger");
        $("#divErrors").text(data["responseJSON"]["message"]);
        $("#divErrors").show();
    });
}