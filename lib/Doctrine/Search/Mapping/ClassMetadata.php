<?php
/*
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

namespace Doctrine\Search\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;

/**
 * A <tt>ClassMetadata</tt> instance holds all the object-document mapping metadata
 * of a document and it's references.
 *
 * Once populated, ClassMetadata instances are usually cached in a serialized form.
 *
 * <b>IMPORTANT NOTE:</b>
 *
 * The fields of this class are only public for 2 reasons:
 * 1) To allow fast READ access.
 * 2) To drastically reduce the size of a serialized instance (public/protected members
 *    get the whole class name, namespace inclusive, prepended to every property in
 *    the serialized representation).
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class ClassMetadata implements ClassMetadataInterface
{
    /**
     * @var string
     */
    public $index;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $numberOfShards = 1;

    /**
     * @var int
     */
    public $numberOfReplicas = 0;

    /**
     * @var int
     */
    public $opType = 1;

    /**
     * @var string
     */
    public $parent;

    /**
     * @var int
     */
    public $timeToLive = 1;

    /**
     * @var int
     */
    public $value = 1;

    /**
     * @var boolean
     */
    public $source = true;

    /**
     * @var float
     */
    public $boost = 1.0;

    /**
     * @var string
     */
    public $className;

    /**
     * The ReflectionProperty instances of the mapped class.
     *
     * @var array
     */
    public $fieldMappings = array();


    /**
     * The ReflectionClass instance of the mapped class.
     *
     * @var ReflectionClass
     */
    public $reflClass;

    /**
     * The ReflectionClass instance of the mapped class.
     *
     * @var ReflectionClass
     */
    public $reflFields;
    
    /**
     * READ-ONLY: The field names of all fields that are part of the identifier/primary key
     * of the mapped entity class.
     *
     * @var mixed
     */
    public $identifier;


    public function __construct($documentName)
    {
        $this->className = $documentName;
        $this->reflClass = new \ReflectionClass($documentName);
    }

    /** Determines which fields get serialized.
     *
     * It is only serialized what is necessary for best unserialization performance.
     *
     * Parts that are also NOT serialized because they can not be properly unserialized:
     *      - reflClass (ReflectionClass)
     *      - reflFields (ReflectionProperty array)
     *
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        // This metadata is always serialized/cached.
        return array(
            'boost',
            'className',
            'fieldMappings',
            'index',
            'numberOfReplicas',
            'numberOfShards',
            'opType',
            'parent',
            'timeToLive',
            'type',
            'value',
            'reflFields',
            'identifier'
        );
    }

    /**
     * Restores some state that can not be serialized/unserialized.
     *
     * @return void
     */
    public function __wakeup()
    {
        // Restore ReflectionClass and properties
        $this->reflClass = new \ReflectionClass($this->className);

        foreach ($this->fieldMappings as $field => $mapping) {
            $reflField = $this->reflClass->getProperty($field);
            $reflField->setAccessible(true);
            $this->reflFields[$field] = $reflField;
        }
    }

    /**
     * Get fully-qualified class name of this persistent class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->reflClass->getName();

    }

    /**
     * Gets the mapped identifier field name.
     *
     * The returned structure is an array of the identifier field names.
     *
     * @return array
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * INTERNAL:
     * Sets the mapped identifier key field of this class.
     * Mainly used by the ClassMetadataFactory to assign inherited identifiers.
     *
     * @param mixed $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Gets the ReflectionClass instance for this mapped class.
     *
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflClass;
    }

    /**
     * Checks if the given field name is a mapped identifier for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isIdentifier($fieldName)
    {
        return $this->identifier === $fieldName;
    }

    /**
     * Checks if the given field is a mapped property for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function hasField($fieldName)
    {
        return false;
    }

    /**
     * This mapping is used in the _wakeup-method to set the reflFields after _sleep.
     *
     * @param \ReflectionProperty $field
     * @param array $mapping
     */
    public function addFieldMapping(\Reflector $field, $mapping = array())
    {
        $fieldName = $field->getName();
        $this->fieldMappings[$fieldName] = $mapping;
    }

    /**
     * @param \ReflectionProperty $field
     */
    /*public function addField(\ReflectionProperty $field)
    {
        $fieldName = $field->getName();
        $this->reflFields[] = $field;
    }*/


    /**
     * Checks if the given field is a mapped association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function hasAssociation($fieldName)
    {
        return false;
    }

    /**
     * Checks if the given field is a mapped single valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isSingleValuedAssociation($fieldName)
    {
        return false;
    }

    /**
     * Checks if the given field is a mapped collection valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        return false;
    }

    /**
     * A numerically indexed list of field names of this persistent class.
     *
     * This array includes identifier fields if present on this class.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->reflFields);
    }

    /**
     * Currently not necessary but needed by Interface
     *
     * @return array
     */
    public function getAssociationNames()
    {
        return array();
    }

    /**
     * Returns a type name of this field.
     *
     * This type names can be implementation specific but should at least include the php types:
     * integer, string, boolean, float/double, datetime.
     *
     * @param string $fieldName
     * @return string
     */
    public function getTypeOfField($fieldName)
    {
        //@todo: check if $field exists
        return gettype($this->$fieldName);
    }

    /**
     * Currently not necessary but needed by Interface
     *
     *
     * @param string $assocName
     * @return string
     */
    public function getAssociationTargetClass($assocName)
    {
        return '';
    }

    public function isAssociationInverseSide($assocName)
    {
        return '';
    }

    public function getAssociationMappedByTargetField($assocName)
    {
        return '';
    }

    /**
     * Return the identifier of this object as an array with field name as key.
     *
     * Has to return an empty array if no identifier isset.
     *
     * @param object $object
     * @return array
     */
    public function getIdentifierValues($object)
    {
        // TODO: Implement getIdentifierValues() method.
    }

    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * @return array
     */
    public function getIdentifierFieldNames()
    {
        // TODO: Implement getIdentifierFieldNames() method.
    }
}
