<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

use Magento\Migration\Code\Processor\CallArgumentCollection;

/**
 * Class CallArgumentCollectionTest
 */
class CallArgumentCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\CallArgumentCollection
     */
    protected $model;
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);


        $this->model = $this->objectManager->getObject(
            'Magento\Migration\Code\Processor\CallArgumentCollection',
            [
            ]
        );

        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        $arr = [];
        for ($cnt=0; $cnt < 10; $cnt++) {
            /** @var TokenArgument $tokenArgument */
            $tokenArgument = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
            $arr[] = $tokenArgument;
        }
        $this->model->setArguments($arr);
    }

    /**
     * test SetArguments
     * test getArguments
     */
    public function testSetArguments()
    {
        $arr = [];
        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        $tokenArgument = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $arr[] = $tokenArgument;

        $result = $this->model->setArguments($arr);
        $this->assertEquals($this->model, $result);
        $result = $this->model->getArguments();
        $this->assertEquals($arr, $result);
    }

    /**
     * test AddArgument
     */
    public function testAddArgument()
    {
        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        $tokenArgument = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $result = $this->model->addArgument($tokenArgument);
        $this->assertEquals($this->model, $result);
        $this->assertEquals(11, count($this->model->getArguments()));
    }

    /**
     * test RemoveArgument
     */
    public function testRemoveArgument()
    {
        $this->assertEquals(10, count($this->model->getArguments()));
        $result = $this->model->removeArgument(2);
        $this->assertEquals($this->model, $result);
        $this->assertEquals(9, count($this->model->getArguments()));
    }

    /**
     * test GetCount
     */
    public function testGetCount()
    {
        $this->model->getCount();
        $this->assertEquals(10, count($this->model->getArguments()));
    }

    /**
     * test GetArgument
     */
    public function testGetArgument()
    {
        $arr = [];
        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        $tokenArgument1 = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $tokenArgument2 = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $arr[] = $tokenArgument1;
        $arr[] = $tokenArgument2;

        $this->model->setArguments($arr);

        $result = $this->model->getArgument(1);
        $this->assertEquals($tokenArgument1, $result);
        $result = $this->model->getArgument(2);
        $this->assertEquals($tokenArgument2, $result);
    }

    /**
     * test GetFirstArgument
     */
    public function testGetFirstArgument()
    {
        $arr = [];
        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        $tokenArgument1 = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $tokenArgument2 = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $arr[] = $tokenArgument1;
        $arr[] = $tokenArgument2;

        $this->model->setArguments($arr);

        $result = $this->model->getFirstArgument();
        $this->assertEquals($tokenArgument1, $result);
    }

    /**
     * test GetArgumentIndex
     */
    public function testGetArgumentIndex()
    {
        $arr = [];
        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        $tokenArgument1 = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $tokenArgument2 = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
        $arr[202] = $tokenArgument1;
        $arr[205] = $tokenArgument2;

        $this->model->setArguments($arr);
        $result = $this->model->getArgumentIndex(1);
        $this->assertEquals(202, $result);
        $result = $this->model->getArgumentIndex(2);
        $this->assertEquals(205, $result);
    }
}
