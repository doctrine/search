<?php
namespace Doctrine\Search\Exception\Json;

use \Doctrine\Search\Exception\DoctrineSearchException;

class JsonDecodeException extends DoctrineSearchException {

    static private $errors = array(
        JSON_ERROR_NONE           => 'unknown error',
        JSON_ERROR_DEPTH          => 'maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'invalid or malformed JSON',
        JSON_ERROR_SYNTAX         => 'Syntax error',
        JSON_ERROR_CTRL_CHAR      => 'Control character error',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters',
    );

    public function __construct() {
        $message = sprintf(
            'Failure by decode from json [%s]',
            static::$errors[json_last_error()]
        );

        parent::__construct($message);
    }
}
