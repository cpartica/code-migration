<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class TokenHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentFactoryMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenFactoryMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenCollectionFactoryMock;

    /**
     * @var \Magento\Migration\Code\Processor\CallArgumentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $callCollectionFactoryMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var array
     */
    protected static $tokens;

    public static function setUpBeforeClass()
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/tokenHelperTest.file');
        self::$tokens = token_get_all($fileContent);
        for ($i = 0; $i < count(self::$tokens); $i++) {
            if (is_array(self::$tokens[$i])) {
                self::$tokens[$i][3] = token_name(self::$tokens[$i][0]);
            }
        }
    }

    public function setUp()
    {
        $this->argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $this->argumentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\Mage\MageFunction\Argument();
                }
            );
        $this->tokenFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $this->tokenFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgument();
                }
            );


        $this->tokenCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $this->tokenCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgumentCollection();
                }
            );


        $this->callCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\CallArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $this->callCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\CallArgumentCollection();
                }
            );

        $this->loggerMock = $this->getMockBuilder('\Magento\Migration\Logger\Logger')
            ->disableOriginalConstructor()->getMock();

        $this->obj = new \Magento\Migration\Code\Processor\TokenHelper(
            $this->loggerMock,
            $this->argumentFactoryMock,
            $this->tokenFactoryMock,
            $this->tokenCollectionFactoryMock,
            $this->callCollectionFactoryMock
        );
    }

    public function testGetNextTokenIndex()
    {
        $tokens = [
            [T_STRING, 'Mage', 1],
            [T_WHITESPACE, "\n\t", 1],
            ',',
            [T_WHITESPACE, "\n\t", 2],
            [T_DOUBLE_COLON, '::', 3],
        ];

        $this->assertEquals(2, $this->obj->getNextTokenIndex($tokens, 0));
        $this->assertEquals(4, $this->obj->getNextTokenIndex($tokens, 0, 1));
        $this->assertNull($this->obj->getNextTokenIndex($tokens, 0, 2));
    }

    public function testSkipFunctionCall()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testSkipFunctionCall');
        $increment = 23;
        $index = $this->obj->skipMethodCall(self::$tokens, $startingIndex);
        $this->assertEquals($startingIndex + $increment, $index);
    }

    /**
     * @expectedException \Exception
     */
    public function testSkipFunctionCallNoEnd()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testSkipFunctionCallNoEnd');
        $index = $this->obj->skipMethodCall(self::$tokens, $startingIndex);
    }

    public function testSkipBlock()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testSkipBlock');
        $increment = 27;
        $index = $this->obj->skipBlock(self::$tokens, $startingIndex);
        $this->assertEquals($startingIndex + $increment, $index);
    }

    /**
     * @expectedException \Exception
     */
    public function testSkipBlockMismatched()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testSkipBlockMismatched');
        $index = $this->obj->skipBlock(self::$tokens, $startingIndex);
    }

    public function testGetNextIndexOfSimpleToken()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetNextIndexOfSimpleToken');
        $increment = 8;
        $index = $this->obj->getNextIndexOfSimpleToken(self::$tokens, $startingIndex, ',');
        $this->assertEquals($startingIndex + $increment, $index);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetNextIndexOfSimpleTokenNotFound()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetNextIndexOfSimpleTokenNotFound');
        $index = $this->obj->getNextIndexOfSimpleToken(self::$tokens, $startingIndex, ')');
    }

    public function testGetNextIndexOfTokenType()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetNextIndexOfTokenType');
        $increment = 9;
        $index = $this->obj->getNextIndexOfTokenType(
            self::$tokens,
            $startingIndex,
            T_CONSTANT_ENCAPSED_STRING,
            "'b'"
        );
        $this->assertEquals($startingIndex + $increment, $index);
    }

    public function testGetNextIndexOfTokenTypeNotFound()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetNextIndexOfTokenType');
        $index = $this->obj->getNextIndexOfTokenType(
            self::$tokens,
            $startingIndex,
            T_CONSTANT_ENCAPSED_STRING,
            "'not_found'"
        );
        $this->assertNull($index);
    }

    public function testGetPrevIndexOfTokenType()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetPrevIndexOfTokenType');
        $increment = -9;
        $index = $this->obj->getPrevIndexOfTokenType(
            self::$tokens,
            $startingIndex,
            T_CONSTANT_ENCAPSED_STRING,
            "'b'"
        );
        $this->assertEquals($startingIndex + $increment, $index);
    }

    public function testGetPrevIndexOfTokenTypeNotFound()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetNextIndexOfTokenType');
        $index = $this->obj->getPrevIndexOfTokenType(
            self::$tokens,
            $startingIndex,
            T_CONSTANT_ENCAPSED_STRING,
            "'not_found'"
        );
        $this->assertNull($index);
    }

    public function testGetFunctionArguments()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetFunctionArguments');
        $arguments = $this->obj->getFunctionArguments(self::$tokens, $startingIndex);
        $this->assertEquals(4, count($arguments));

        $this->assertNull($arguments[0]->getType());
        $this->assertEquals('$input1', $arguments[0]->getName());
        $this->assertFalse($arguments[0]->isOptional());

        $this->assertNull($arguments[1]->getType());
        $this->assertEquals('$input2', $arguments[1]->getName());
        $this->assertTrue($arguments[1]->isOptional());

        $this->assertEquals('array', $arguments[2]->getType());
        $this->assertEquals('$input3', $arguments[2]->getName());
        $this->assertTrue($arguments[2]->isOptional());

        $this->assertEquals('MyType', $arguments[3]->getType());
        $this->assertEquals('$input4', $arguments[3]->getName());
        $this->assertTrue($arguments[3]->isOptional());
    }

    public function testGetFunctionArgumentsEmpty()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetFunctionArgumentsEmpty');
        $arguments = $this->obj->getFunctionArguments(self::$tokens, $startingIndex);
        $this->assertEmpty($arguments);
    }

    public function testGetFunctionStartingIndex()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetFunctionStartingIndex');
        for (; $startingIndex < count(self::$tokens); $startingIndex++) {
            if (is_array(self::$tokens[$startingIndex]) && self::$tokens[$startingIndex][0] == T_FUNCTION) {
                break;
            }
        }

        $increment = -5;
        $index = $this->obj->getFunctionStartingIndex(
            self::$tokens,
            $startingIndex
        );
        $this->assertEquals($startingIndex + $increment, $index);
    }

    public function testGetFunctionStartingIndexNoDocComment()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetFunctionStartingIndexNoDocComment');
        for (; $startingIndex < count(self::$tokens); $startingIndex++) {
            if (is_array(self::$tokens[$startingIndex]) && self::$tokens[$startingIndex][0] == T_FUNCTION) {
                break;
            }
        }

        $increment = -3;
        $index = $this->obj->getFunctionStartingIndex(
            self::$tokens,
            $startingIndex
        );
        $this->assertEquals($startingIndex + $increment, $index);
    }

    public function testGetNextLineIndex()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetNextLineIndex');

        $increment = 1;
        $index = $this->obj->getNextLineIndex(
            self::$tokens,
            $startingIndex,
            self::$tokens[$startingIndex][2]
        );
        $this->assertEquals($startingIndex + $increment, $index);

    }

    public function testGetPrevLineIndex()
    {
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetPrevLineIndex');

        $increment = -2;
        $index = $this->obj->getPrevLineIndex(
            self::$tokens,
            $startingIndex,
            self::$tokens[$startingIndex][2]
        );
        $this->assertEquals($startingIndex + $increment, $index);

    }

    public function testGetCallArguments()
    {
        //$this->function1($this, 'abc', array ( 'a','b' ), null);
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetCallArguments');

        $argumentCollection = $this->obj->getCallArguments(self::$tokens, $startingIndex);
        $this->assertEquals(4, $argumentCollection->getCount());

        $argument = $argumentCollection->getArgument(1);
        $tokens = $argument->getTokens();
        $this->assertEquals(1, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_VARIABLE, $firstToken->getToken()[0]);
        $this->assertEquals('$this', $firstToken->getToken()[1]);

        $argument = $argumentCollection->getArgument(2);
        $tokens = $argument->getTokens();
        $this->assertEquals(1, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_CONSTANT_ENCAPSED_STRING, $firstToken->getToken()[0]);
        $this->assertEquals("'abc'", $firstToken->getToken()[1]);

        $argument = $argumentCollection->getArgument(3);
        $tokens = $argument->getTokens();
        $this->assertEquals(6, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_ARRAY, $firstToken->getToken()[0]);
        $this->assertEquals('array', $firstToken->getToken()[1]);

        $argument = $argumentCollection->getArgument(4);
        $tokens = $argument->getTokens();
        $this->assertEquals(1, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_STRING, $firstToken->getToken()[0]);
        $this->assertEquals('null', $firstToken->getToken()[1]);
    }

    public function testGetCallArgumentsNoTrim()
    {
        //$this->function1($this, 'abc', array ( 'a','b' ), null);
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetCallArguments');

        $argumentCollection = $this->obj->getCallArguments(self::$tokens, $startingIndex, false);
        $this->assertEquals(4, $argumentCollection->getCount());

        $argument = $argumentCollection->getArgument(1);
        $tokens = $argument->getTokens();
        $this->assertEquals(1, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_VARIABLE, $firstToken->getToken()[0]);
        $this->assertEquals('$this', $firstToken->getToken()[1]);

        $argument = $argumentCollection->getArgument(2);
        $tokens = $argument->getTokens();
        $this->assertEquals(2, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_WHITESPACE, $firstToken->getToken()[0]);
        $secondToken = array_shift($tokens);
        $this->assertEquals(T_CONSTANT_ENCAPSED_STRING, $secondToken->getToken()[0]);
        $this->assertEquals("'abc'", $secondToken->getToken()[1]);

        $argument = $argumentCollection->getArgument(3);
        $tokens = $argument->getTokens();
        $this->assertEquals(10, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_WHITESPACE, $firstToken->getToken()[0]);
        $secondToken = array_shift($tokens);
        $this->assertEquals(T_ARRAY, $secondToken->getToken()[0]);
        $this->assertEquals('array', $secondToken->getToken()[1]);

        $argument = $argumentCollection->getArgument(4);
        $tokens = $argument->getTokens();
        $this->assertEquals(3, count($tokens));
        $firstToken = array_shift($tokens);
        $this->assertEquals(T_WHITESPACE, $firstToken->getToken()[0]);
        $secondToken = array_shift($tokens);
        $this->assertEquals(T_STRING, $secondToken->getToken()[0]);
        $this->assertEquals('null', $secondToken->getToken()[1]);
    }

    public function testGetCallArgumentsEmpty()
    {
        //$this->function3( );
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetCallArgumentsEmpty');

        $argumentCollection = $this->obj->getCallArguments(self::$tokens, $startingIndex);
        $this->assertEquals(0, $argumentCollection->getCount());

    }

    public function testGetCallArgumentsOneArgument()
    {
        //$this->function1('abc' );
        $startingIndex = $this->getTestStartIndex(self::$tokens, 'testGetCallArgumentsOneArgument');

        $argumentCollection = $this->obj->getCallArguments(self::$tokens, $startingIndex);
        $this->assertEquals(1, $argumentCollection->getCount());
    }

    public function testReplaceCallArgumentsTokens()
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/tokenHelperTest.file');
        $tokens = token_get_all($fileContent);
        for ($i = 0; $i < count(self::$tokens); $i++) {
            if (is_array($tokens[$i])) {
                $tokens[$i][3] = token_name($tokens[$i][0]);
            }
        }

        //$this->function1($this, 'abc', array ( 'a','b' ), null);
        $startingIndex = $this->getTestStartIndex($tokens, 'testReplaceCallArgumentsTokens');

        $replacementTokens = new \Magento\Migration\Code\Processor\TokenArgumentCollection();
        $token = new \Magento\Migration\Code\Processor\TokenArgument();
        $token->setName('hello');
        $replacementTokens->addToken($token, 0);
        $this->obj->replaceCallArgumentsTokens($tokens, $startingIndex, $replacementTokens);

        $newArguments = $this->obj->getCallArguments($tokens, $startingIndex);
        $this->assertEquals(1, $newArguments->getCount());
        $argument = $newArguments->getFirstArgument();
        $this->assertEquals('hello', $argument->getString());
    }

    public function testReplaceCallArgumentsTokensNoArgument()
    {
        $fileContent = file_get_contents(__DIR__ . '/_files/tokenHelperTest.file');
        $tokens = token_get_all($fileContent);
        for ($i = 0; $i < count(self::$tokens); $i++) {
            if (is_array($tokens[$i])) {
                $tokens[$i][3] = token_name($tokens[$i][0]);
            }
        }

        //$this->function3();
        $this->loggerMock->expects($this->once())
            ->method('warn');
        $startingIndex = $this->getTestStartIndex($tokens, 'testReplaceCallArgumentsTokensNoArgument');

        $replacementTokens = new \Magento\Migration\Code\Processor\TokenArgumentCollection();
        $token = new \Magento\Migration\Code\Processor\TokenArgument();
        $token->setName('hello');
        $replacementTokens->addToken($token, 0);
        $this->obj->replaceCallArgumentsTokens($tokens, $startingIndex, $replacementTokens);
    }



    /**
     * @param array $tokens
     * @param string $marker
     * @return int|null
     */
    protected function getTestStartIndex($tokens, $marker)
    {
        $marker = '//' . $marker;
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if (is_array($tokens[$i])
                && $tokens[$i][0] == T_COMMENT
                && strpos($tokens[$i][1], $marker) === 0) {
                return $i;
            }
        }

        return null;
    }
}
