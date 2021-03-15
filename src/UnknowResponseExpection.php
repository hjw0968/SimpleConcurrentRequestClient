<?php
/**
 * @author      黄大帅
 * @version     1.0
 */

namespace SimpleConcurrent;

/**
 * when response can't explain, this exception will be throw
 */
class UnknowResponseExpection extends \Exception
{
    public function __construct($response)
    {
        $type = 'unknow';
        if (is_string($response)) {
            $type = 'String';
        } elseif (is_array($type)) {
            $type = 'Array';
        } elseif (is_object($type)) {
            $type = 'Class ' . get_class($type);
        }
        parent::__construct('response is not a support type of ' . $type, 404);
    }
}