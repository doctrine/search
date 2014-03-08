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
use Doctrine\Common\Collections\ArrayCollection;

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
     * @param ClassMetadata $classes
     * @param mixed $id
     * @throws Doctrine\Search\Exception\NoResultException
     */
    public function find(ClassMetadata $class, $id);
    
    /**
     * Finds document by specified key and value.
     *
     * @param ClassMetadata $class
     * @param mixed $value
     * @throws Doctrine\Search\Exception\NoResultException
     */
    public function findOneBy(ClassMetadata $class, $key, $value);
    
    /**
     * Finds all documents
     *
     * @param array $classes
     */
    public function findAll(array $classes);
    
    /**
     * Finds documents by a specific query.
     *
     * @param object $query
     * @param array $classes
     */
    public function search($query, array $classes);

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
     * Refresh the index to make documents available for search
     */
    public function refreshIndex($index);
    
    /**
     * Create a document type mapping as defined in the
     * class annotations
     *
     * @param ClassMetadata $metadata
     */
    public function createType(ClassMetadata $metadata);
    
    /**
     * Delete a document type
     *
     * @param ClassMetadata $metadata
     */
    public function deleteType(ClassMetadata $metadata);

    /**
     * Adds documents of a given type to the specified index
     *
     * @param ClassMetadata $class
     * @param array $documents Indexed by document id
     */
    public function addDocuments(ClassMetadata $class, array $documents);

    /**
     * Remove documents of a given type from the specified index
     *
     * @param ClassMetadata
     * @param array $documents Indexed by document id
     */
    public function removeDocuments(ClassMetadata $class, array $documents);

    /**
     * Remove all documents of a given type from the specified index
     * without deleting the index itself
     *
     * @param ClassMetadata $class
     * @param object $query
     */
    public function removeAll(ClassMetadata $class, $query = null);
}
