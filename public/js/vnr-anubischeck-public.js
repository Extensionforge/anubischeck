(function( $ ) {
	'use strict';
	  
	  $( window ).load(function() { 
	  
	  	$("#vnr_enter_customernr_submit").click(function(){
 			 
		var customernr = $('#vnr_enter_customernr').val();
        var stripped = customernr.replace(/[^0-9 -]/g, '');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'vnr_save_kdnr',
                customernr: stripped    
            },  
           success: function(data) {   

                          $('#vnr_savedkdnr').html('Kundennummer gespeichert.<br /><b>'+stripped+'</b>');
                          $('#vnr_enter_customernr').val(stripped);
                          $("#vnr_savedkdnr").delay(3000).fadeOut(2000); 
                         

                          setTimeout(function() { 
         location.reload();
    }, 2000);

                          
                             },
                          error: function() {
                            alert('There was some error saving(Maybe Buddypress missing?)!');
                          }
        });
		}); 
	 	  });
	 
})( jQuery );