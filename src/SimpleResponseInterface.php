<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

/**
 * simple response interface
 */
interface SimpleResponseInterface
{
    /**
     * when request failed will use this method pass the error
     * @param mixed $error
     */
    public function setFail($error);

    /**
     * this method will return error when request failed
     * otherwise will return null
     * @return mixed
     */
    public function getFail();

    /**
     * if response failed or not
     * @return bool
     */
    public function isFail(): bool;

    /**
     * when request successed will use this method pass the result
     * @param mixed $result
     */
    public function setResult($result);

    /**
     * this method will return result when request successed
     * otherwise will return null
     * @return mixed
     */
    public function getResult();
}