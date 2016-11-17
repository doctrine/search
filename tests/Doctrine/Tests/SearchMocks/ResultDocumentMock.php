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

namespace Doctrine\Tests\SearchMocks;

class ResultDocumentMock
{
    public function getData()
    {
        $data = [
            'testField1' => 'test data 1',
            'testField2' => 'test data 2',
            'testField3' => 'test data 3',
        ];

        return $data;
    }

    public function getVersion()
    {
        return 'v_1';
    }

    public function hasFields()
    {
        return true;
    }

    public function getFields()
    {
        $data = [
            'testField1' => 'test data 1',
            'testField2' => 'test data 2',
            'testField3' => 'test data 3',
        ];

        return $data;
    }

    public function getId()
    {
        return 10;
    }

    public function getIndex()
    {
        return 'testIndex';
    }

    public function getType()
    {
        return 'testType';
    }
}
