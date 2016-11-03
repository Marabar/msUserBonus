<?php
/** @var modX $modx */
/** @var array $sources */

$settings = array();

$tmp = array(
    /*'auth_user' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'msuserbonus_main',
    ),*/
    'payment_id' => array(
        'xtype' => 'textfield',
        'value' => 4,
        'area' => 'msuserbonus_main',
    ),
    'size_bonus' => array(
        'xtype' => 'textfield',
        'value' => 50,
        'area' => 'msuserbonus_main',
    ),
    'err_sum_bonus' => array(
        'xtype' => 'textfield',
        'value' => 'На Вашем счету не достаточно бонусов, пожалуйста исправьте и повторите ещё раз.',
        'area' => 'msuserbonus_main',
    ),
    'err_bonus' => array(
        'xtype' => 'textfield',
        'value' => 'Вы пытаетесь оплатить заказ бонусами полностью - выберите способ оплаты БОНУСНАЯ СИСТЕМА.',
        'area' => 'msuserbonus_main',
    ),
    'number' => array(
        'xtype' => 'numberfield',
        'value' => 1000,
        'area' => 'msuserbonus_main',
    )
);

foreach ($tmp as $k => $v) {
    /** @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => 'msuserbonus_' . $k,
            'namespace' => PKG_NAME_LOWER,
        ), $v
    ), '', true, true);

    $settings[] = $setting;
}
unset($tmp);

return $settings;
