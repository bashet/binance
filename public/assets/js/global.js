/**
 * Created by Bashet on 04/05/2018.
 */
function valid_this_form(MyForm) {
    $(MyForm).validate({
        ignore: ":hidden:not(.chosen-select)",
        highlight: function(element) {
            $(element).closest('.form-group').addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('is-invalid');
        },
        errorElement: 'div',
        errorClass: 'form-control invalid-feedback',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }
    });
}