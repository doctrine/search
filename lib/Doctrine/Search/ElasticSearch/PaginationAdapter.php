<?php

namespace Doctrine\Search\ElasticSearch;

use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\Search\Query;

class PaginationAdapter implements AdapterInterface
{
    /** @var Query */
    protected $query;
    
    /** @var integer */
    protected $hits;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function getItems($iOffset, $iItemCountPerPage)
    {
        $this->query->setFrom($iOffset);
        $this->query->setLimit($iItemCountPerPage);

        $resultSet = $this->query->getResult();
        
        // Return Elastica\Results if hydration is bypassed
        if($resultSet instanceof \Elastica\ResultSet) {
            return $resultSet->getResults();
        }
        
        return $resultSet;
    }

    public function count()
    {
        return $this->query->count();
    }
}