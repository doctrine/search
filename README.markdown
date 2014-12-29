# Doctrine Search #

Note: This project is a prototype at the moment. See `demo` folder for practical implementation example.

__Supported search engines__

* [ElasticSearch](http://www.elasticsearch.org/) (functional)
* [Solr](http://lucene.apache.org/solr/) (partial implementation)
* [ZendLucene](http://framework.zend.com/manual/en/zend.search.lucene.html) (partial implementation)


__Features__
* SearchManager
  * Can be used stand-alone or in a hybrid configuration
  * Configurable search manager supports aggregate entity manager
  * supports direct API calls through search engine adapters such as Elastica
  * transforms returned ID's via batch operation into hydrated objects as required
  * Supports event manager listeners for customizable entity handling
* Support for indexing through event listeners via JMS Serializer or simple entity callback.
* Annotations for index and data type creation using ObjectManager::getClassMetadata() as the base structure

#Usage#

## Configuration ##
The search manager connection can be configured as shown in the following example:
```php
$config = new Doctrine\Search\Configuration();
$config->setMetadataCacheImpl(new Doctrine\Common\Cache\ArrayCache());
$config->setEntitySerializer(
  new Doctrine\Search\Serializer\JMSSerializer(
    JMS\Serializer\SerializationContext::create()->setGroups('search')
  )
);

$eventManager = new Doctrine\Search\EventManager();
$eventManager->addListener($listener);

$searchManager = new Doctrine\Search\SearchManager(
  $config,
  new Doctrine\Search\ElasticSearch\Client(
    new Elastica\Client(array(
      array('host' => 'localhost', 'port' => '9200')
    )
  ),
  $eventManager
);
```

## Mappings ##
Basic entity mappings for index and type generation can be annotated as shown in the following example. Mappings
can be rendered into a format suitable for automatically generating indexes and types using a build script
(advanced setup required).
```php
<?php
namespace Entities;

use Doctrine\Search\Mapping\Annotations as MAP;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @MAP\ElasticSearchable(index="indexname", type="post", source=true)
 */
class Post
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   * @MAP\ElasticField(type="integer", includeInAll=false)
   */
  private $id;

  /**
   * @ORM\Column(type="string")
   * @MAP\ElasticField(type="string", includeInAll=true, boost=5.0)
   */
  private $title;

  /**
   * @ORM\Column(type="text")
   * @MAP\ElasticField(type="string", includeInAll=true)
   */
  private $content;

  /**
   * @MAP\ElasticField(name="tags", type="string", includeInAll=false, index="not_analyzed")
   */
  public function getTags() {
    return $this->tags->slice(0,3);
  }
}
```

## Indexing ##
Documents can be serialized for indexing currently in the following ways. If required an event listener can
be used with your ORM as shown in this example. If an event listener is not needed, entities can be persisted
or removed directly using the search manager.

The repository contains `OrmSearchableListener`, which can be used for this.


### CallbackSerializer ###
This approach simply expects a `toArray()` method on the entity, although this method be configured as required.
The interface suggested in this example can be any interface you desire, as long as your event listener can identify
entities that need to be persisted to the search engine (see above example).
```php
...
use Entities\Behaviour\SearchableEntityInterface

class Post implements SearchableEntityInterface
{
  ...
  public function toArray() {
    return array(
      'id' => $this->id,
      'title' => $this->title,
      'content' => $this->content
      ...
    );
  }
}
```

### JMS Serializer ###
You can alternatively use the advanced serialization power of the `JMS Serializer` to automatically handle
serialization for you based on annotations such as those shown in this example.
```php
...
use JMS\Serializer\Annotation as JMS;
use Entities\Behaviour\SearchableEntityInterface

/**
 * @ORM\Entity
 * @MAP\ElasticSearchable(index="indexname", type="post", source=true)
 * @JMS\ExclusionPolicy("all")
 */
class Post implements SearchableEntityInterface
{
  ...
  /**
   * @ORM\Column(type="string")
   * @MAP\ElasticField(type="string", includeInAll=true, boost=5.0)
   * @JMS\Expose
   * @JMS\Groups({"public", "search"})
   */
  private $title;

  /**
   * @ORM\Column(type="text")
   * @MAP\ElasticField(type="string", includeInAll=true)
   * @JMS\Expose
   * @JMS\Groups({"public", "search"})
   */
  private $content;
  ...
}
```

### AnnotationSerializer ###
Not yet available.


## Queries ##
Queries can be executed through the search manager as shown below. Use of the result cache refers to using the
cache for the hydration query. Search engine specific adapter interfaces are exposed magically so as in this
example, `Elastica\Query::addSort` method is amalgamated with `Doctrine\Search\Query` methods. Thus any complexity
of query supported by the search engine client library is supported.
```php
$hydrationQuery = $entityManager->createQueryBuilder()
  ->select(array('p', 'field(p.id, :ids) as HIDDEN field'))
	->from('Entities\Post', 'p')
	->where('p.id IN (:ids)')
	->orderBy('field')
	->getQuery();

$query = $searchManager->createQuery()
  ->from('Entities\Post')
  ->searchWith(new Elastica\Query())
	->hydrateWith($hydrationQuery)
	->addSort('_score')
	->setFrom(0)
	->setLimit(10)
	->getResult();
```

Simple Repository ID queries and `Term` search can by done using the following technique. Deserialization is done
independently of `Doctrine\ORM` but the same models are hydrated according to the registered `SerializerInterface`.
```php
$entity = $searchManager->getRepository('Entities\Post')->find($id);
$entity = $searchManager->getRepository('Entities\Post')->findOneBy(array($key => $term));
```
