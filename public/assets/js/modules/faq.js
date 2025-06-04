$(document).ready(function () {
  $.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

    $.ajax({
        type: "POST",
        url: "/faq-view-data",
        success: function (response) {
            var table = '';
            $(".faq_table tbody").empty();
            $.each(response, function (indexInArray, valueOfElement) { 
                 table += '<tr>'+
                 '<td class="text-center">'+ valueOfElement.id+ '</td>'+
                 '<td class="text-center">'+ valueOfElement.question+ '</td>'+
                 '<td class="text-center">'+ valueOfElement.answer+ '</td>'+
                 '<td class="text-center"><span style="color:darkblue;" data-id="'+ valueOfElement.id+'"class="icon ri-edit-2-fill faq-edit"></span> &nbsp; &nbsp;<span style="color:red;" data-id="'+ valueOfElement.id+'" class="icon ri-chat-delete-fill faq-delete"></span></td>'
                 '</tr>';
            });
            $(".faq_table tbody").append(table);
            $('.faq_table').DataTable({               
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
    
    $('#faq-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
          type:'POST',
          url:'/faq-store-data',
          data: formData,
          cache:false,
          contentType: false,
          processData: false,
          success:function(data){
            if(data['flag']=='Y'){
              $(".table_msg1").show();
              $('.table_msg1').delay(1200).fadeOut();
              $('#faq-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            }
            else if(data['flag']=='N'){
              $(".table_msg2").show();
              $('.table_msg2').delay(1200).fadeOut();
              $('#faq-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            }
            else if(data['flag']=='VE'){
              $(".table_msg2").show();
              $('.table_msg2').delay(1200).fadeOut();
              $('#faq-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
            else if(data['flag']=='YY'){
              $(".table_msg5").show();
              $('.table_msg5').delay(1200).fadeOut();
              $('#faq-form').trigger("reset");
              setTimeout(function(){
                window.location.reload();
             }, 3000);
            } 
            else if(data['flag']=='NN'){
              $(".table_msg6").show();
              $('.table_msg6').delay(1200).fadeOut();
              $('#faq-form').trigger("reset");
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

      $(document).on('click','.faq-edit',function (e) { 
        e.preventDefault();
        var faq_id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/faq-show-data",
          data: {faq_id},
          cache:false,
          success:function(data){
            $('#slider-form').trigger("reset");
            $("#id").val(data[0]['id']);
            $("#question").val(data[0]['question']);
            $("#answer").val(data[0]['answer']);
            $("#faqs-tab").tab('show');
          }
        });
      });

    

      $(document).on('click','.faq-delete',function (e) { 
        e.preventDefault();
        var faq_id = $(this).data('id');
        $.ajax({
          type: "POST",
          url: "/faq-delete-data",
          data: {faq_id},
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

     
});