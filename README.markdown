# Doctrine Search (Solr, Elastic-Search, Lucene, ...) 

#Todo: convert this into items
* SearchService
   * Aggregiert eine ObjectManager Instanz: $searchManager = new SearchManager($objectManager);
   * Direkt mit API (Solr, Lucene, ... Adapter nutzbar)
   * Wandelt zur�ckgegebene IDs per Batch-Operation in Objekte um
* EventListener f�r Indexierung, new SearchIndexListener($backendAdapter);
* Nutzt ObjectManager::getClassMetadata() f�r Grundstruktur
* Hat eigene Annotationen f�r komplexere Konfigurationszwecke

An der Klasse:
@ORM\Entity
@Search\Searchable

An den Feldern/Properties:
@Search\Field(boost=2.0,...)
@Search\SolrField (explizitere konfiguration)