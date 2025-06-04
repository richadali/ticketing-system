<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Ticketing System</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  <link href="{{asset('assets/vendor/quill/quill.snow.css')}}" rel="stylesheet">
  <link href="{{asset('assets/vendor/quill/quill.bubble.css')}}" rel="stylesheet">
  <link href="{{asset('assets/vendor/remixicon/remixicon.css')}}" rel="stylesheet">
  <link href="{{asset('assets/vendor/simple-datatables/style.css')}}" rel="stylesheet">



  <!-- Template Main CSS File -->
  <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
  <link href="{{ asset('assets/css/login.css') }}" rel="stylesheet">

</head>

<body class="custom-background">
  <div id="app">
    <main class="py-4">
      @yield('content')
    </main>
  </div>
  <!-- Vendor JS Files -->
  <script src="{{asset('assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
  <script src="{{asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('assets/vendor/chart.js/chart.min.js')}}"></script>
  <script src="{{asset('assets/vendor/echarts/echarts.min.js')}}"></script>
  <script src="{{asset('assets/vendor/quill/quill.min.js')}}"></script>
  <script src="{{asset('assets/vendor/simple-datatables/simple-datatables.js')}}"></script>
  <script src="{{asset('assets/vendor/tinymce/tinymce.min.js')}}"></script>
  <script src="{{asset('assets/vendor/php-email-form/validate.js')}}"></script>

  <!-- Template Main JS File -->
  <script src="{{asset('assets/js/main.js')}}"></script>
  <script src="{{asset('assets/js/jquery.js')}}"></script>
  <script src="{{ asset('js/select2.min.js') }}"></script>

  <script>
    $(document).ready(function(){
        $.ajaxSetup({headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),}, });
        $('#form-reset-password').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var pass1 = $("#password").val();
        var pass2 = $("#password-confirm").val();
        if(pass1==pass2){
          $.ajax({
          type:'POST',
          url:'/user-reset-password',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          success:function(data){
            if(data==200){
                $("#reset_content").hide();
                $("#main_check").show();
                $("#main_check_error").hide();
                $("#valid_password").hide();
                $("#main_check_error_validation").hide();
                return false;
            }  
            else if(data==500){
                $("#reset_content").hide();
                $("#main_check").hide();
                $("#main_check_error").show();
                $("#valid_password").hide();
                $("#main_check_error_validation").hide();
                return false;
            }
            else if(data==400){
                $("#reset_content").hide();
                $("#main_check").hide();
                $("#main_check_error_validation").show();
                $("#valid_password").hide();
                return false;
            }      
             },
          error: function(data){
            return false;
         }
        });
        }
        else {
            $("#valid_password").show();
            return false;
        }
      });

      $(".get-content").click(function (e) { 
        e.preventDefault();
        $("#password").val("");
        $("#password-confirm").val("");
        $("#reset_content").show();
        $("#main_check").hide();
        $("#main_check_error").hide();
        $("#valid_password").hide();
        $("#main_check_error_validation").hide();
      });
    });

    $("#password,#password-confirm").click(function (e) { 
        e.preventDefault();
        $("#valid_password").hide();
    });
  </script>
</body>

</html>