<?php

namespace Emailcenter\Maxautomation\Plugin;

use Emailcenter\Maxautomation\MxaApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;

class SubscriberPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var MxaApi
     */
    private $api;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getEnabledConfigValue()
    {
        return $this->scopeConfig->getValue('emailcenter_maxautomation/general/enabled',
            ScopeInterface::SCOPE_STORE);
    }

    public function afterSubscribe(Subscriber $email)
    {
        if ($this->getEnabledConfigValue() == true) {
            if ($email->isStatusChanged() && $email->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                $this->getMxaApi()->sendContact($email->getId(), $email->getEmail());
            }
        }
        return true;
    }

    public function afterConfirm(Subscriber $code)
    {
        if ($this->getEnabledConfigValue() == true) {
            $this->getMxaApi()->sendContact($code->getId(), $code->getEmail());
        }
        return true;
    }

    private function getMxaApi(): MxaApi
    {
        if ($this->api === null) {
            $token = $this->scopeConfig->getValue('emailcenter_maxautomation/general/api_key',
                ScopeInterface::SCOPE_STORE);
            $this->api = new MxaApi($token);
        }

        return $this->api;
    }

    public function setMxaApi(MxaApi $mxaApi)
    {
        $this->api = $mxaApi;
        return $this;
    }
}
