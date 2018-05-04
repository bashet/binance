/**
 * Created by Bashet on 04/05/2018.
 */

$(function () {

    $('#table_current_market').dataTable({
        responsive: true,
        stateSave: true
    });

    $('#btnTradeAction').click(function (e) {
        e.preventDefault();

        var selectedPair = $('input[name=optCoinPair]:checked').val();
        var selectedInterval = $('input[name=optInterval]:checked').val();
        //alert()
        if (selectedPair == null || selectedInterval == null){
            swal("Coin-Pair/Time interval missing!", '', 'error');
            return;
        }

        if( $('#frm_trade').valid() ){
            $.LoadingOverlay('show',{
                image       : "",
                fontawesome : "fas fa-cog fa-spin"
            });
            $('#frm_trade').submit();
        }
    });

    $('#frm_trade').submit(function (e) {
        e.preventDefault();
        $('#frm_trade').ajaxSubmit({
            success: function (result) {
                $.LoadingOverlay('hide');
                swal('Total Record found: ' + result.length  + ' Please check browser console!', 'Success', 'success');
                console.log(result);
            }
        });
    });
});