<?php
namespace Doctrine\Search\Http;

interface ResponseInterface
{
    /**
     * Get the statuscode
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Get a header
     *
     * @return string
     */
    public function getHeader($key);

    /**
     * Get all headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * @return bool
     */
    public function isSuccessfull();

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getContent();
}