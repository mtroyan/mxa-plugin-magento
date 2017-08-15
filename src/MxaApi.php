<?php

namespace Emailcenter\Maxautomation;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

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

    public function __construct($token)
    {
        $this->token = $token;
    }

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

    private function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client(['base_uri' => 'https://maxautomation.emailcenteruk.com/api/v1/']);
        }
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}
