<?php

namespace Emailcenter\Maxautomation\Plugin;

use Emailcenter\Maxautomation\MxaApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;

/**
 * @package emailcenter/maxautomation-plugin-magento
 */
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

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue('emailcenter_maxautomation/general/enabled',
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param Subscriber $email
     * @return bool
     */
    public function afterSubscribe(Subscriber $email)
    {
        if ($this->isEnabled() === true) {
            if ($email->isStatusChanged() && $email->getStatus() == Subscriber::STATUS_SUBSCRIBED) {
                $this->getMxaApi()->sendContact($email->getId(), $email->getEmail());
            }
        }
        return true;
    }

    /**
     * @param Subscriber $code
     * @return bool
     */
    public function afterConfirm(Subscriber $code)
    {
        if ($this->isEnabled() === true) {
            $this->getMxaApi()->sendContact($code->getId(), $code->getEmail());
        }
        return true;
    }

    /**
     * @return MxaApi
     */
    private function getMxaApi()
    {
        if ($this->api === null) {
            $token = $this->scopeConfig->getValue('emailcenter_maxautomation/general/api_key',
                ScopeInterface::SCOPE_STORE);
            $this->api = new MxaApi($token);
        }
        return $this->api;
    }

    /**
     * @param MxaApi $mxaApi
     * @return $this
     */
    public function setMxaApi(MxaApi $mxaApi)
    {
        $this->api = $mxaApi;
        return $this;
    }
}
