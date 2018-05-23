/**
 * Created by Bashet on 22/05/2018.
 */
$(function () {
    var intervalId = null;
    $('#btn_start_scanning').click(function (e) {
        e.preventDefault();
        $('#results').html('');
        $('#btn_start_scanning').prop('disabled', true);
        $('#btn_stop_scanning').prop('disabled', false);

        intervalId = setInterval(function () {
            $('#frm_trading').submit();
        }, 5000);

    });

    $('#btn_stop_scanning').click(function (e) {
        e.preventDefault();

        $('#btn_stop_scanning').prop('disabled', true);
        $('#btn_start_scanning').prop('disabled', false);

        if (intervalId)
            clearInterval(intervalId);
    });

    $('#frm_trading').submit(function (e) {
        e.preventDefault();

        $('#frm_trading').ajaxSubmit({
            success: function (result) {
                var current_time = moment(result.time);
                var closingPrice = '<span class="text-primary"> Price: '+ result.signals.closingPrice +'</span>';
                var macd = '<span class="text-primary"> MACD: '+ result.signals.MACD +'</span>';
                var ema = '<span class="text-danger"> EMA: '+ result.signals.Signal +'</span>';
                var outputResult = '<p>Time: '+ current_time.format('DD/MM/YYYY : HH:mm:ss') + closingPrice + macd + ema +' </p>';


                //$('#result_container').show();

                //$('#results').prepend(outputResult);

                $('#SignalDataTable').first().append("<tr><td>#</td>" +
                                                        "<td>" + current_time.format('YYYY/MM/DD : HH:mm:ss') + "</td>" +
                                                        "<td>" + result.closingPrice + "</td>" +
                                                        "<td>" + result.signals.MACD + "</td>" +
                                                        "<td>" + result.signals.Signal + "</td></tr>");

                console.log(result);
            }
        });
    });
});