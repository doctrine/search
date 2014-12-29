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

namespace Doctrine\Search;

use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Mapping\TypeMetadata;
use Doctrine\Search\Mapping\TypeMetadataFactory;



/**
 * Interface for a Doctrine SearchManager class to implement.
 *
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
interface SearchClient extends TypeMetadataFactory
{
    /**
     * Finds document by id.
     *
     * @param ClassMetadata $class
     * @param mixed $id
     * @param array $options
     * @throws \Doctrine\Search\NoResultException
     */
    public function find(ClassMetadata $class, $id, $options = array());

    /**
     * Finds document by specified field and value.
     *
     * @param ClassMetadata $class
     * @param string $field
     * @param mixed $value
     * @throws \Doctrine\Search\NoResultException
     */
    public function findOneBy(ClassMetadata $class, $field, $value);

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
     * Adds documents of a given type to the specified index
     *
     * @param ClassMetadata $class
     * @param array $documents Indexed by document id
     */
    public function addDocuments(ClassMetadata $class, array $documents);

    /**
     * Remove documents of a given type from the specified index
     *
     * @param ClassMetadata $class
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

    /**
     * Refresh the index to make documents available for search
     *
     * @param string $index
     */
    public function refreshIndex($index);

    /**
     * @param string $className
     * @return TypeMetadata
     */
    public function createTypeMetadata($className);

}
