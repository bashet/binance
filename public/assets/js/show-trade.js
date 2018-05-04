/**
 * Created by Bashet on 04/05/2018.
 */

$(function () {
    valid_this_form('#frm_trade');

    $('#btnTradeAction').click(function (e) {
        e.preventDefault();

        if( $('#frm_trade').valid() ){
            $('#frm_trade').submit();
        }
    });
});