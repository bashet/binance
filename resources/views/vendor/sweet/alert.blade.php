@if (Session::has('sweet_alert.alert'))
    <script>
        swal({
            type: "{!! Session::get('sweet_alert.type') !!}",
            text: "{!! Session::get('sweet_alert.text') !!}",
            title: "{!! Session::get('sweet_alert.title') !!}",
            {{--            icon: "{!! Session::get('sweet_alert.type') !!}",--}}
            showConfirmButton: "{!! Session::get('sweet_alert.showConfirmButton') !!}",
            confirmButtonText: "{!! Session::get('sweet_alert.confirmButtonText') !!}",
            confirmButtonColor: "#AEDEF4"
        });
    </script>
@endif