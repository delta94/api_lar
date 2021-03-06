<?php
namespace Test;

use GuzzleHttp\Client;
use Test\Base\AccountBase;

abstract class TestCase extends \Laravel\Lumen\Testing\TestCase
{
    public $http;
    public $header;
    public $response;

    protected $method = 'GET';
    protected $url    = '/';

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';
        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        $this->http = new Client([
            'base_uri' => env('API_URL'),
        ]);

        $this->header = [
            'User-Agent'    => 'testing/1.0',
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . env('TOKEN_SECRET'),
        ];
    }

    /**
     * Thiết lập header
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param $token
     */
    public function setHeader($token) {
        $this->header = [
            'User-Agent'    => 'testing/2.0',
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * Giả lập đăng nhập theo chức vụ
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param string $role
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loginAs($role = 'admin') {
        $user = new AccountBase();
        $this->setHeader($user->getToken($role));
    }

    /**
     * Tạo request đến Guzzle Client
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param array $option
     *
     * @return $this
     */
    public function request($option = [])
    {
        $this->response = $this->http->request(
            $this->method,
            $this->url,
            $option
        );

        return $this;
    }

    /**
     * Lưu response vào instance
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return $this
     */
    public function body()
    {
        $this->response = json_decode($this->response->getBody()->getContents());
        return $this;
    }

    /**
     * Lấy mã code trả về
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->response->code;
    }

    /**
     * Lấy data trả về
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->response->data;
    }

    /**
     * Kiểm tra object xem có tồn tại các trường trong props
     * @author HarikiRito <nxh0809@gmail.com>
     *
     * @param       $item
     * @param array $props
     *
     * @return bool
     */
    public function checkResponseData($item, $props = [])
    {
        foreach ($props as $prop) {
            if (!property_exists($item, $prop)) {
                return false;
            }
        }
        return true;
    }


}
