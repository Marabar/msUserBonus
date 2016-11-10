var msUserBonus = {
    initialize: function(config)
    {
        actionPath = config.actionUrl;
        totalCost = 0;
        var payment = config.payment;
        selector = {
            changeCount: '#msCart input[name=count]',
            blockBonus: '#msBonusBlock',
            fieldInput: '#msBonusInput',
            chekBonus: '#ms-copy-bonus',
            packPrice: '#mspack-price',
            rowBonus: '#msBonus-row'
        };
        
        miniShop2.Callbacks.add('Order.getcost.response.success', 'bonus_get', function(response) {
            
            totalCost = response['data']['cost'] + msUserBonus.getPackPrice();
            checkedPack = $('input[name=mspack]:checked').val();
            checkedPackPrice = $('input[name=mspack]:checked').data('mspack');
        });
        
        $(document).on('click', 'button[name=ms2_action]', function()
        {
            var v = $(this).val();
            
            if (v == 'cart/add' || v == 'cart/chance' || v == 'cart/remove')
            {
                msUserBonus.send({
                    action:'act',
                    cost: totalCost
                });
            }
        });
        
        $(document).on('change', selector.changeCount, function()
        {
            var id = '#' + $( this ).closest( 'tr' ).attr( 'id' );
            var count = parseInt($( this ).val()); 
            var price = parseFloat( $( id + ' .price-row' ).text().replace( /\s+/g, '' ) );

            $( id + ' .price-sum' ).html(count * price);
            
            msUserBonus.send({
                action:'act',
                cost: totalCost
            });
        });
        
        $(document).on('change', selector.chekBonus, function()
        {
            if ($(selector.chekBonus).prop("checked")) {
                msUserBonus.send({
                    action:'input',
                    cost: totalCost
                });
            } else {
                msUserBonus.send({
                    action:'removeBonus'
                });
                msUserBonus.remove(selector.fieldInput);
            }
        });
        
        $(document).on('change', 'input[name=payment]', function()
        {
            var paymentId = $(this).val();
            
            if (payment == paymentId) {
                msUserBonus.remove(selector.blockBonus);
            }
                
            msUserBonus.send({
                action: 'payment',
                value: paymentId,
                cost: totalCost
            });
        });
        
        $(document).on('change', 'input[name=mspack]', function()
        {
            var msPack = $(this).val();
            var newPackPrice = $(this).data('mspack');
            totalCost = totalCost - checkedPackPrice + newPackPrice;
            
            msUserBonus.send({
                action: 'mspack',
                value: msPack,
                oldVal: checkedPack,
                cost: totalCost
            });
            
            checkedPack = msPack;
            checkedPackPrice = newPackPrice;
        });
    },
    
    send: function(value)
    {
        setTimeout(function() {
            $.ajax({
                type: 'POST',
                url: actionPath,
                data: { action : value },
                dataType: 'json',
                cache: false,
                success: function( data ) {
                    if (data['success'] == true) {
                        var response = data['data'];
                        msUserBonus.set(response);
                    }
                }
            });
        }, 1000);
    },
    
    getPackPrice: function()
    {
        return parseFloat($(selector.packPrice).text());
    },
    
    set: function(response)
    {
        switch (response['action']) {
            case 'act':
                $('.msuserbonus').text(response['funded']);
                if (response['tpl']) {
                    $(selector.blockBonus).replaceWith(response['tpl']);
                }
                
                break;
            case 'input':
                $(selector.blockBonus).append(response['tpl_input']);
                
                break;
            case 'payment':
                if ($(selector.blockBonus).length) {
                    $(selector.blockBonus).replaceWith(response['tpl']);
                } else {
                    $(selector.rowBonus).html(response['tpl']);
                }
                
                break;
            case 'mspack':
                $(selector.blockBonus).replaceWith(response['tpl']);
                
                break;
        }
        
        //return;
    },
    
    remove: function(selector)
    {
        $(selector).remove();
    }
};

