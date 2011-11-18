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
class ClassMetadata extends ClassMetadataInfo
{
    /**
     * The ReflectionProperty instances of the mapped class.
     *
     * @var array
     */
    public $reflFields = array();

    /**
     * The prototype from which new instances of the mapped class are created.
     *
     * @var object
     */
    private $prototype;

    /**
     * Initializes a new ClassMetadata instance that will hold the object-document mapping
     * metadata of the class with the given name.
     *
     * @param string $documentName The name of the document class the new instance is used for.
     */
    public function __construct($documentName)
    {
        parent::__construct($documentName);
        $this->reflClass = new \ReflectionClass($documentName);
        $this->namespace = $this->reflClass->getNamespaceName();
        $this->setCollection($this->reflClass->getShortName());
    }

    /**
     * Map a field.
     *
     * @param array $mapping The mapping information.
     */
    public function mapField(array $mapping)
    {
        $mapping = parent::mapField($mapping);

        if ($this->reflClass->hasProperty($mapping['fieldName'])) {
            $reflProp = $this->reflClass->getProperty($mapping['fieldName']);
            $reflProp->setAccessible(true);
            $this->reflFields[$mapping['fieldName']] = $reflProp;
        }
    }

}
