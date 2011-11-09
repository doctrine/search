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

namespace Doctrine\Search\ElasticSearch;

use Doctrine\Search\SearchClientInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Search\Http\ClientInterface as HttpClientInterface;

/**
 * SearchManager for ElasticSearch-Backend
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 */
class Client implements SearchClientInterface
{
    
    private $client;
    
    /*
     * @param Connection $conn
     * @param Configuration $config
     */
    public function __construct(HttpClientInterface $client = null)
    {
        $this->client = $client;
    }

    /**
     * (non-PHPdoc)
     * @see Doctrine\Search.SearchClientInterface::find()
     */
    public function find($query)
    {
       assert(is_string($query));
        
       $response = $this->client->sendRequest('GET', $query);
       $content = $response->getContent();
       $decodedJson = json_decode($content);
       
       if($decodedJson == NULL) {
           throw new Exception\JsonDecodeException();
       }
       
       return $decodedJson;
    }

    /**
     * (non-PHPdoc)
     * @see Doctrine\Search.SearchClientInterface::createIndex()
     * 
     * @return Doctrine\Search\Http\ResponseInterface
     */
    public function createIndex($index, array $data)
    {
        $encodedJson = json_encode($data);
        
        if($encodedJson == NULL) {
           throw new Exception\JsonEncodeException($data);
        }
        
        return $this->client->sendRequest('PUT', $index, $encodedJson);
        
    }
    
    public function updateIndex(array $data)
    {
        
    }

    /**
     *
     * @param array $data
     */
    public function deleteIndex($index)
    {
        // TODO: Implement deleteIndex() method.
    }

    /**
     * @param array $data
     */
    public function bulkAction(array $data)
    {
        // TODO: Implement bulkAction() method.
    }

}