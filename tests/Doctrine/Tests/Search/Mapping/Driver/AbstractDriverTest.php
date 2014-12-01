<?php

namespace Doctrine\Tests\Search\Mapping\Driver;

use Doctrine\Search\Mapping\ClassMetadata;

abstract class AbstractDriverTest extends \PHPUnit_Framework_TestCase {

    protected function loadExpectedMetadataFor($className) {

        $expected = new ClassMetadata($className);
        $expected->type = 'users';
        $expected->identifier = 'id';
        $expected->index = 'searchdemo';
        $expected->numberOfShards = 2;
        $expected->numberOfReplicas = 1;
        $expected->timeToLive = 180;
        $expected->boost = 2.0;
        $expected->source = true;

        $expected->mapRoot(array(
            'name' => 'dynamic_templates',
            'id' => 'template_2',
            'match' => 'description*',
            'mapping' => array(
                'type' => 'multi_field',
                'fields' => array(
                    array(
                        'fieldName' => '{name}',
                        'type' => 'string',
                        'includeInAll' => false
                    ),
                    array(
                        'fieldName' => 'untouched',
                        'type' => 'string',
                        'index' => 'not_analyzed'
                    )
                )
            )
        ));

        $expected->mapRoot(array(
            'name' => 'date_detection',
            'value' => 'false'
        ));

        $expected->mapField(array(
            'fieldName' => 'name',
            'type' => 'string',
            'includeInAll' => false,
            'index' => 'no',
            'boost' => 2.0
        ));

        $expected->mapField(array(
            'fieldName' => 'username',
            'type' => 'multi_field',
            'fields' => array(
                array(
                    'fieldName' => 'username',
                    'type' => 'string',
                    'includeInAll' => true,
                    'analyzer' => 'whitespace'
                ),
                array(
                    'fieldName' => 'username.term',
                    'type' => 'string',
                    'includeInAll' => false,
                    'index' => 'not_analyzed'
                )
            )
        ));

        $expected->mapField(array(
            'fieldName' => 'ip',
            'type' => 'ip',
            'includeInAll' => false,
            'index' => 'no',
            'store' => true,
            'nullValue' => '127.0.0.1'
        ));

        $expected->mapField(array(
            'fieldName' => 'emails',
            'type' => 'nested',
            'properties' => array(
                array(
                    'fieldName' => 'email',
                    'type' => 'string',
                    'includeInAll' => false,
                    'index' => 'not_analyzed'
                ),
                array(
                    'fieldName' => 'createdAt',
                    'type' => 'date'
                )
            )
        ));

        $expected->mapField(array(
            'fieldName' => 'active',
            'type' => 'boolean',
            'nullValue' => false
        ));

        $expected->mapParameter(array(
            'parameterName' => '_routing',
            'type' => 'string'
        ));

        return $expected;
    }
}