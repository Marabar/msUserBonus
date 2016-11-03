<?php
define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
if (!$msUserBonus = $modx->getService('msuserbonus', 'msUserBonus', $modx->getOption('msuserbonus_core_path', null,
        $modx->getOption('core_path') . 'components/msuserbonus/') . 'model/msuserbonus/', $scriptProperties)
) {
    die();
}

if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
	$modx->sendRedirect($modx->makeUrl($modx->getOption('site_start'),'','','full'));
}
elseif (!empty($_REQUEST['action'])) {
	exit($msUserBonus->parseCart($_REQUEST['action']));
}
else {
    die();
}