<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class ThrowExceptionTest extends AbstractMageFunctionTestCase
{
    /**
     * @var ThrowException
     */
    protected $obj;

    /**
     * @return ThrowException
     */
    protected function getSubjectUnderTest()
    {
        return new ThrowException(
            $this->classMapperMock,
            $this->aliasMapperMock,
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
        );
    }

    /**
     * @dataProvider throwExceptionDataProvider
     * @param $inputFile
     * @param $index
     * @param $attrs
     * @param $expectedFile
     */
    public function testThrowException(
        $inputFile,
        $index,
        $attrs,
        $expectedFile
    ) {
        $this->executeTestAgainstExpectedFile($inputFile, $index, $attrs, $expectedFile);
    }

    public function throwExceptionDataProvider()
    {
        $data = [
            'throw_exception' => [
                'input' => 'throw_exception',
                'index' => 29,
                'attr' => [
                    'start_index' => 29,
                    'end_index' => 31,
                    'method' => 'throwException',
                    'class' => null,
                    'type' => MageFunctionInterface::MAGE_THROW_EXCEPTION,
                    'di_variable_name' => null,
                    'di_variable_class' => null,

                ],
                'expected' => 'throw_exception_expected',
            ],
        ];
        return $data;
    }
}
