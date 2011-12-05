<?php
namespace Doctrine\Search\Http;

interface ResponseInterface
{
    /**
     * Get the statuscode
     *
     * @return int
     */
    function getStatusCode();

    /**
     * Get a header
     *
     * @return string
     */
    function getHeader($key);

    /**
     * Get all headers
     *
     * @return array
     */
    function getHeaders();

    /**
     * @return bool
     */
    function isSuccessfull();

    /**
     * @return string
     */
    function __toString();

    /**
     * @return string
     */
    function getContent();
}