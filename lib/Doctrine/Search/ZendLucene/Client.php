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

namespace Doctrine\Search\ZendLucene;


use Doctrine\Search\SearchClientInterface;
use Doctrine\Search\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * SearchManager for ZendLucene-Backend
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class Client implements SearchClientInterface
{

    private $config;

    private $connection;

    /*
    * @param Connection $conn
    * @param Configuration $config
    */
    public function __construct(Connection $conn = null, Configuration $config = null)
    {
        $this->connection = $conn;
        $this->config = $config;
    }

    public function find($index, $type, $query)
    {

    }

    public function createIndex($index, array $data)
    {

    }

    public function createType(ClassMetadata $metadata)
    {

    }

    public function getIndex($index)
    {

    }

    public function deleteIndex($index)
    {

    }

    public function addDocuments($index, $type, array $documents)
    {

    }

    public function removeDocuments($index, $type, array $documents)
    {

    }

    public function removeAll($index, $type)
    {

    }
}
