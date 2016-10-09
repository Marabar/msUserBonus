var msUserBonus = {
    initialize: function(config)
    {
        $(document).on('click', 'button[name=ms2_action]', function(e) {
            var v = $(this).val();
            
            if (v == 'cart/add' || v == 'cart/chance' || v == 'cart/remove') {
                msUserBonus.send(config.actionUrl);
            }
        });
        
        $(document).on('change', '#msCart input[name=count]', function() {
            msUserBonus.send(config.actionUrl);
        });
        
        $( '#msCart input[name="count"]' ).change( function(){
                var id = '#' + $( this ).closest( 'tr' ).attr( 'id' );
                var count = $( this ).val(); 
                var price = parseFloat( $( id + ' .price-row' ).text().replace( /\s+/g, '' ) );

                $( id + ' .price-sum' ).html( count * price );
        });
    },
    
    send: function(action)
    {
        setTimeout(function() {
            $.ajax({
                type: 'POST',
                url: action,
                data: { action : 'act' },
                cache: false,
                success: function( data ) {
                    var total = data;
                    if (total) {
                        $('.msuserbonus').text(total);
                    }
                }
            });

        }, 1000);
    }
};

