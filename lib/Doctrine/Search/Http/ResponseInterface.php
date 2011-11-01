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