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

use Doctrine\Search\Mapping\ClassMetadataInfo;

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
 * 2) To drastically reduce the size of a serialized instance (private/protected members
 *    get the whole class name, namespace inclusive, prepended to every property in
 *    the serialized representation).
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class ClassMetadata implements \Doctrine\Common\Persistence\Mapping\ClassMetadata
{
    /**
     * The ReflectionProperty instances of the mapped class.
     *
     * @var array
     */
    public $reflFields = array();

   
    /**
     * The ReflectionClass instance of the mapped class.
     *
     * @var ReflectionClass
     */
    private $reflClass;


    public function initialize(\ReflectionClass $reflClass)
    {
        $this->reflClass = $reflClass;
    }

    /**
     * Get fully-qualified class name of this persistent class.
     *
     * @return string
     */
    function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Gets the mapped identifier field name.
     *
     * The returned structure is an array of the identifier field names.
     *
     * @return array
     */
    function getIdentifier()
    {
        // TODO: Implement getIdentifier() method.
    }

    /**
     * Gets the ReflectionClass instance for this mapped class.
     *
     * @return ReflectionClass
     */
    function getReflectionClass()
    {
        return $this->reflClass;
    }

    /**
     * Checks if the given field name is a mapped identifier for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    function isIdentifier($fieldName)
    {
        // TODO: Implement isIdentifier() method.
    }

    /**
     * Checks if the given field is a mapped property for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    function hasField($fieldName)
    {
        // TODO: Implement hasField() method.
    }

    /**
     * Checks if the given field is a mapped association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    function hasAssociation($fieldName)
    {
        // TODO: Implement hasAssociation() method.
    }

    /**
     * Checks if the given field is a mapped single valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    function isSingleValuedAssociation($fieldName)
    {
        // TODO: Implement isSingleValuedAssociation() method.
    }

    /**
     * Checks if the given field is a mapped collection valued association for this class.
     *
     * @param string $fieldName
     * @return boolean
     */
    function isCollectionValuedAssociation($fieldName)
    {
        // TODO: Implement isCollectionValuedAssociation() method.
    }

    /**
     * A numerically indexed list of field names of this persistent class.
     *
     * This array includes identifier fields if present on this class.
     *
     * @return array
     */
    function getFieldNames()
    {
        // TODO: Implement getFieldNames() method.
    }

    /**
     * A numerically indexed list of association names of this persistent class.
     *
     * This array includes identifier associations if present on this class.
     *
     * @return array
     */
    function getAssociationNames()
    {
        // TODO: Implement getAssociationNames() method.
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
    function getTypeOfField($fieldName)
    {
        // TODO: Implement getTypeOfField() method.
    }

    /**
     * Returns the target class name of the given association.
     *
     * @param string $assocName
     * @return string
     */
    function getAssociationTargetClass($assocName)
    {
        // TODO: Implement getAssociationTargetClass() method.
    }
}
