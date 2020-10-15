jQuery(document).ready( function($) {

   $("#ses-enable-logs").on('click', function(_evt){

      _evt.preventDefault(); 

      $.ajax({
         type : "post",
         dataType : "json",
         url : sasm_admin.ajaxurl,
         data : {
            action: "sasm_enable_logs", 
            nonce: sasm_admin.nonce
         },
         success: function(response) {

            alert(response.message);
            location.reload();

         }
      });   

   });

   $("#ses-send-test-email").on('click', function(_evt){

      _evt.preventDefault(); 

      $.ajax({
         type : "post",
         dataType : "json",
         url : sasm_admin.ajaxurl,
         data : {
            action: "sasm_send_test", 
            nonce: sasm_admin.nonce
         },
         success: function(response) {

            alert(response.message);
            location.reload();

         }
      });   

   });

   $("#ses-clear-logs").on('click', function(_evt){

      _evt.preventDefault(); 

      $.ajax({
         type : "post",
         dataType : "json",
         url : sasm_admin.ajaxurl,
         data : {
            action: "ssasm_clear_logs", 
            nonce: sasm_admin.nonce
         },
         success: function(response) {

            alert(response.message);
            location.reload();

         }
      });   

   });

});