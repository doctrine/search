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

namespace Doctrine\Search\Solr;

use Doctrine\Search\SearchManager;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * SearchManager for Solr-Backend
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class SolrSearchManager implements SearchManager
{
    /* 
     *  Holds the ObjectManager to access documents or entities
    */
    private $objectManager;
    
    private $config;
    
    private $connection;
    
    /*
     * @param Doctrine\Search\Solr\Connection $conn
     * @param Doctrine\Search\Solr\Configuration $config
     * @param Doctrine\Common\Persitence\ObjectManager $om
     */
    public function __construct(Connection $conn = null, Configuration $config = null, ObjectManager $om = null)
    {
        $this->$objectManager = $om;
        $this->$connection = $conn;
        $this->$config = $config;
    }

    public function find($searchString)
    {
        
    }
    
    public function findByApiCommand($apiCommand)
    {
        
    }
}