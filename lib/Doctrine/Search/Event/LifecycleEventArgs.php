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

namespace Doctrine\Search\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Search\SearchManager;

/**
 * Lifecycle Events are triggered by the UnitOfWork during lifecycle transitions
 * of entities.
 *
 * @link   www.doctrine-project.org
 * @since  2.0
 * @author Roman Borschel <roman@code-factory.de>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class LifecycleEventArgs extends EventArgs
{
    /**
     * @var \Doctrine\Search\SearchManager
     */
    private $sm;

    /**
     * @var object
     */
    private $entity;

    /**
     * Constructor
     *
     * @param object $entity
     * @param \Doctrine\Search\SearchManager $em
     */
    public function __construct($entity, SearchManager $sm)
    {
        $this->entity = $entity;
        $this->sm     = $sm;
    }

    /**
     * Retrieve associated Entity.
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Retrieve associated SearchManager.
     *
     * @return \Doctrine\Search\SearchManager
     */
    public function getSearchManager()
    {
        return $this->sm;
    }
}
