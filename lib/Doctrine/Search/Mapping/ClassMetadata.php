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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Search\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use Doctrine\Common\Persistence\Mapping\ReflectionService;



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
 * @author Filip Procházka <filip@prochazka.su>
 */
class ClassMetadata implements ClassMetadataInterface
{
    /**
     * @todo allow mapping to multiple types
     * @var TypeMetadata
     */
    public $type;

    /**
     * @todo allow mapping to multiple indexes
     * @var IndexMetadata
     */
    public $index;

    /**
     * @var string
     */
    public $parent;

    /**
     * @var string
     */
    public $className;

    /**
     * @var string with class implementing \Doctrine\Search\EntityRiver
     */
    public $riverImplementation;

    /**
     * @var ClassMetadataInterface|\Doctrine\ORM\Mapping\ClassMetadata
     */
    private $parentMetadata;



    public function __construct($entityName)
    {
        $this->className = $entityName;
    }



    /**
     * @return array The names of all the fields that should be serialized.
     */
    public function __sleep()
    {
        // This metadata is always serialized/cached.
        return array(
            'type',
            'index',
            'parent',
            'className',
            'riverImplementation',
        );
    }



    /**
     * @return string
     */
    public function getIndexName()
    {
        return $this->index->name;
    }



    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->type->name;
    }



    /**
     * Get fully-qualified class name of this persistent class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->className;
    }



    /**
     * Gets the mapped identifier field name.
     *
     * The returned structure is an array of the identifier field names.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->parentMetadata->getSingleIdentifierFieldName();
    }



    /**
     * Gets the ReflectionClass instance for this mapped class.
     *
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->parentMetadata->getReflectionClass();
    }



    /**
     * Checks if the given field name is a mapped identifier for this class.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function isIdentifier($fieldName)
    {
        return $this->parentMetadata->isIdentifier($fieldName);
    }



    /**
     * Checks if the given field is a mapped property for this class.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function hasField($fieldName)
    {
        return $this->parentMetadata->hasField($fieldName);
    }



    /**
     * Checks if the given field is a mapped association for this class.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function hasAssociation($fieldName)
    {
        return $this->parentMetadata->hasAssociation($fieldName);
    }



    /**
     * Checks if the given field is a mapped single valued association for this class.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function isSingleValuedAssociation($fieldName)
    {
        return $this->parentMetadata->isSingleValuedAssociation($fieldName);
    }



    /**
     * Checks if the given field is a mapped collection valued association for this class.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        return $this->parentMetadata->isCollectionValuedAssociation($fieldName);
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
        return $this->parentMetadata->getFieldNames();
    }



    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * @return array
     */
    public function getIdentifierFieldNames()
    {
        return $this->parentMetadata->getIdentifierFieldNames();
    }



    /**
     * Returns a numerically indexed list of association names of this persistent class.
     *
     * This array includes identifier associations if present on this class.
     *
     * @return array
     */
    public function getAssociationNames()
    {
        return $this->parentMetadata->getAssociationNames();
    }



    /**
     * Returns a type name of this field.
     *
     * This type names can be implementation specific but should at least include the php types:
     * integer, string, boolean, float/double, datetime.
     *
     * @param string $fieldName
     *
     * @return string
     */
    public function getTypeOfField($fieldName)
    {
        return $this->parentMetadata->getTypeOfField($fieldName);
    }



    /**
     * Returns the target class name of the given association.
     *
     * @param string $assocName
     *
     * @return string
     */
    public function getAssociationTargetClass($assocName)
    {
        return $this->parentMetadata->getAssociationTargetClass($assocName);
    }



    /**
     * Checks if the association is the inverse side of a bidirectional association.
     *
     * @param string $assocName
     *
     * @return boolean
     */
    public function isAssociationInverseSide($assocName)
    {
        return $this->parentMetadata->isAssociationInverseSide($assocName);
    }



    /**
     * Returns the target field of the owning side of the association.
     *
     * @param string $assocName
     *
     * @return string
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        return $this->parentMetadata->getAssociationMappedByTargetField($assocName);
    }



    /**
     * Returns the identifier of this object as an array with field name as key.
     *
     * Has to return an empty array if no identifier isset.
     *
     * @param object $object
     *
     * @return array
     */
    public function getIdentifierValues($object)
    {
        return $this->parentMetadata->getIdentifierValues($object);
    }



    public function wakeupReflection(ReflectionService $reflService, ClassMetadataInterface $parentMetadata)
    {
        $this->parentMetadata = $parentMetadata;
    }



    public function __clone()
    {
        $this->type = clone $this->type;
        $this->index = clone $this->index;
    }

}
