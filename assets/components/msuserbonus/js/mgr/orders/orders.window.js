Ext.ComponentMgr.onAvailable('minishop2-window-order-update', function() {
    this.on('beforerender', function() {
        var bonusPayment = this.items.items[0].items.items[0].items.items[0].items.items[1].items.items[1];
        bonusPayment.insert(1, {
            //id: 'msuserbonus-bonus-payment',
            layout: 'form',
            name: 'bonus_payment',
            fieldLabel: _('msuserbonus_bonus_payment'),
            xtype: 'displayfield',
            url: msUserBonus.config['connector_url'],
            //value: 2222
            //baseParams: {
                action: 'mgr/orders/getlist',
            //    //order_id: config.id || 0
            //},
            style: 'font-size:1.1em;'
        });
        
        console.log(bonusPayment);
    });
});
/*
msUserBonus.window.Payment = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        //layout: 'form',
        name: 'bonus_payment',
        fieldLabel: _('msuserbonus_bonus_payment'),
        //value: 'bonus_payment',
        id: 'msuserbonus-bonus-payment',
        xtype: 'displayfield',
        url: msUserBonus.config['connector_url'],
        //baseParams: {
            action: 'mgr/orders/getlist',
        //    order_id: config.id || 0
        //},
        listeners: {
                select: function (combo, row) {
                    
                    console.log(combo, row);
                }
            }
    });
    console.log(config.url);
    msUserBonus.window.Payment.superclass.constructor.call(this, config);
};
Ext.extend(msUserBonus.window.Payment, Ext.form.DisplayField);
Ext.reg('msuserbonus-bonus-payment', msUserBonus.window.Payment);
*/