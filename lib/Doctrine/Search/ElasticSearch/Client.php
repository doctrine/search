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

/**
 * SearchManager for ElasticSearch-Backend
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author  Mike Lohmann <mike.h.lohmann@googlemail.com>
 * @author  Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Client implements SearchClientInterface
{
    /**
     * @var \Elastica_Client
     */
    private $client;

    /**
     * @param \Elastica_Client $client
     */
    public function __construct(\Elastica\Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function find($index, $type, $query)
    {
        $index = $this->client->getIndex($index);
        return iterator_to_array($index->search($query));
    }

    /**
     * {@inheritDoc}
     */
    public function createIndex($index, $type, array $data)
    {
        $index = $this->client->getIndex($index);
        $index->create();

        $index->addDocuments($data);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteIndex($index)
    {
        $index = $this->client->getIndex($index);
        $index->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function bulkSearch(array $data)
    {
    }
}