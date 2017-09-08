<?php

namespace Emailcenter\Maxautomation;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

/**
 * @package emailcenter/mxa-plugin-magento
 * @license LGPL-3.0
 */
class MxaApi
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @param int $id
     * @param string $email
     * @return void
     */
    public function sendContact($id, $email)
    {
        $options = [
            'headers' => [
                'X-Auth-Token' => $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'json' => [
                '$contactId' => $id,
                '$email' => $email
            ],
            'timeout' => 2
        ];
        try {
            $this->getClient()->request('POST', 'trigger/Magento New Subscriber', $options);
        } catch (RequestException $e) {
            // Swallow exceptions to avoid blocking Magento
        }
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client(['base_uri' => 'https://maxautomation.emailcenteruk.com/api/v1/']);
        }
        return $this->client;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
}
