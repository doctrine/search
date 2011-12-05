<?php
namespace Doctrine\Search;
 
final class IndexEvents {

    private function __construct() {}

    const preCreate = 'preCreate';

    const postCreate = 'postCreate';

    const preRemove = 'preRemove';

    const postRemove = 'postRemove';

    const postCommit = 'postCommit';

    const loadClassMetadata = 'loadClassMetadata';

}
