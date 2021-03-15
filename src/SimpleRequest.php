<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use function GuzzleHttp\json_decode;

/**
 * a simple request implements SimpleRequestInterface
 */
class SimpleRequest implements SimpleRequestInterface
{

    /**
     * request client implements \GuzzleHttp\ClientInterface
     * @var ClientInterface
     */
    private $client;

    /**
     * success callback list
     * every item in this array must be closure
     * @var \Closure[]
     */
    private $callbackOfSuccess = [];

    /**
     * fail callback list
     * every item in this array must be closure
     * @var \Closure[]
     */
    private $callbackOfFail = [];

    /**
     * a request promise implements \GuzzleHttp\Promise\PromiseInterface
     * @var PromiseInterface
     */
    private $promise;

    /**
     * the response of request
     * this response implememnts SimpleResponseInterface
     * @var SimpleResponseInterface
     */
    private $response;

    /**
     * whether the response is json format.
     * @var bool
     */
    private $responseIsJson = false;

    /**
     * whether the conversion json format is add to callback list.
     * @var bool
     */
    private $isJsonCallbackPassed = false;


    /**
     * request orign
     * @var RequestInterface
     */
    private $requestOrign;

    /**
     * request options
     * @var array
     */
    private $requestOption = [];

    public function __construct()
    {
        $this->callbackOfSuccess = [];
        $this->callbackOfFail = [];
    }

    /**
     * get or init client
     * @return ClientInterface
     */
    private function _getClient(): ClientInterface
    {
        if (! $this->client instanceof ClientInterface) $this->client = new Client();
        return $this->client;
    }

    /**
     * when response is json format use this method and client will auto use json_decode conversion result
     * @return self
     */
    public function responseIsJson(): self
    {
        $this->responseIsJson = true;
        return $this;
    }

    /**
     * when response is not json format use this method close auto conversion json
     * @return self
     */
    public function responseIsNotJson(): self
    {
        $this->responseIsJson = false;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::setRequest()
     */
    public function setRequest(RequestInterface $request, array $options = []): self
    {
        $this->requestOrign = $request;
        $this->requestOption = $options;
        return $this;
    }

    /**
     * pass a closure to successed callback list.
     * @param \Closure $callback
     * @return self
     */
    public function addSuccessCallback(\Closure $callback): self
    {
        $this->callbackOfSuccess[] = $callback;
        return $this;
    }

    /**
     * pass a closure to failed callback list.
     * @param \Closure $callback
     * @return self
     */
    public function addFailCallback(\Closure $callback): self
    {
        $this->callbackOfFail[] = $callback;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::getFailCallbackList()
     */
    public function getFailCallbackList(): array
    {
        return $this->callbackOfFail;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::getPromise()
     */
    public function getPromise(): PromiseInterface
    {
        if (! $this->requestOrign && ! $this->promise) throw new RequestBuildExpection('please give a request.');
        if (! $this->promise) {
            $this->promise = $this->_getClient()->sendAsync($this->requestOrign, $this->requestOption);
        }
        if ($this->responseIsJson && ! $this->isJsonCallbackPassed) {
            array_unshift($this->callbackOfSuccess, function ($res) {
                return json_decode($res, true);
            });
            $this->isJsonCallbackPassed = true;
        }
        return $this->promise;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::getSuccessCallbackList()
     */
    public function getSuccessCallbackList(): array
    {
        return $this->callbackOfSuccess;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::setClient()
     */
    public function setClient(ClientInterface $client): self
    {
        $this->client = $client;
        return $this;
    }
    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::getResponse()
     */
    public function getResponse(): SimpleResponseInterface
    {
        return $this->response;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleRequestInterface::setResponse()
     */
    public function setResponse(SimpleResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @param PromiseInterface $promise
     * @return self
     */
    public function setPromise(PromiseInterface $promise): self
    {
        $this->promise = $promise;
        return $this;
    }

}