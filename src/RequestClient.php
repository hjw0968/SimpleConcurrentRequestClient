<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Pool;
use Psr\Http\Message\ResponseInterface;

/**
 * simple request client
 */
class RequestClient
{

    /**
     * guzzle client instance
     * @var ClientInterface
     */
    private $client;

    /**
     * default guuzzle client config
     * @var array
     */
    private $clientConfig = [
        'timeout' => 10,
        'connect_timeout' => 10,
        'allow_redirects' => false,
        'cookies' => false,
        'verify' => false,
        'headers' => [
            'user-agent' => 'Simple Concurrent Client v0.1'
        ]
    ];

    /**
     * a list of simple request instance
     * @var SimpleRequest
     */
    private $requestList;

    /**
     * the config of request concurrency
     * @var integer
     */
    private $configOfConcurrency = 10;

    public function __construct()
    {
        $this->initStatus();
    }

    /**
     * get a client implements \GuzzleHttp\ClientInterface
     * @return ClientInterface
     */
    private function _getClient(): ClientInterface
    {
        if (! $this->client instanceof ClientInterface) $this->client = new Client($this->clientConfig);
        return $this->client;
    }

    /**
     * set the request concurrency count
     * @param int $concurrency
     * @return self
     */
    public function setRequestConcurrency(int $concurrency): self
    {
        $this->configOfConcurrency = max(1, $concurrency);
        return $this;
    }

    /**
     * add a client request header
     * @param string $headerName
     * @param mixed $headerValue
     * @return self
     */
    public function addClientHeader(string $headerName, $headerValue): self
    {
        $this->clientConfig['headers'][$headerName] = $headerValue;
        return $this;
    }

    /**
     * set the request timeout seconds
     * default value is 10 seconds
     * @param number $seconds
     * @return self
     */
    public function setClientConfigOfTimeout($seconds = 10): self
    {
        $this->clientConfig['timeout'] = max(1, $seconds);
        return $this;
    }

    /**
     * open or close the client allow redirect or not allow
     * default value is not allow
     * @param string $enable
     * @return self
     */
    public function setAllowRedirect($enable = false): self
    {
        $this->clientConfig['allow_redirects'] = boolval($enable);
        return $this;
    }

    /**
     * open client cookie
     * @return self
     */
    public function enableCookie(): self
    {
        $this->clientConfig['cookies'] = true;
        return $this;
    }

    /**
     * close client cookie
     * @return self
     */
    public function disableCookie(): self
    {
        $this->clientConfig['cookies'] = false;
        return $this;
    }

    /**
     * pass a cookie instance to client
     * the cookie instance must implements \GuzzleHttp\Cookie\CookieJarInterface
     * @param CookieJarInterface $cookie
     * @return RequestClient
     */
    public function setCookieInstance(CookieJarInterface $cookie)
    {
        $this->clientConfig['cookies'] = $cookie;
        return $this;
    }

    /**
     * get client cookie instance
     * if client use cookie will return a cookie implements \GuzzleHttp\Cookie\CookieJarInterface
     * if client not use cookie will return null
     * @return mixed
     */
    public function getCookieInstance()
    {
        return $this->_getClient()->getConfig('cookies');
    }

    /**
     * clear request list
     * @return self
     */
    public function initStatus():self
    {
        $this->requestList = [];
        return $this;
    }

    /**
     * pass new request to request list
     * @param SimpleRequest $request
     * @return self
     */
    public function addRequest(SimpleRequest & $request): self
    {
        $this->requestList[] = $request;
        return $this;
    }

    /**
     * when request successed this method will be called
     * @param mixed $response
     * @param int $index
     * @throws \Exception
     */
    private function _responseSuccessHandle($response, $index)
    {
        try {
            if (! $response instanceof ResponseInterface) throw new UnknowResponseExpection($response);
            $result = $response->getBody()->getContents();
            $cbk = $this->requestList[$index]->getSuccessCallbackList();
            if (! empty($cbk)) {
                $result = array_reduce($cbk, function ($prev, $cb) {
                    return $cb($prev);
                }, $result);
            }
            $response = new SimpleResponse();
            $response->setResult($result);
            $this->requestList[$index]->setResponse($response);
        } catch (\Exception $e) {
            $this->_responseFailHandle($e, $index);
        }
    }

    /**
     * when request failed this method will be called
     * @param mixed $error
     * @param int $index
     */
    private function _responseFailHandle($error, $index)
    {
        $cbk = $this->requestList[$index]->getFailCallbackList();
        if (! empty($cbk)) {
            $error = array_reduce($cbk, function ($prev, $cb) {
                return $cb($prev);
            }, $error);
        }
        $response = new SimpleResponse();
        $response->setFail($error);
        $this->requestList[$index]->setResponse($response);
    }

    /**
     * @return \Generator
     */
    private function _getRequestPromise()
    {
        foreach ($this->requestList as $request) {
            yield function () use ($request) {
                return $request->setClient($this->_getClient())->getPromise();
            };
        }
    }

    /**
     * build a request pool implements \GuzzleHttp\Pool
     * @return Pool
     */
    private function _getRequestPool(): Pool
    {
        return new Pool($this->_getClient(), $this->_getRequestPromise(), [
            'concurrency' => max(1, $this->configOfConcurrency),
            'fulfilled' => function () {
                call_user_func_array([$this, '_responseSuccessHandle'], func_get_args());
            },
            'rejected' => function () {
                call_user_func_array([$this, '_responseFailHandle'], func_get_args());
            }
        ]);
    }

    /**
     * execute all request
     * @return self
     */
    public function promiseAll(): self
    {
        $pool = $this->_getRequestPool();
        $pool->promise()->wait();
        return $this;
    }

    /**
     * open or close the client verify https or not allow
     * @param bool $allow
     * @return self
     */
    public function setVerifyHttps(bool $allow): self
    {
        $this->clientConfig['verify'] = $allow;
        return $this;
    }

    /**
     * add or modify client config by custom
     * @param string $settingKey
     * @param $settingValue
     * @return self
     */
    public function customSetting(string $settingKey, $settingValue): self
    {
        $this->clientConfig[$settingKey] = $settingValue;
        return $this;
    }
}