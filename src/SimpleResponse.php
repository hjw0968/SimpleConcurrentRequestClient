<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;


class SimpleResponse implements SimpleResponseInterface
{
    private $result;

    private $error = null;

    public function __construct()
    {
        $this->error = null;
        $this->result = null;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleResponseInterface::getFail()
     */
    public function getFail()
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleResponseInterface::getResult()
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleResponseInterface::isFail()
     */
    public function isFail(): bool
    {
        return $this->result === null || $this->error !== null;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleResponseInterface::setFail()
     */
    public function setFail($error)
    {
        $this->error = $error;
    }

    /**
     * {@inheritDoc}
     * @see \SimpleConcurrent\SimpleResponseInterface::setResult()
     */
    public function setResult($result)
    {
        $this->result = $result;
    }


}