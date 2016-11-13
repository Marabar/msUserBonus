<?php

if (!$msUserBonus = $modx->getService('msuserbonus', 'msUserBonus', $modx->getOption('msuserbonus_core_path', null,
        $modx->getOption('core_path') . 'components/msuserbonus/') . 'model/msuserbonus/', $scriptProperties)
) {
    return;
}
switch ($modx->event->name) {
    case 'OnMODXInit':
        $map = array(
            'msProductData' => array(
                'fields' => array(
                    'cost_price' => '',
                    'purchase_price' => 0,
                ),
                'fieldMeta' => array(
                    'cost_price' => array (
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => '',
                    ),
                    'purchase_price' => array (
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0,
                    ),
                ),
            ),
            'msOrder' => array(
                'fields' => array(
                    'bonus_cost' => 0,
                    'bonus_payment' => 0,
                    'bonus_purchase' => 0,
                ),
                'fieldMeta' => array(
                    'bonus_cost' => array (
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0,
                    ),
                    'bonus_payment' => array(
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0,
                    ),
                    'bonus_purchase' => array(
                        'dbtype' => 'decimal',
                        'precision' => '12,2',
                        'phptype' => 'float',
                        'null' => true,
                        'default' => 0,
                    ),
                ),
            )
        );
        
        foreach ($map as $class => $data) {
            $modx->loadClass($class);
            foreach ($data as $tmp => $fields) {
                if ($tmp == 'fields') {
                    foreach ($fields as $field => $value) {
                        foreach (array(
                            'fields',
							'fieldMeta',
							'indexes',
							'composites',
							'aggregates',
							'fieldAliases',
							) as $key) {
                            if (isset($data[$key][$field])) {
								$modx->map[$class][$key][$field] = $data[$key][$field];
							}
                        }
                    }
                }
            }
            
        }
        
        break;
        
    case 'OnDocFormPrerender':
        if ($resource->class_key == 'msProduct'){
            $obj = $modx->getObject('msProductData', array('id' => $resource->id));
            if ($obj) {
                $data['cost_price'] = $obj->get('cost_price');
                $data['purchase_price'] = $obj->get('purchase_price');
            }
            
            $modx->controller->addHtml("
                <script type='text/javascript'>
                    Ext.ComponentMgr.onAvailable('minishop2-product-tabs', function() {
                        this.on('beforerender', function() {
                            var costField = this.items.items[2].items.items[0].items.items[1];
                            var purchaseField = this.items.items[2].items.items[0].items.items[0];
                            costField.items.insert(1, 'modx-msuserbonus-cost-price', new Ext.form.DisplayField({
                                id: 'modx-msuserbonus-cost-price',
                                cls: 'x-form-text',
                                width: '100%',
                                name: 'cost_price',
                                fieldLabel: 'Прибыль',
                                description: '<b>[[*cost_price]]</b><br />Рассчитывается автоматически',
                                xtype: 'numberfield',
                                value: '{$data['cost_price']}'
                            }));
                            
                            purchaseField.items.insert(1, 'modx-msuserbonus-purchase-price', new Ext.form.NumberField({
                                id: 'modx-msuserbonus-purchase-price',
                                width: '100%',
                                name: 'purchase_price',
                                fieldLabel: 'Цена закупочная',
                                description: '<b>[[*purchase_price]]</b><br />Нужно указать, чтобы получить себестоимость. Если поле не заполнено, себестоимость будет не определена.',
                                xtype: 'numberfield',
                                value: '{$data['purchase_price']}'
                            }));
                        });
                    });
                </script>
            ");
        }
        
        break;
        
    case 'OnDocFormSave':
        $purchase = 0;
        
        if ($resource->class_key == 'msProduct'){
            $obj = $modx->getObject('msProductData', array('id' => $id));
            if ($obj) {
                $purchase = $obj->get('purchase_price');
                $cost = $obj->get('cost_price');
                $price = $obj->get('price');
            }
            if ($purchase > 0 && $price > 0) {
                $newCost = $price - $purchase;
                if ($cost != $newCost) {
                    $obj->set('cost_price', $newCost);
                    $obj->save();
                }
            }
        } else {
            return;
        }
        
        break;
        
    case 'OnManagerPageBeforeRender':
        if ($_GET['a'] != 'mgr/orders' && $_GET['namespace'] != 'minishop2') {
            return;
        }

        $modx->regClientStartupScript($msUserBonus->config['jsUrl'] . 'mgr/msuserbonus.js');
        $modx->regClientStartupScript($msUserBonus->config['jsUrl'] . 'mgr/orders/orders.grid.js');
        $modx->regClientStartupScript($msUserBonus->config['jsUrl'] . 'mgr/orders/orders.window.js');
        
        break;

    case 'msOnSubmitOrder';
        $arrOrder = $order->get();
        $priceOrder = $order->getCost(true, true);
        $pricePack = $msUserBonus->getTotalPack($arrOrder['mspack']);
        //$modx->log(1, $modx->event->name . ' ' . print_r($pricePack, 1));
        $cost = $priceOrder + $pricePack;
        $profile = $msUserBonus->getCustomerProfile();
        
        if ($profile && $profile->get('account') < $arrOrder['msbonuscost']) {
            
            return $msUserBonus->errorMessage('msuserbonus_err_sum_bonus');
        } else if ($cost == $arrOrder['msbonuscost']) {
                    
            return $msUserBonus->errorMessage('msuserbonus_err_bonus');
        }
        
        break;

    case 'msOnChangeOrderStatus':
        if (empty($status)) { return; }
        if (!$profile = $order->getOne('CustomerProfile')) { return; }
        
        switch ($status) {
            case 2:
                if ($order->get('bonus_cost') > 0 || $order->get('payment') != 4) {
                    $profile->set('account', $profile->get('account') + $order->get('bonus_cost'));
                    $profile->save();
                }
                if ($order->get('bonus_payment') > 0) {
                    $profile->set('spent', $profile->get('spent') + $order->get('bonus_payment'));
                    $profile->save();
                }
                
                break;

            case 4:
                if ($order->get('bonus_payment') > 0) {
                    $profile->set('account', $profile->get('account') + $order->get('bonus_payment'));
                    $profile->save();
                    
                    $order->set('bonus_payment', 0.00);
                    $order->save();
                }

                break;
        }
        
        break;

    case 'msOnAddToCart':
    case 'msOnChangeInCart':
    case 'msOnRemoveFromCart';
        $tmp = $cart->get();
        $totalCostPrice = 0;
        $totalPurchasePrice = 0;

        foreach ($tmp as $cartProduct) {
            if ($product = $modx->getObject('msProduct', $cartProduct['id'])) {
                if ($costPrice = $product->get('cost_price')) {
                    $totalCostPrice += $costPrice * $cartProduct['count'];
                }
                // Себестоимость
                if ($puchasePrice = $product->get('purchase_price')) {
                    $totalPurchasePrice += $puchasePrice * $cartProduct['count'];
                }
            }
        }
        $number = $modx->getOption('msuserbonus_number');
        $total = $totalCostPrice - $number;
        
        if ($total > 0) {
            $_SESSION['minishop2']['order']['bonus_cost'] = $total;
        } else {
            $_SESSION['minishop2']['order']['bonus_cost'] = 0;
        }

        if ($totalPurchasePrice > 0) {
            $_SESSION['minishop2']['order']['bonus_purchase'] = $totalPurchasePrice;
        } else {
            $_SESSION['minishop2']['order']['bonus_purchase'] = 0;
        }
        
        break;
        
    case 'msOnCreateOrder':
        //if (!$modx->user->isAuthenticated($modx->context->key))
        //    return;
        
        $arrOrder = $order->get();
        if ($arrOrder['bonus_cost'] > 0) {
            $msOrder->set('bonus_cost', $arrOrder['bonus_cost']);
            $msOrder->save();
        }
        if ($arrOrder['bonus_purchase']) {
            $msOrder->set('bonus_purchase', $arrOrder['bonus_purchase']);
            $msOrder->save();
        }
        if ($arrOrder['msbonuscost']) {
            if ($obj = $msOrder->getOne('CustomerProfile')) {
                $obj->set('account', $obj->get('account') - $arrOrder['msbonuscost']);
                $obj->save();

                $msOrder->set('bonus_payment', $arrOrder['msbonuscost']);
                $msOrder->set('cost', $msOrder->get('cost') - $arrOrder['msbonuscost']);
                $msOrder->save();
            }
        }
        
        break;
}
