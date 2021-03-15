<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

/**
 * when build promise faied this exception will be throw
 */
class RequestBuildExpection extends \Exception
{
    public function __construct($message)
    {
        parent::__construct('build request failed because ' . $message, 501);
    }
}