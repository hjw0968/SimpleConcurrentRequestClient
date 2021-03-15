<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

/**
 * when response read stream failed this exception will be throw
 */
class ResponseReadExpection extends \Exception
{
    public function __construct()
    {
        parent::__construct('response read failed', 404);
    }
}