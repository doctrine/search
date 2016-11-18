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

namespace Doctrine\Search\ElasticSearch;

use Doctrine\Search\ResultDocumentInterface;

class ElasticaAdapter implements ResultDocumentInterface
{
    protected $elasticaDocument;

    /**
     * ElasticaAdapter constructor.
     */
    public function __construct($document)
    {
        $this->elasticaDocument = $document;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        $this->elasticaDocument->getId();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        $this->elasticaDocument->getType();
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        $this->elasticaDocument->getFields();
    }

    /**
     * @return mixed
     */
    public function hasFields()
    {
        $this->elasticaDocument->hasFields();
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        $this->elasticaDocument->getIndex();
    }

    /**
     * @return mixed
     */
    public function getScore()
    {
        $this->elasticaDocument->getScore();
    }

    /**
     * @return mixed
     */
    public function getHit()
    {
        $this->elasticaDocument->getHit();
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        $this->elasticaDocument->getVersion();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        $this->elasticaDocument->getData();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        $this->elasticaDocument->getSource();
    }

    /**
     * @return mixed
     */
    public function getDocument()
    {
        $this->elasticaDocument->getDocument();
    }
}
