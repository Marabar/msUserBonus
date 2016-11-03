var msUserBonus = function (config) {
    config = config || {};
    msUserBonus.superclass.constructor.call(this, config);
};
Ext.extend(msUserBonus, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, keymap: {}, plugin: {},
});
Ext.reg('msuserbonus', msUserBonus);

msUserBonus = new msUserBonus();