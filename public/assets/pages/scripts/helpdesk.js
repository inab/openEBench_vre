//var baseURL = $('#base-url').val();
var Helpdesk = function() {

    var handleHelpdesk = function() {
				$('#Request').change(function() {
                    $("#Subject").val("");
                    $("#Message").val("");
					fillForm($(this).val());
                });

        $('#helpdesk').validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            rules: {
                Request: {
                    required: true,
                },
				Tool: {
                    required: true,
                },
                commmunityList: {
                    required: true,
                },
                challengeList: {
                    required: true,
                },
                Subject: {
                    required: true
                },
                Message: {
                    required: true
                }
            },

            invalidHandler: function(event, validator) { //display error alert on form submit
                //$('#err-mail-pwd', $('.login-form')).show();
            },

            highlight: function(element) { // hightlight error inputs
                $(element)
                    .closest('.form-group').addClass('has-error'); // set error class to the control group
            },

            success: function(label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },

            errorPlacement: function(error, element) {

							if (element.closest('.input-icon').size() === 1) {
                    error.insertAfter(element.closest('.input-icon'));
                } else {
                    error.insertAfter(element);
                }

            },

            submitHandler: function(form) {

            	form.submit();

            }
        });

        $('#helpdesk input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#helpdesk').validate().form()) {
                    $('#helpdesk').submit(); //form validation success, call ajax form submit
                }
                return false;
            }
        });
    }

    return {
        //main function to initiate the module
        init: function() {

           handleHelpdesk();

        }

    };

}();


jQuery(document).ready(function() {
    Helpdesk.init();
    var selectedOption = $('#Request').val();
    fillForm(selectedOption);
    $('#BEList').trigger('change');
    

});

function fillForm(option) {
    if(option == "tools") {
        $('#Tool').prop('disabled', false);
        $('#row-tools').show();
        $('#row-communities').hide();
        $('#row-benchEvent').hide();
        $('#row-challenges').hide();
        $('#label-msg').html("Message details");
    }else if(option == "tooldev"){
        $('#label-msg').html("Please tell us which kind of tool(s) you want to integrate in the VRE");
    }else if(option == "roleUpgrade"){
        $('#row-tools').hide();
        $('#commmunity').prop('disabled', false);
        $('#row-communities').show();
        $('#row-benchEvent').show();
        $('#row-challenges').show();
        roleUpgrade();
    }else{
        $('#Tool').prop('disabled', true);
        $('#row-tools').hide();
        $('#row-communities').hide();
        $('#row-challenges').hide();
        $('#label-msg').html("Message details");
    }
}

function roleUpgrade () {
    //get data --> AJAX TODO -> get user logged, get approver name
    $.ajax({
       url: 'applib/helpdeskPetitions.php?getActors'
     }).done(function(data) {
       var fileinfo = JSON.parse(data);

       //if user already have manager/owner roles, not show
       var requester = fileinfo['Name']+" "+fileinfo['Surname'];
       var roleToupgrade = "contributor";
       var community_name = $( "#commmunityList option:selected" ).text();
       var be_name = $( "#BEList option:selected" ).text();
       var challengesList = $( "#challengeList" ).val();
       console.log(challengesList)

       //subject
       $("#Subject").val("Request to upgrade role from "+requester);
       //message
       $("#Message").html("Dear user,\n\nThe user "+requester+" would like to upgrade its role to "+roleToupgrade+".\
       \n Community: "+community_name+".\n BenchmarkingEvent: "+be_name+".\n Challenges: "+challengesList+"\nIf you agree on that, please accept that request on OEB. \n\nRegards, \n\nOEB team.");

   })
}


$('#commmunityList').on('change',function(){
    $('#BEList').html('');
    
    var communityID = $(this).val();

    if(communityID != ""){
        $.ajax({
            type:'POST',
            url: 'applib/oeb_publishAPI.php?action=listOfBE',
            data:'community_id='+communityID,
            success:function(data){
                var bList = JSON.parse(data);
                $('#BEList').append('<option value="">Select a benchmarking event </option>');
                bList.forEach((element) => {
                    $('#BEList').append('<option value="'+element['_id']+'">'+element['name']+'</option>');
                });
                roleUpgrade();
            }
        }); 
    }else{
        $('#BEList').html('<option value="">Select a Benchmarking event </option>'); 
        $('#challengeList').html('<option value="">Select a Challenge </option>'); 
    }
});


$('#BEList').on('change',function(){
    $('#challengeList').html('');
    
    var BEID = $(this).val();

    if(BEID != ""){
        $.ajax({
            type:'POST',
            url: 'applib/oeb_publishAPI.php?action=listOfChallenge',
            data:'BE_id='+BEID,
            success:function(data){
                var cList = JSON.parse(data);
                cList.forEach((element) => {
                    $('#challengeList').append('<option value="'+element['name']+'">'+element['name']+'</option>');
                });
                roleUpgrade();
            }
        }); 
    }else{
        $('#challengeList').append('<option value="">Select a Challenge </option>'); 
    }
});


$('#challengeList').click(function() {
    roleUpgrade();
});

