<?php

namespace Emailcenter\Maxautomation\src\Model;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class Sender
{
    public $id;
    public $email;
    public $token;

    public function __construct($id, $email, $token)
    {
        $this->id = $id;
        $this->email = $email;
        $this->token = $token;
    }

    public function sendContact()
    {
        try {
            $client = new Client(['base_uri' => 'https://maxautomation.dev.emailcenteruk.com/api/v1/']);
            $response = $client->request('POST', 'trigger/Magento%20New%20Subscriber', [
                'headers' => ['X-Auth-Token' => $this->token, 'Accept' => 'application/json'],
                'form_params' => ['$contactId' => $this->id, '$email' => $this->email],
                'timeout' => 10.00
            ]);
            echo $response->getBody()->getContents();
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }
    }
}
