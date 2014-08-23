<?php

use Pagerfanta\Adapter\AdapterInterface;
use Doctrine\Search\Query;

class PagerfantaAdapter implements AdapterInterface
{
    /** @var Query */
    protected $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getSlice($iOffset, $iLength)
    {
        $this->query->setFrom($iOffset);
        $this->query->setLimit($iLength);
        return $this->query->getResult();
    }

    public function getNbResults()
    {
        return $this->query->count();
    }
}
