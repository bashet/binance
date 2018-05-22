$(function () {
    setInterval(function () {

        //var coinpair = $('#optCoinPair').val();

    }, 5000);

    // $.ajax({
    //     type: "POST",
    //     headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //     url: "/adaeth",
    //     datatype: 'JSON',
    //     data: { }
    // }).done(function processKLineData(result) {
    // });
});


function processKLineData(result)
{
    // var closing_time = moment(result[6]);
    $('#scanning').append(result[0] + ' : ' + result[1]);// + ' @ ' + closing_time.format('DD/MM/YYYY : HH:mm:ss') + '<br/>');
}