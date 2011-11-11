<?php
namespace Doctrine\Search\Exception;
 
class JsonEncodeException extends \Exception {

    static private $errors = array(
        JSON_ERROR_NONE           => 'unknown error',
        JSON_ERROR_DEPTH          => 'maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'invalid or malformed JSON',
        JSON_ERROR_SYNTAX         => 'Syntax error',
        JSON_ERROR_CTRL_CHAR      => 'Control character error',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters',
    );

    public function __construct($value) {
        $message = sprintf(
            'Failure by encode %s to json [%s]',
            (is_object($value) ? get_class($value) : gettype($value)),
            static::$errors[json_last_error()]
        );

        parent::__construct($message);
    }
}
