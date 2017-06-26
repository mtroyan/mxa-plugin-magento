<?php

namespace Emailcenter\Maxautomation\src\Plugin;

use Emailcenter\Maxautomation\src\Model\Sender;

class SubscriberPlugin
{
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function afterSubscribe(\Magento\Newsletter\Model\Subscriber $email, $proceed)
    {
        $enabled = $this->_scopeConfig->getValue('emailcenter_maxautomation/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $token = $this->_scopeConfig->getValue('emailcenter_maxautomation/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($enabled == true && !empty($token)) {
            if ($email->isStatusChanged() && $email->getStatus() ==
            \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED) {
                $pass = new Sender($email->getId(), $email->getEmail(), $token);
                $pass->sendContact();
            }
        }
        return $proceed;
    }

    public function afterConfirm(\Magento\Newsletter\Model\Subscriber $code, $proceed)
    {
        $enabled = $this->_scopeConfig->getValue('emailcenter_maxautomation/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $token = $this->_scopeConfig->getValue('emailcenter_maxautomation/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($enabled == true && !empty($token)) {
            $pass = new Sender($code->getId(), $code->getEmail(), $token);
            $pass->sendContact();
        }
        return $proceed;
    }
}
