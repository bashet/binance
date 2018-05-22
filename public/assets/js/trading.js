/**
 * Created by Bashet on 22/05/2018.
 */
$(function () {
    $('#btn_start_scanning').click(function (e) {
        e.preventDefault();

        setInterval(function () {
            $('#frm_trading').submit();
        }, 5000);

    });

    $('#frm_trading').submit(function (e) {
        e.preventDefault();

        $('#frm_trading').ajaxSubmit({
            success: function (result) {
                var current_time = moment(result.time);
                var macd = '<span class="text-primary"> MACD: '+ result.signals.macd +'</span>';
                var ema = '<span class="text-danger"> EMA: '+ result.signals.ema +'</span>';
                var htnl = '<p>Time: '+ current_time.format('DD/MM/YYYY : HH:mm:ss') + macd + ema +' </p>';


                $('#result_container').show();

                $('#results').prepend(htnl);

                console.log(result);
            }
        });
    });
});