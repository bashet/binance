$(function () {
    setInterval(function() {
        var msg = 'scanning .....<br>';

        $('#scanning').append(msg);

        $.ajax({
            type: "POST",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: "/adaeth",
            datatype: 'JSON',
            data:{}
        }).done(function (result) {

        });
    }, 5000);
});