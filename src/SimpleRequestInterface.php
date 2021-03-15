<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * simple request interface
 */
interface SimpleRequestInterface
{
    /**
     * set a client implements \GuzzleHttp\ClientInterface
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client);

    /**
     * get request promise implements \GuzzleHttp\Promise\PromiseInterface
     * @return PromiseInterface
     */
    public function getPromise(): PromiseInterface;

    /**
     * get callback function list when request successed called
     * this method will return a array and each element must be closure
     * @return \Closure[]
     */
    public function getSuccessCallbackList(): array;

    /**
     * get callback function list when request failed called
     * this method will return a array and each element must be closure
     * @return \Closure
     */
    public function getFailCallbackList(): array;

    /**
     * set response implements SimpleResponseInterface
     * @param SimpleResponseInterface $response
     */
    public function setResponse(SimpleResponseInterface $response);

    /**
     * get response
     * this method will return a response implements SimpleResponseInterface
     * @return SimpleResponseInterface
     */
    public function getResponse(): SimpleResponseInterface;

    /**
     * set the request implements \Psr\Http\Message\RequestInterface
     * @param RequestInterface $request
     * @param array $options
     */
    public function setRequest(RequestInterface $request, array $options = []);

    /**
     * set a promise implements \GuzzleHttp\Promise\PromiseInterface.
     * this method is not necessary, you can use setRequest to set a request
     * this method set promise will be priority to use
     * @param PromiseInterface $promise
     */
    public function setPromise(PromiseInterface $promise);
}