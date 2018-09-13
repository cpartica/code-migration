<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code;

class ClassMappingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Mapping\ClassMapping
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMockBuilder('\Magento\Migration\Logger\Logger')
            ->disableOriginalConstructor()->getMock();

        $this->obj = new \Magento\Migration\Mapping\ClassMapping(
            $this->loggerMock
        );
    }

    public function testMapM1Class()
    {
        $this->assertEquals("\\Magento\\Backend\\Helper\\Data", $this->obj->mapM1Class('Mage_Admin_Helper_Data'));
        $this->assertEquals(
            "\\Magento\\Catalog\\Model\\Product\\Media\\ConfigInterface",
            $this->obj->mapM1Class('Mage_Media_Model_Image_Config_Interface')
        );
        $this->assertEquals(
            "obsolete",
            $this->obj->mapM1Class('Mage')
        );
    }
}
