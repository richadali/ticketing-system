$(document).ready(function () {
  $.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    });

    $.ajax({
        type: "POST",
        url: "/slider-view-data",
        success: function (response) {
            var table = '';
            $(".slider_table tbody").empty();
            $.each(response, function (indexInArray, valueOfElement) { 
                 table += '<tr>'+
                 '<td class="text-center">'+ valueOfElement.id+ '</td>'+
                 '<td class="text-center">'+ valueOfElement.slider_name+ '</td>';
                 if(valueOfElement.active==1){
                  table += '<td class="text-center"><span class="badge bg-success pointer change_slider_active" data-id="'+ valueOfElement.id+'" >Yes <span> </td>';
                 }
                 else  if(valueOfElement.active==0){
                  table += '<td class="text-center"><span class="badge bg-danger pointer change_slider_active" data-id="'+ valueOfElement.id+'" >No <span> </td>';
                 }
                 table += '<td class="text-center"><span style="color:darkblue;" data-id="'+ valueOfElement.id+'"class="icon ri-edit-2-fill slider-edit"></span> &nbsp; &nbsp;<span style="color:red;" data-id="'+ valueOfElement.id+'" class="icon ri-chat-delete-fill slider-delete"></span></td>'
                 '</tr>';
            });
            $(".slider_table tbody").append(table);
            $('.slider_table').DataTable({               
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
    
    $('#slider-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
          type:'POST',
          url:'/slider-store-data',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          success:function(data){
            if(data['flag']=='Y'){
              $(".table_msg1").show();
              $('.table_msg1').delay(1200).fadeOut();
              $('#slider-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            }
            else if(data['flag']=='N'){
              $(".table_msg2").show();
              $('.table_msg2').delay(1200).fadeOut();
              $('#slider-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            }
            else if(data['flag']=='VE'){
              $(".table_msg2").show();
              $('.table_msg2').delay(1200).fadeOut();
              $('#slider-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
            else if(data['flag']=='YY'){
              $(".table_msg5").show();
              $('.table_msg5').delay(1200).fadeOut();
              $('#slider-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
            else if(data['flag']=='NN'){
              $(".table_msg6").show();
              $('.table_msg6').delay(1200).fadeOut();
              $('#slider-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
          },
          error: function(data){
            alert("System Error"); return false;
         }
        });
      });

      $(document).on('click','.slider-edit',function (e) { 
        e.preventDefault();
        var slider_id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/slider-show-data",
          data: {slider_id},
          cache:false,
          success:function(data){
            $('#slider-form').trigger("reset");
            $("#id").val(data[0]['id']);
            $("#slider_name").val(data[0]['slider_name']);
            $("#active").prop("checked",data[0]['active']);
            $('#sliders_img').attr('src',data[0]['path_file']);
            $("#has_image").show();
            $("#sliders-tab").tab('show');
          }
        });
      });

      $(document).on('click','.change_slider_active',function (e) { 
        e.preventDefault();
        var slider_id = $(this).data('id');
       if(confirm("Do you want to change it's status ?")){
        $.ajax({
          type: "POST",
          url: "slider-change-active",
          data: {slider_id},
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

      $(document).on('click','.slider-delete',function (e) { 
        e.preventDefault();
        var slider_id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/slider-delete-data",
          data: {slider_id},
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

      $('#slider_img,#table_img,#item_img,#food_img').on('change', function() {
        var fileName = $(this).val();
        if (fileName.length > 0) {
          $("#has_image").hide();
        }
      });
});