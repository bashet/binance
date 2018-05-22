/**
 * Created by Bashet on 22/05/2018.
 */
$(function () {
    $('#btn_start_scanning').click(function (e) {
        e.preventDefault();
        $.LoadingOverlay('show', {
            image       : "",
            fontawesome : "fas fa-cog fa-spin"
        });

        $('#frm_trading').submit();
    });

    $('#frm_trading').submit(function (e) {
        e.preventDefault();

        $('#frm_trading').ajaxSubmit({
            success: function (result) {

                $.LoadingOverlay('hide');
                console.log(result);
            }
        });
    });
});