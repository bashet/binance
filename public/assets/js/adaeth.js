$(function () {
    setInterval(function() {

        $.ajax({
            type: "POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/adaeth",
            datatype: 'JSON',
            data:{}
        }).done(function (result) {
            var row = result[0];
            var closing_time = moment.unix(row[6]);
            $('#scanning').append('Closing time: ' + closing_time.format('DD/MM/YYYY : HH:mm:ss') + '<br>');
        });
    }, 5000);
});