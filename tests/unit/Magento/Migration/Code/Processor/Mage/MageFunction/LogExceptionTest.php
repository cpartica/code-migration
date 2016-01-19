<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor\Mage\MageFunctionInterface;

class LogExceptionTest extends AbstractMageFunctionTestCase
{
    /**
     * @var LogException
     */
    protected $obj;

    /**
     * @return LogException
     */
    protected function getSubjectUnderTest()
    {
        return new LogException(
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
            'log_exception' => [
                'input' => 'log_exception',
                'index' => 29,
                'attr' => [
                    'start_index' => 29,
                    'end_index' => 31,
                    'method' => 'logException',
                    'class' => '\Psr\Log\LoggerInterface',
                    'type' => MageFunctionInterface::MAGE_LOG_EXCEPTION,
                    'di_variable_name' => 'logger',
                    'di_variable_class' => '\Psr\Log\LoggerInterface',

                ],
                'expected' => 'log_exception_expected',
            ],
        ];
        return $data;
    }
}
