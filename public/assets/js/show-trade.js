/**
 * Created by Bashet on 04/05/2018.
 */

$(function () {

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
            $('#frm_trade').submit();
        }
    });
});