# Doctrine Search #

__Supported search engines__

* [ElasticSearch](http://www.elasticsearch.org/)
* [Solr](http://lucene.apache.org/solr/)
* [ZendLucene](http://framework.zend.com/manual/en/zend.search.lucene.html)

## Todo convert this into items ##
* SearchService
   * aggregates a ObjectManager instance:  $searchManager = new SearchManager($objectManager);
   * supports direct API calls (Solr, Lucene, ... Adapter useable)
   * transforms returned ID"s via batch operation into objects
* EventListener for indexing, new SearchIndexListener($backendAdapter);
* uses ObjectManager::getClassMetadata() as the base structure
* adds new Annotationen for more complexe configuration needs


## Usage ##

```php
<?php
namespace Entities;

use Doctrine\Search\Mapping\Annotation as Search;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Search\Searchable
 */
class Post
{
    /**
     * @var integer
     * @ORM\Id @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Search\Field
     */
    public $title;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Search\Field
     * @Search\SolrField
     */
    public $content;
}