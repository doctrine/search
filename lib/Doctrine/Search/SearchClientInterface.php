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

namespace Doctrine\Search;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Search\Mapping\ClassMetadata;

/**
 * Interface for a Doctrine SearchManager class to implement.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
interface SearchClientInterface
{
    /**
     * Finds document by id.
     *
     * @param string $index
     * @param string $type
     * @param mixed $id
     * @throws Doctrine\Search\Exception\NoResultException
     */
    public function find($index, $type, $id);
    
    /**
     * Finds document by specified key and value.
     *
     * @param string $index
     * @param string $type
     * @param string $key
     * @param mixed $value
     * @throws Doctrine\Search\Exception\NoResultException
     */
    public function findOneBy($index, $type, $key, $value);
    
    /**
     * Finds all documents by type
     *
     * @param string $index
     * @param string $type
     */
    public function findAll($index, $type);
    
    /**
     * Finds documents by a specific query.
     *
     * @param object $query
     * @param string $index
     * @param string $type
     */
    public function search($query, $index = null, $type = null);

    /**
     * Creates a document index
     *
     * @param string $name The name of the index.
     * @param string $config The configuration of the index.
     */
    public function createIndex($name, array $config = array());

    /**
     * Gets a document index reference
     *
     * @param string $name The name of the index.
     */
    public function getIndex($name);

    /**
     * Deletes an index and its types and documents
     *
     * @param string $index
     */
    public function deleteIndex($index);

    /**
     * Create a document type mapping as defined in the
     * class annotations
     *
     * @param ClassMetadata $metadata
     */
    public function createType(ClassMetadata $metadata);

    /**
     * Adds documents of a given type to the specified index
     *
     * @param string $index
     * @param string $type
     * @param array $documents Indexed by document id
     */
    public function addDocuments($index, $type, array $documents);

    /**
     * Remove documents of a given type from the specified index
     *
     * @param string $index
     * @param string $type
     * @param array $documents Indexed by document id
     */
    public function removeDocuments($index, $type, array $documents);

    /**
     * Remove all documents of a given type from the specified index
     * without deleting the index itself
     *
     * @param string $index
     * @param string $type
     */
    public function removeAll($index, $type);
}
