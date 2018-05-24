/**
 * Created by Bashet on 22/05/2018.
 */
$(function () {
    var intervalId = null;
    var rowCounter = 0;

    function submitForm(){
        intervalId = setInterval(function () {
            $('#frm_trading').submit();
        }, 5000);
    }

    $('#btn_start_scanning').click(function (e) {
        e.preventDefault();
        $('#results').html('');
        $('#btn_start_scanning').prop('disabled', true);
        $('#btn_stop_scanning').prop('disabled', false);

        $('#frm_trading').submit(); //### First Submit is done immediately...

        submitForm(); //### Now create a timer to Submit on every 5 secs!

        // intervalId = setInterval(function () {
        //     $('#frm_trading').submit();
        // }, 5000);

    });

    $('#btn_stop_scanning').click(function (e) {
        e.preventDefault();
        //### Clear all session variables; Clear the Session Variables from Server Side!
        $.ajax({
            type: "POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/clear_session",
            datatype: 'JSON',
            data: { }
        }).done(function (result) {});

        $('#btn_stop_scanning').prop('disabled', true);
        $('#btn_start_scanning').prop('disabled', false);

        if (intervalId)
            clearInterval(intervalId);
    });

    $('#btn_clear_table').click(function (e) {
        e.preventDefault();
        $("#SignalDataTable tbody").empty();
        $("#AutoSignalDataTable tbody").empty();
        rowCounter = 0;
    });


    $('#frm_trading').submit(function (e) {
        e.preventDefault();

        $('#frm_trading').ajaxSubmit({
            success: function (result) {
                var tradeSignal = result.signals.TradeSignal;
                var rowStyle = '';
                if(tradeSignal=='buy')
                    rowStyle = 'bg-primary';
                else if(tradeSignal=='sell')
                    rowStyle='bg-success';
                else
                    rowStyle='';

                var current_time = moment(result.time);
                var tradeTime = current_time.format('HH:mm:ss');
/*
                var closingPrice = '<span class="text-primary"> Price: '+ result.signals.closingPrice +'</span>';
                var macd = '<span class="text-primary"> MACD: '+ result.signals.MACD +'</span>';
                var ema = '<span class="text-danger"> EMA: '+ result.signals.Signal +'</span>';
                var outputResult = '<p>Time: '+ tradeTime + closingPrice + macd + ema +' </p>';
*/

                rowCounter++;
                $('#SignalDataTable').prepend("<tr class='"+ rowStyle + "'><td>" + rowCounter + "</td>" +
                                                        "<td>" + tradeTime + "</td>" +
                                                        "<td>" + result.closingPrice + "</td>" +
                                                        "<td>" + result.signals.MACD + "</td>" +
                                                        "<td>" + result.signals.Signal + "</td></tr>");

                //### When a signal is flagged- display that resultset on a separate table.
                if(tradeSignal == 'buy' || tradeSignal == 'sell'){
                    $('#AutoSignalDataTable').last().append("<tr><td>#</td>" +
                        "<td>" + tradeTime +
                        "<td>" + result.coinPair + "</td>" +
                        "<td>Y</td>" +
                        "<td></td>" +
                        "<td>" + result.signals.TradeSignal + "</td>" +
                        "<td>" + result.closingPrice + "</td>" +
                        "<td>#</td>" +
                        "<td>#</td></tr>");
                }

                $("#MACD_CurrentStatus").text(result.closingPrice + ' / ' + result.signals.MACD + ' / ' + result.signals.Signal);

                console.log(result);
            }
        });
    });
});