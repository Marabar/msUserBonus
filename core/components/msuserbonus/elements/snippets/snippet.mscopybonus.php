<?php

if (!$msUserBonus = $modx->getService('msuserbonus', 'msUserBonus', $modx->getOption('msuserbonus_core_path', null,
        $modx->getOption('core_path') . 'components/msuserbonus/') . 'model/msuserbonus/', $scriptProperties)
) {
    return '';
}

$tpl = $modx->getOption('tpl', $scriptProperties, 'msCopyBonusTpl');
$tplInput = $modx->getOption('tplInput', $scriptProperties, 'msInputBonusTpl');

$paymentId = $modx->getOption('msuserbonus_payment_id');
$orderPayment = $_SESSION['minishop2']['order']['payment'];
$output = '';

if (!$msUserBonus->userAuth()) {
    return '';
}

if ($paymentId == $orderPayment) {
    return '';
}

$profile = $msUserBonus->getCustomerProfile();
if (!$profile || $profile->get('account') <= 0) {
    return '';
}

if (!isset($_SESSION['msUserBonus'])) {
    $_SESSION['msUserBonus'] = array();
}

$record = array(
    'tpl' => $tpl,
    'tplInput' => $tplInput,
);

if ($_SESSION['msUserBonus'] !== $record) {
    $_SESSION['msUserBonus'] = $record;
}

$output = $msUserBonus->getChunk($tpl);

return $output;
