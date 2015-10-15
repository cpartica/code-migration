<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class ClassProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\ClassProcessor
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Mapping\ClassMapping|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMapMock;

    /**
     * @var \Magento\Migration\Mapping\Alias|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aliasMapMock;

    /**
     * @var \Magento\Migration\Mapping\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Migration\Code\Processor\ConstructorHelperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $constructorHelperFactoryMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    public function setUp()
    {
        $this->loggerMock = $this->getMock('\Magento\Migration\Logger\Logger');

        $this->classMapMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\ClassMapping'
        )->disableOriginalConstructor()
            ->getMock();
        $this->aliasMapMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\Alias'
        )->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\Context'
        )->getMock();

        $this->constructorHelperFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\ConstructorHelperFactory'
        )->setMethods(['create'])
            ->getMock();

        $this->tokenHelper = $this->setupTokenHelper($this->loggerMock);

        /** @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory $argumentFactoryMock */
        $argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $argumentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\Mage\MageFunction\Argument();
                }
            );
        $this->constructorHelperFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () use ($argumentFactoryMock) {
                    return new \Magento\Migration\Code\Processor\ConstructorHelper (
                        $this->loggerMock,
                        $this->tokenHelper,
                        $argumentFactoryMock
                    );
                }
            );

        $this->obj = new \Magento\Migration\Code\Processor\ClassProcessor(
            $this->classMapMock,
            $this->aliasMapMock,
            $this->loggerMock,
            $this->contextMock,
            $this->constructorHelperFactoryMock,
            $this->tokenHelper
        );
    }

    /**
     * @dataProvider processNameSpaceDataProvider
     */
    public function testProcessNameSpace($inputFile, $expectedFile)
    {
        $file = __DIR__ . '/_files/class_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);
        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/class_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processNameSpaceDataProvider()
    {
        $data = [
            'namespace_with_doc_no_whitespace' => [
                'input' => 'namespace_with_doc_no_whitespace',
                'expected' => 'namespace_with_doc_no_whitespace_expected'
            ],
            'namespace_with_doc_with_whitespace' => [
                'input' => 'namespace_with_doc_with_whitespace',
                'expected' => 'namespace_with_doc_with_whitespace_expected'
            ],
            'namespace_without_doc_no_whitespace' => [
                'input' => 'namespace_without_doc_no_whitespace',
                'expected' => 'namespace_without_doc_no_whitespace_expected'
            ],
            'namespace_without_doc_with_whitespace' => [
                'input' => 'namespace_without_doc_with_whitespace',
                'expected' => 'namespace_without_doc_with_whitespace_expected'
            ],
            'namespace_no_class' => [
                'input' => 'namespace_no_class',
                'expected' => 'namespace_no_class_expected'
            ],
            'namespace_no_open_tag' => [
                'input' => 'namespace_no_open_tag',
                'expected' => 'namespace_no_open_tag_expected'
            ],
        ];
        return $data;
    }

    /**
     * @param string $inputFile
     * @param string $expectedFile
     * @dataProvider processClassReferenceDataProvider
     */
    public function testProcessClassReferences($inputFile, $expectedFile)
    {
        $file = __DIR__ . '/_files/class_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $classMap = [
            ['Mage_Type', '\\Magento\\Type'],
            ['Varien_Type', '\\Magento\\Framework\\Type'],
            ['Mage_Type_Obsolete', 'obsolete'],
        ];
        $this->classMapMock->expects($this->exactly(3))
            ->method('mapM1Class')
            ->willReturnMap($classMap);

        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/class_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processClassReferenceDataProvider()
    {
        $data = [
            'argument_type' => [
                'input' => 'argument_type',
                'expected' => 'argument_type_expected'
            ],
            'static_class_reference' => [
                'input' => 'static_class_reference',
                'expected' => 'static_class_reference_expected',
            ],
            'instance_of' => [
                'input' => 'instance_of',
                'expected' => 'instance_of_expected',
            ],
        ];
        return $data;
    }

    /**
     * @param string $inputFile
     * @param string $expectedFile
     * @dataProvider processInheritanceDataProvider
     */
    public function testProcessInheritance($inputFile, $expectedFile)
    {
        include_once __DIR__ . '/_files/class_processor/Magento/Type.php';
        include_once __DIR__ . '/_files/class_processor/Magento/EmptyConstructorType.php';
        $file = __DIR__ . '/_files/class_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);
        $tokens = $this->tokenHelper->refresh($tokens);

        $classMap = [
            ['Mage_Type', '\\Magento\\Type'],
            ['Mage_EmptyConstructorType', '\\Magento\\EmptyConstructorType'],
        ];
        $this->classMapMock->expects($this->exactly(1))
            ->method('mapM1Class')
            ->willReturnMap($classMap);

        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/class_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processInheritanceDataProvider()
    {
        $data = [
            'no_existing_constructor_argument' => [
                'input' => 'no_existing_constructor_argument',
                'expected' => 'no_existing_constructor_argument_expected'
            ],
            'no_constructor' => [
                'input' => 'no_constructor',
                'expected' => 'no_constructor_expected'
            ],
            'existing_parent_constructor_call' => [
                'input' => 'existing_parent_constructor_call',
                'expected' => 'existing_parent_constructor_call_expected',
            ],
            'empty_parent_constructor' => [
                'input' => 'empty_parent_constructor',
                'expected' => 'empty_parent_constructor_expected',
            ],
            'empty_parent_constructor_existing_parent_call' => [
                'input' => 'empty_parent_constructor_existing_parent_call',
                'expected' => 'empty_parent_constructor_existing_parent_call_expected',
            ],
            'empty_parent_constructor_no_existing_constructor' => [
                'input' => 'empty_parent_constructor_no_existing_constructor',
                'expected' => 'empty_parent_constructor_no_existing_constructor_expected',
            ],
        ];
        return $data;
    }


    /**
     * @param \Magento\Migration\Logger\Logger $loggerMock
     * @return \Magento\Migration\Code\Processor\TokenHelper
     */
    public function setupTokenHelper(\Magento\Migration\Logger\Logger $loggerMock)
    {
        $argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $argumentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\Mage\MageFunction\Argument();
                }
            );
        $tokenFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $tokenFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgument();
                }
            );


        $tokenCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $tokenCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgumentCollection();
                }
            );

        $callCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\CallArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $callCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\CallArgumentCollection();
                }
            );

        $tokenHelper = new \Magento\Migration\Code\Processor\TokenHelper(
            $loggerMock,
            $argumentFactoryMock,
            $tokenFactoryMock,
            $tokenCollectionFactoryMock,
            $callCollectionFactoryMock
        );

        return $tokenHelper;
    }
}
