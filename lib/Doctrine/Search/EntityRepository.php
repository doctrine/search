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

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Search\SearchManager;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Search\Exception\DoctrineSearchException;

class EntityRepository implements ObjectRepository
{
    /**
     * @var string
     */
    protected $_entityName;

    /**
     * @var \Doctrine\Search\Mapping\ClassMetadata
     */
    private $_class;

    /**
     * @var \Doctrine\Search\SearchManager
     */
    private $_sm;

    public function __construct(SearchManager $sm, ClassMetadata $class)
    {
        $this->_sm = $sm;
        $this->_entityName = $class->className;
        $this->_class = $class;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     * @return object The object.
     */
    public function find($id)
    {
        return $this->_sm->find($this->_entityName, $id);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return mixed The objects.
     */
    public function findAll()
    {
        throw new DoctrineSearchException('Not yet implemented.');
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @throws \UnexpectedValueException
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return mixed The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new DoctrineSearchException('Not yet implemented.');
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $options = array('field' => key($criteria));
        $value = current($criteria);
        return $this->_sm->getUnitOfWork()->load($this->_class, $value, $options);
    }

    /**
     * Execute a direct search query on the associated index and type
     *
     * @param object $query
     */
    public function search($query)
    {
        return $this->_sm->getUnitOfWork()->loadCollection(array($this->_class), $query);
    }

    /**
     * Execute a direct delete by query on the associated index and type
     *
     * @param object $query
     */
    public function delete($query)
    {
        $this->_sm->getClient()->removeAll($this->_class, $query);
    }

    /**
     * Returns the class name of the object managed by the repository
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_entityName;
    }

    /**
     * Returns the class metadata managed by the repository
     *
     * @return string
     */
    public function getClassMetadata()
    {
        return $this->_class;
    }

    /**
     * Returns the search manager
     *
     * @return \Doctrine\Search\SearchManager
     */
    public function getSearchManager()
    {
        return $this->_sm;
    }
}
