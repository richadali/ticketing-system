$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    
    $.ajax({
      type: "POST",
      url: "/view-data",
      success: function (response) {
          var table = '';
          $(".table tbody").empty();
          $.each(response, function (indexInArray, valueOfElement) { 
               table += '<tr>'+
               '<td class="text-center">'+ valueOfElement.id+ '</td>'+
               '<td class="text-center">'+ valueOfElement.table_name+ '</td>'+
               '<td class="text-center">'+ valueOfElement.seat+ '</td>'+
               '<td class="text-center">₹'+ valueOfElement.price+ '</td>';
               if(valueOfElement.active==1){
                table += '<td class="text-center"><span class="badge bg-success pointer change_table_active" data-id="'+ valueOfElement.id+'" >Yes <span> </td>';
               }
               else  if(valueOfElement.active==0){
                table += '<td class="text-center"><span class="badge bg-danger pointer change_table_active" data-id="'+ valueOfElement.id+'" >No <span> </td>';
               }
               table += '<td class="text-center"><span style="color:darkblue;" data-id="'+ valueOfElement.id+'"class="icon ri-edit-2-fill table-edit"></span> &nbsp; &nbsp;<span style="color:red;" data-id="'+ valueOfElement.id+'" class="icon ri-chat-delete-fill table-delete"></span></td>'
               '</tr>';
          });
          
          $(".table tbody").append(table);
          $('.table').DataTable({               
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

    $(".table_msg1,.table_msg2,.table_msg3,.table_msg4").hide();

    $('#table-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
          type:'POST',
          url:'/store-data',
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
      
      $(document).on('click','.table-edit',function (e) { 
        e.preventDefault();
        var table_id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/show-data",
          data: {table_id},
          cache:false,
          success:function(data){
            $('#table-form').trigger("reset");
            $("#id").val(data[0]['id']);
            $("#table_name").val(data[0]['table_name']);
            $("#seat").val(data[0]['seat']);
            $("#price").val(data[0]['price']);
            $("#desc").val(data[0]['desc']);
            $("#active").prop("checked",data[0]['active']);
            $('#table_imgs').attr('src',data[0]['path_file']);
            $("#has_image").show();
            $("#tables-tab").tab('show');
          }
        });
      });

      $(document).on('click','.table-delete',function (e) { 
        e.preventDefault();
        var table_id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/delete-data",
          data: {table_id},
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

      $(document).on('click','.change_table_active',function (e) { 
        e.preventDefault();
        var table_id = $(this).data('id');
       if(confirm("Do you want to change it's status ?")){
        $.ajax({
          type: "POST",
          url: "change-active",
          data: {table_id},
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
      
     
});