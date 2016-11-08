<?php

class msUserBonus
{
    /** @var modX $modx */
    public $modx;
    
    var $data = array();

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
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'jsUrl' => $assetsUrl . 'js/',
            'actionUrl' => $actionUrl,
            'connectorUrl' => $connectorUrl,

            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'processorsPath' => $corePath . 'processors/',
        ), $config);
        
        $this->modx->addPackage('msuserbonus', $this->config['modelPath']);
        $this->modx->lexicon->load('msuserbonus:default');
    }
    
    
    
    public function parseCart($action)
    {
        //if (!$this->modx->user->isAuthenticated($this->modx->context->key))
        //    return 0;
        
        $profile = $this->getCustomerProfile();
        if (!$profile || $profile->get('account') <= 0) {
            return '';
        }
        
        $this->data['action'] = $action['action'];
        $this->data['funded'] = $_SESSION['minishop2']['order']['bonus_cost'];
        $this->data['grant_bonus'] = $_SESSION['minishop2']['order']['msbonuscost'];
        $this->data['total_bonus'] = $_SESSION['minishop2']['order']['totalBonus'];
        $this->data['size_bonus'] = $_SESSION['minishop2']['order']['sizeBonus'];
        $this->data['tpl_input'] = $_SESSION['msUserBonus']['tplInput'];
        
        switch ($action['action']) {
            case 'act':
                $this->data['tpl'] = $this->getAjaxTpl($action['cost'], $_SESSION['msUserBonus']['tpl']);
                
                break;
            case 'input':
                $this->data['tpl_input'] = $this->getAjaxTpl($action['cost'], $_SESSION['msUserBonus']['tplInput']);
                
                break;
            case 'payment':
                if ($action['value'] != $this->getPaymentId()) {
                    $this->data['tpl'] = $this->getAjaxTpl($action['cost'], $_SESSION['msUserBonus']['tpl']);
                }
                
                break;
            case 'removeBonus':
                unset($_SESSION['minishop2']['order']['msbonuscost']);
                
                break;
            case 'mspack':
                //$oldPricePack = $this->getTotalPack($action['oldVal']);
                //$newPricePack = $this->getTotalPack($action['value']);
                
                //$totalCost = $action['cost'] - $oldPricePack + $newPricePack;
                $this->data['tpl'] = $this->getAjaxTpl($action['cost'], $_SESSION['msUserBonus']['tpl']);
                
                break;
        }
        
        return $this->success($this->data);
    }
    
    
    private function getAjaxTpl($cost, $tpl)
    {
        $grantBonus = $this->getGrandBonus($cost, $this->data['size_bonus']);
        $output = $this->modx->getChunk($tpl, array(
            'msbonus' => $this->calculateBonus($this->data['total_bonus'], $grantBonus),
        ));
        
        return $output;
    }
    
    
    // Получить общую стоимость корзины с доставкой, упаковкой
    public function getInitMinishop2()
    {
        $ms2 = $this->modx->getService('minishop2');
        $ms2->initialize($this->modx->context->key);
        $order = $ms2->order->get();
        $orderCostArr = $ms2->order->getCost(true, true);
        //$status = $ms2->cart->status();
        //$totalCart = $status['total_cost'];
        $idPack = empty($order['mspack']) ? 1 : $order['mspack'];
        $costCart = $orderCostArr + $this->getTotalPack($idPack);
        
        return $costCart;
    }
    
    
    public function getChunk($tplName)
    {
        $bonus = $this->getUserBonus();
        if ($bonus) {
            return $this->modx->getChunk($tplName, array(
                'msbonus' => $bonus,
            ));
        }
        
        return '';
    }
    
    // Получить стоимость упаковки
    public function getTotalPack($id)
    {
        $packPrice = 0;
        $pack = $this->modx->getObject('msPackList', array('id' => $id));
        if ($pack) {
            $packPrice = $pack->get('pack_price');
        }
        
        return $packPrice;
    }
    
    // Получить накопленный бонус пользователя
    public function getUserBonus()
    {
        $bonus = null;
        
        $sizeBonus = $this->getSizeBonus();
        $grantBonus = $this->getGrandBonus($this->getInitMinishop2(), $sizeBonus);

        $objBonus = $this->getCustomerProfile();
        if ($objBonus) {
            $totalBonus = $objBonus->get('account');
            $bonus = $this->calculateBonus($totalBonus, $grantBonus);

            $_SESSION['minishop2']['order']['totalBonus'] = $totalBonus;
            $_SESSION['minishop2']['order']['sizeBonus'] = $sizeBonus;
        }
        
        return $bonus;
    }
    
    
    public function getCustomerProfile()
    {
        $userId = $this->userGetId();
        
        if ($userId) {
            $profile = $this->modx->getObject('msCustomerProfile', array('id' => $this->userGetId()));
            if ($profile) {
                return $profile;
            } else {
                return false;
            }
        }
        return false;
    }
    
    
    private function calculateBonus($totalBonus, $grantBonus)
    {
        $bonus =  $totalBonus >= $grantBonus
            ? $grantBonus
            : $totalBonus;
        
        //$_SESSION['minishop2']['order']['msbonuscost'] = $bonus;
        
        return $bonus;
    }
    
    
    // Получить размер разрешённого бонуса
    public function getGrandBonus($cartCost, $sizeBonus)
    {
        return round($cartCost / 100 * $sizeBonus, 2);
    }


    // Получить разрешённый размер бонуса от итоговой суммы
    public function getSizeBonus()
    {
        return $this->modx->getOption('msuserbonus_size_bonus');
    }
    
    
    public function getPaymentId()
    {
        return $this->modx->getOption('msuserbonus_payment_id');
    }
    
    // Авторизован ли пользователь
    public function userAuth()
    {
        return $this->modx->user->isAuthenticated($this->modx->context->key);
    }
    
    // Получить ID пользователя
    public function userGetId()
    {
        return $this->modx->user->id;
    }
    
    
    public function errorMessage($text)
    {
        $message = $this->modx->getOption($text);
        return $this->modx->event->output($message);
    }
    
    
    public function success($data, array $success = array())
    {
        $success['success'] = true;
        $success['data'] = $data;
        
        return $this->modx->toJSON($success);
    }
}