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

namespace Doctrine\Search\Serializer;

use Doctrine\Search\SerializerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;



class JMSSerializer implements SerializerInterface
{

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var SerializationContext
     */
    protected $context;



    public function __construct(Serializer $serializer = NULL, SerializationContext $context = NULL)
    {
        $this->context = $context;
        $this->serializer = $serializer;
    }



    public function serialize($object)
    {
        $context = $this->context ? clone $this->context : NULL;

        return json_decode($this->getSerializer()->serialize($object, 'json', $context), TRUE);
    }



    public function deserialize($entityName, $data)
    {
        return $this->getSerializer()->deserialize($data, $entityName, 'json');
    }



    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (!$this->serializer === NULL) {
            $this->serializer = SerializerBuilder::create()
                ->setPropertyNamingStrategy(new SerializedNameAnnotationStrategy(new IdenticalPropertyNamingStrategy()))
                ->addDefaultHandlers()
                ->build();
        }

        return $this->serializer;
    }

}
