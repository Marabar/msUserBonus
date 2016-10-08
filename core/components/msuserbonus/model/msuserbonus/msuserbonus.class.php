<?php

class msUserBonus
{
    /** @var modX $modx */
    public $modx;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('msuserbonus_core_path', $config,
            $this->modx->getOption('core_path') . 'components/msuserbonus/'
        );
        $assetsUrl = $this->modx->getOption('msuserbonus_assets_url', $config,
            $this->modx->getOption('assets_url') . 'components/msuserbonus/'
        );
        $actionUrl = $assetsUrl . 'action.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'actionUrl' => $actionUrl,

            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'snippetsPath' => $corePath . 'elements/snippets/',
        ), $config);
    }
    
    
    
    public function parseCart($action)
    {
        return $action == 'act'
            ? $_SESSION['minishop2']['order']['bonus_cost']
            : false;
    }
}