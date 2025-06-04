$(document).ready(function () {

    var firebaseConfig = {
      apiKey: "AIzaSyA70MvUaqXQWYnc6-inXGsQzHeNF7svrcA",
      authDomain: "cafeapp-6964b.firebaseapp.com",
      projectId: "cafeapp-6964b",
      storageBucket: "cafeapp-6964b.appspot.com",
      messagingSenderId: "100680290556",
      appId: "1:100680290556:web:f1d4d23c8618404c614779",
      measurementId: "G-H5HQ281EMT"

  };
  // measurementId: G-R1KQTR3JBN
    // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  const messaging = firebase.messaging();

  function initFirebaseMessagingRegistration() {
          messaging
          .requestPermission()
          .then(function () {
              return messaging.getToken()
          })
          .then(function(token) {
              console.log(token);

              $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });

              $.ajax({
                  url: '{{ route("save-token") }}',
                  type: 'POST',
                  data: {
                      token: token
                  },
                  dataType: 'JSON',
                  success: function (response) {
                      alert('Token saved successfully.');
                  },
                  error: function (err) {
                      console.log('User Chat Token Error'+ err);
                  },
              });

          }).catch(function (err) {
              toastr.error('User Chat Token Error'+ err, null, {timeOut: 3000, positionClass: "toast-bottom-right"});
          });
  }  
  messaging.onMessage(function(payload) {
      const noteTitle = payload.notification.title;
      const noteOptions = {
          body: payload.notification.body,
          icon: payload.notification.icon,
      };
      new Notification(noteTitle, noteOptions);
  });


  $.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
  });
  var user_id ;

  $.ajax({
    type: "POST",
    url: "/user-view-data",
    success: function (response) {
        var table = '';
        $(".user-table tbody").empty();
        $.each(response, function (indexInArray, valueOfElement) { 
             table += '<tr>'+
             '<td class="text-center">'+ valueOfElement.id+ '</td>'+
             '<td class="text-center">'+ valueOfElement.name+ '</td>'+
             '<td class="text-center">'+ valueOfElement.email+ '</td>'+
             '<td class="text-center">'+ valueOfElement.phone_no+ '</td>';

             if(valueOfElement.document_name){
              table += '<td class="text-center"><span class="badge bg-success pointer get-id-type" data-id="'+ valueOfElement.id+'" >'+ valueOfElement.document_name+' <span> </td>';
             }
              else{
              table += '<td class="text-center"><span class="badge bg-danger pointer" data-id="'+ valueOfElement.id+'">ID not available<span> </td>';
             }

             if(valueOfElement.active==1){
              table += '<td class="text-center"><span class="badge bg-success pointer change_user_active" data-id="'+ valueOfElement.id+'" >Enabled <span> </td>';
             }
               if(valueOfElement.active==0){
              table += '<td class="text-center"><span class="badge bg-danger pointer change_user_active" data-id="'+ valueOfElement.id+'" >Disabled <span> </td>';
             }
             table += '<td class="text-center"><span style="color:green;" data-bs-toggle="modal" " data-id="'+ valueOfElement.id+'" class="bi bi-bell-fill user-open-modal"></span> &nbsp;&nbsp;<span style="color:darkblue;" data-id="'+ valueOfElement.id+'"class="icon ri-edit-2-fill user-edit"></span> &nbsp; &nbsp;<span style="color:red;" data-id="'+ valueOfElement.id+'" class="icon ri-chat-delete-fill user-delete"></span> </td>'
             '</tr>';
        });
          $(".user-table tbody").append(table);
          $('.user-table').DataTable({               
            destroy:true,
            processing:true,
            select:true,
            paging:true,
            lengthChange:true,
            searching:true,
            info:false,
            responsive:true,
            autoWidth:false
        });
      }
    });

     $('#user-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
          type:'POST',
          url:'/user-store-data',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          success:function(data){
            if(data['flag']=='Y'){
              $(".table_msg1").show();
              $('.table_msg1').delay(1200).fadeOut();
              $('#table-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            }
            else if(data['flag']=='N'){
              $(".table_msg2").show();
              $('.table_msg2').delay(1200).fadeOut();
              $('#table-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            }
            else if(data['flag']=='VE'){
              $(".table_msg2").show();
              $('.table_msg2').delay(1200).fadeOut();
              $('#table-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
            else if(data['flag']=='YY'){
              $(".table_msg5").show();
              $('.table_msg5').delay(1200).fadeOut();
              $('#table-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
            else if(data['flag']=='NN'){
              $(".table_msg6").show();
              $('.table_msg6').delay(1200).fadeOut();
              $('#table-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
          },
          error: function(data){
            
         }
        });
      });
      
      $(document).on('click','.user-edit',function (e) { 
        e.preventDefault();
        var id = $(this).data('id');
        var value;
        $.ajax({
          type: "POST",
          url: "/user-show-data",
          data: {id},
          cache:false,
          success:function(data){
            $('#user-form').trigger("reset");
            $("#id").val(data[0]['id']);
            $("#name").val(data[0]['name']);
            $("#email").val(data[0]['email']);
            $("#phone_no").val(data[0]['phone_no']);
            $("#document_type").val(data[0]['document_id']);
            $("#active").prop("checked",data[0]['active']);
            $("#users-tab").tab('show');
          }
        });
      });

      $(document).on('click','.user-delete-data',function (e) { 
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/delete-data",
          data: {id},
          cache:false,
          success:function(data){
            if(data['flag']=='Y'){
              $(".table_msg3").show();
              $('.table_msg3').delay(1200).fadeOut();
              location.reload();
            }
            else {
              $(".table_msg4").show();
              $('.table_msg4').delay(1200).fadeOut();
              location.reload();
            }
          }
        });
      });

      
      $(document).on('click','.get-id-type',function (e) { 
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/user-get-id-type",
          data: {id},
          cache:false,
          success:function(data){
              $('.get-document-name').html(data[0]['document_name']) ;  
              $('.get-user-name').html(data[0]['name']) ;                  
              $("#userdocument_modal").modal('show');
              var url = data[0]['path_file'];
              $('.document_path').attr("src",url);
          }
        });
      });

      $(document).on('click','.change_user_active',function (e) { 
        e.preventDefault();
        var id = $(this).data('id');
       if(confirm("Do you want to change it's status ?")){
        $.ajax({
          type: "POST",
          url: "user-change-active",
          data: {id},
          success: function (response) {
            if(response['flag']=='Y'){
              $(".table_msg7").show();
              $('.table_msg7').delay(1200).fadeOut();
              location.reload();
              return false;
            }
            else if(response['flag']=='N'){
              $(".table_msg7").show();
              $('.table_msg7').delay(1200).fadeOut();
              location.reload();
              return false;
            }
            else {
              $(".table_msg4").show();
              $('.table_msg4').delay(1200).fadeOut();
              location.reload();
              return false;
            }
          }
        });
       }
       else {
        return false;
       }

      });

      $(document).on('click','.user-open-modal',function (e) { 
        e.preventDefault();
         user_id = $(this).data('id');
        $("#disablebackdrop").modal('show');
      });

      $("#btn-send-user-notification").click(function(e){
        e.preventDefault();
        var title = $("#title").val();
        var body = $("#body").val();
        if(title==''){
          $(".msg1").show();
          return false;
        }
        else if(body==''){
          $(".msg2").show();
          return false;
        }
        else {
          $.ajax({
            type: "POST",
            url: "send-user-notification",
            cache:false,
            data: {title,body,user_id},
            success: function (response) {
              console.log(response)
              if(response['flag']=='VE'){
                $(".msg6").show();
                $('.msg6').delay(1200).fadeOut();
                $("#title").val("");
                $("#body").val("");      
              }
              else if(response['flag']=='A'){
                $(".msg3").show();
                $('.msg3').delay(1200).fadeOut();
                $("#title").val("");
                $("#body").val("");
              }
              else if(response['flag']=='F'){
                $(".msg4").show();
                $('.msg4').delay(1200).fadeOut();
                $("#title").val("");
                $("#body").val("");             
              }
              else if(response['flag']=='X'){
                $(".msg5").show();
                $('.msg5').delay(1200).fadeOut();
                $("#title").val("");
                $("#body").val("");                
              } 
            }
          });
        }
      });

      $("#title").click(function(e){
        $(".msg1").hide();
      });

      $("#body").click(function(e){
        $(".msg2").hide();
      });
  });