Ext.ComponentMgr.onAvailable('minishop2-window-order-update', function() {
    this.on('beforerender', function() {
        var bonusPpurchase = this.items.items[0].items.items[0].items.items[0].items.items[1].items.items[1];
        bonusPpurchase.insert(1, {
            id: 'msuserbonus-bonus-purchase',
            layout: 'form',
            name: 'bonus_purchase',
            fieldLabel: _('msuserbonus_bonus_purchase'),
            xtype: 'displayfield',
            value: msUserBonus.bonus_purchase,
            style: 'font-size:1.1em;'
        });
        
        console.log(msUserBonus.bonus_purchase);
    });
});