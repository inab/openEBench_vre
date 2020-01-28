jQuery(document).ready(function() {

    $(".multiple_selector").select2({
      placeholder: "Select one or more operations clicking here",
            width: '100%'
    });


    $('.multiple_selector').on('change', function() {
            if($(this).find('option:selected').length > 0) {
                    $(this).parent().removeClass('has-error');
                    $(this).parent().find('.help-block').hide();
            }
    });

ValidateForm.init();
});
