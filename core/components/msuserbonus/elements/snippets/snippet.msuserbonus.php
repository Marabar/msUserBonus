<?php
/** @var modX $modx */
/** @var msUserBonus $msUserBonus */
if (!$msUserBonus = $modx->getService('msuserbonus', 'msUserBonus', $modx->getOption('msuserbonus_core_path', null,
        $modx->getOption('core_path') . 'components/msuserbonus/') . 'model/msuserbonus/', $scriptProperties)
) {
    return;
}

if (!$modx->user->isAuthenticated($modx->context->key))
    return 0;

$output = isset($_SESSION['minishop2']['order']['bonus_cost'])
    ? $_SESSION['minishop2']['order']['bonus_cost']
    : 0;

$modx->regClientScript($msUserBonus->config['jsUrl'] . 'web/msuserbonus.js');
$modx->regClientHTMLBlock('<script>msUserBonus.initialize({ "actionUrl":"'
        . $msUserBonus->config['actionUrl'] . '"});</script>');

return $output;
