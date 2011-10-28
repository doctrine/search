# Doctrine Search (Solr, Elastic-Search, Lucene, ...) 

#Todo: convert this into items
* SearchService
   * aggregates a ObjectManager instance: $searchManager = new SearchManager($objectManager);
   * supports direct API calls (Solr, Lucene, ... Adapter useable)
   * transforms returned ID"s via batch operation into objects
* EventListener for indexing, new SearchIndexListener($backendAdapter);
* uses ObjectManager::getClassMetadata() as the base structure
* adds new Annotationen for more complexe configuration needs

on the class:
@ORM\Entity
@Search\Searchable

omn field/properties:
@Search\Field(boost=2.0,...)
@Search\SolrField (explcit configuration)