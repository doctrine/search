<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Search\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Searchable extends Annotation
{
    /**
     * @var string $index;
     */
    public $index;
    /**
     * @var string $type;
     */
    public $type;
}

/**
 * @Annotation
 * @Target("CLASS")
 */
final class ElasticSearchable extends Searchable
{
    /**
     * @var int $numberOfShards;
     */
    public $numberOfShards;
    /**
     * @var int $numnberOfReplicas
     */
    public $numberOfReplicas;
    /**
     * @var string $op_type;
     */
    public $opType;
    /**
     * @var float $parent;
     */
    public $parent;
    /**
     * TTL in milliseconds
     * @var int $timeToLive
     */
    public $timeToLive;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Field extends Annotation
{
    /**
     * @var float
     */
    public $boost;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class SolrField extends Field
{
    /* configuration */
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class ElasticField extends Field
{
   /* configuration */
}
