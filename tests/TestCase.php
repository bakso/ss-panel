<?php

namespace Tests;

use App\Services\Config;
use PHPUnit_Framework_TestCase;
use Slim\Http\Body;
use Slim\Http\RequestBody;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\HTTP\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

class TestCase extends PHPUnit_Framework_TestCase
{
    public $app;

    public $request, $response;

    public function setUp()
    {
        $this->setTestingEnv();
    }

    /**
     * @param $method
     * @param $path
     * @param $body
     * @param $options
     * @return Request
     */
    protected function requestFactory($method, $path, $body = [], $options = [])
    {
        $uri = Uri::createFromString($path);
        $headers = new Headers();
        $cookies = [];
        $env = Environment::mock();
        $serverParams = $env->all();
        $body = $this->buildBody($body);
        //echo $body->getContents();
        $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body, []);
        return $request;
    }

    public function newNewStream()
    {
        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
        rewind($stream);
        return $stream;
    }

    /**
     * @param $input
     * @return Body
     */
    protected function buildBody($input)
    {
        $getContent = function () use ($input) {
            if (is_array($input)) {
                return http_build_query($input);
            }
            return $input;
        };
        $content = $getContent();
        $body = new RequestBody();
        $body->write($content);
        $body->rewind();
        return $body;
    }

    public function createApp()
    {
        // Build App
        $app = require __DIR__ . '/../app/routes.php';
        $app->run(true);
        return $app;
    }

    public function request($method, $path, $options = [])
    {
        // Build App
        $app = $this->createApp();
        $this->app = $app;
        // Build Req,Res
        $response = new Response();

        $request = $this->requestFactory($method, $path, $options);

        // send Req
        $this->response = $app($request, $response);
    }

    public function get($path, $options = [])
    {
        $this->request('GET', $path, $options);
    }

    public function post($path, $body = [], $options = [])
    {
        $this->request('POST', $path, $body, $options);
    }

    public function put($path, $body = [], $options = [])
    {
        $this->request('POST', $path, $body, $options);
    }

    public function delete($path, $body = [], $options = [])
    {
        $this->request('POST', $path, $body, $options);
    }

    /**
     * Set env in prod
     */
    public function setProdEnv()
    {
        Config::set('env', 'prod');
    }

    /**
     * Set testing env
     */
    public function setTestingEnv()
    {
        Config::set('env', 'testing');
    }

}