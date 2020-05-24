//var a = document.getElementById("a");
var urlJSON = "applib/oeb_processesAPI.php";

$(document).ready(function() {
    colorSteps();
    insertProcessesSelect();

    $('#submit').on("click",function() {
        var nameWorkflow = $.trim($("#nameWorkflow").val());
        if (!nameWorkflow) {
            $("#divErrors").removeClass("alert alert-info");
            $("#divErrors").addClass("alert alert-danger");
            $("#divErrors").text("The name is required.");
            $("#divErrors").show();
        } else {
            if(!nameWorkflow.match(/^[a-zA-Z0-9._]+$/)) {
                $("#divErrors").removeClass("alert alert-info");
                $("#divErrors").addClass("alert alert-danger");
                $("#divErrors").text("The name only admits a-z, A-Z, 0-9, . and _");
                $("#divErrors").show();
            } else {
                setWorkflow();
            }
        }
    });
});

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

function insertProcessesSelect() {
    $.ajax({
        type: 'POST',
        url: urlJSON,
        data: {'action': 'getProcessSelect'}
    }).done(function(data) {
        var sel = $('<select class="form-control" id="validationSelect">').appendTo('#validation');
        for (let x = 0; x < data.length; x++) {
            if (data[x]["data"]["type"] == "validation") {
                $(data[x]).each(function() {
                    sel.append($("<option>").attr('value',data[x]["data"]["title"]).text(data[x]["data"]["title"]));
                });
            } 
        }
    });
}

function setWorkflow() {
    var result;
    var validation = $("#validationSelect").val();
    var metrics = $("#metricsSelect").val();
    var consolidation = $("#consolidationSelect").val();
    var nameWF = $("#nameWorkflow").val();

    $.ajax({
        type: 'POST',
        url: urlJSON,
        data: {'action': 'setWorkflow', 'nameWF': nameWF, 'validation': validation, 'metrics': metrics, 'consolidation': consolidation}
    }).done(function(data) {
        if (data["code"] == 200) {
            $("#divErrors").removeClass("alert alert-danger");
            $("#divErrors").addClass("alert alert-info");
            $("#divErrors").text("Workflow inserted successfully");
            $("#divErrors").show();
            location.href="oeb_management/oeb_process/oeb_workflows.php";
        } else {
            $("#divErrors").removeClass("alert alert-info");
            $("#divErrors").addClass("alert alert-danger");
            $("#divErrors").text(data["message"]);
            $("#divErrors").show(); 
        }
    }).fail(function(data) {
        $("#divErrors").removeClass("alert alert-info");
        $("#divErrors").addClass("alert alert-danger");
        $("#divErrors").text(data["responseJSON"]["message"]);
        $("#divErrors").show();
    });
}