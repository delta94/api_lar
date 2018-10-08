<?php

namespace Test\Base;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Test\Roles\Roles;

class AccountBase
{
    use DatabaseTransactions;

    protected $role;
    protected $method;
    protected $url;
    protected $http;

    public function __construct()
    {
        $this->method = 'POST';
        $this->url    = 'login';
        $this->role   = 'admin';

        $this->http = new Client([
            'base_uri' => env('API_URL'),
        ]);
    }

    public function getToken($role = null)
    {
        if (!is_null($role)) {
            $this->role = $role;
        }
        try {
            $response = $this->http->request(
                $this->method,
                $this->url,
                $this->user()
            );;

            $body = json_decode($response->getBody()->getContents());

            return $body->access_token;
        } catch (ClientException $clientException) {
            dd($clientException->getMessage());
        }

    }

    public function user()
    {
        $user = 'admin@westay.org';
        switch ($this->role) {
            case Roles::ADMIN:
                $user = 'admin@westay.org';
                break;
            default:
                break;
        }

        return [
            'json' => [
                'username' => $user,
                'password' => 'admin',
            ],
        ];
    }
}