<?php

namespace Emailcenter\Maxautomation\Plugin;

use Emailcenter\Maxautomation\MxaApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class SubscriberPluginTest extends TestCase
{
    /**
     * @var SubscriberPlugin|\PHPUnit_Framework_MockObject_MockObject
     */
    private $plugin;

    /**
     * @var Subscriber|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriberMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var MxaApi|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockMxaApi;

    protected function setUp()
    {
        $this->subscriberMock = $this->createMock(Subscriber::class);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->plugin = new SubscriberPlugin($this->scopeConfigMock);

        $this->mockMxaApi = $this->createMock(MxaApi::class);
        $this->plugin->setMxaApi($this->mockMxaApi);
    }

    public function testIsEnabled()
    {
        $dbValue = 1;
        $scopeConfigMock = $this->scopeConfigMock;
        $scopeConfigMock->method('getValue')
            ->willReturn(true);
        $subscriber = new SubscriberPlugin($scopeConfigMock);
        $this->assertEquals($dbValue, $subscriber->isEnabled());
    }

    public function testAfterSubscribeCallsSendContact()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('emailcenter_maxautomation/general/enabled', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        $this->subscriberMock->method('isStatusChanged')
            ->willReturn(true);

        $this->subscriberMock->method('getStatus')
            ->willReturn(1);

        $id = 123;
        $email = 'test@example.com';

        $this->subscriberMock->method('getId')
            ->willReturn($id);
        $this->subscriberMock->method('getEmail')
            ->willReturn($email);

        $this->mockMxaApi->expects($this->once())
            ->method('sendContact')
            ->with($id, $email);

        $this->plugin->afterSubscribe($this->subscriberMock);
    }

    /**
     * @dataProvider providerAfterSubscribeDoesNotCallSendContact
     */
    public function testAfterSubscribeDoesNotCallSendContact($value, $changes, $status)
    {
        $this->scopeConfigMock->method('getValue')
            ->with('emailcenter_maxautomation/general/enabled', ScopeInterface::SCOPE_STORE, null)
            ->willReturn($value);

        $this->subscriberMock->method('isStatusChanged')
            ->willReturn($changes);

        $this->subscriberMock->method('getStatus')
            ->willReturn($status);

        $this->mockMxaApi->expects($this->never())
            ->method('sendContact');

        $this->plugin->afterSubscribe($this->subscriberMock);
    }

    public function providerAfterSubscribeDoesNotCallSendContact()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 0, 0],
            [1, 0, 1],
            [1, 1, 0]
        ];
    }

    public function testAfterSubscribeReturnsTrue()
    {
        $actual = $this->plugin->afterSubscribe($this->subscriberMock);
        $this->assertTrue($actual);
    }

    public function testAfterConfirmDoesNotCallSendContact()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('emailcenter_maxautomation/general/enabled', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(0);

        $this->mockMxaApi->expects($this->never())
            ->method('sendContact');

        $this->plugin->afterConfirm($this->subscriberMock);
    }

    public function testAfterConfirmCallsSendContact()
    {
        $this->scopeConfigMock->method('getValue')
            ->with('emailcenter_maxautomation/general/enabled', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(1);

        $id = 123;
        $email = 'test@example.com';

        $this->subscriberMock->method('getId')
            ->willReturn($id);
        $this->subscriberMock->method('getEmail')
            ->willReturn($email);

        $this->mockMxaApi->expects($this->once())
            ->method('sendContact')
            ->with($id, $email);

        $this->plugin->afterConfirm($this->subscriberMock);
    }

    public function testAfterConfirmReturnsTrue()
    {
        $actual = $this->plugin->afterConfirm($this->subscriberMock);
        $this->assertTrue($actual);
    }
}
