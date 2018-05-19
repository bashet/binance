$(function () {
    setInterval(function() {

        $.ajax({
            type: "POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/adaeth",
            datatype: 'JSON',
            data:{}
        }).done(function (result) {
            var closing_time = moment(result[6]);
            $('#scanning').append(result[4] + ' @ ' + closing_time.format('DD/MM/YYYY : HH:mm:ss') + '<br>');
        });
    }, 5000);
});