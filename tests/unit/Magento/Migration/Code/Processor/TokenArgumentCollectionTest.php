<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

use Magento\Migration\Code\Processor\TokenArgumentCollection;

/**
 * Class TokenArgumentCollectionTest
 */
class TokenArgumentCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\TokenArgumentCollection
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
            '\Magento\Migration\Code\Processor\TokenArgumentCollection',
            [
            ]
        );
    }

    /**
     * test AddToken
     * tests getToken, getFirstToken
     * @param mixed[]|string $token
     * @dataProvider addTokenProvider
     */
    public function testAddToken($token)
    {
        $result = $this->model->addToken($token);
        $this->assertEquals($this->model, $result);
        $argument = $this->model->getFirstToken();
        $this->assertEquals($token, $argument);
        $argument = $this->model->getToken(1);
        $this->assertEquals($token, $argument);
    }

    /**
     * test GetTokens
     */
    public function testGetTokens()
    {
        $this->setupTokens();
        $result = $this->model->getTokens();
        $this->assertEquals(11, count($result));
        foreach ($this->addTokenProvider() as $key => $current) {
            $this->assertEquals(current($current), $result[$key]);
        }
    }

    /**
     * test SetTokens
     * tests getTokens
     * @param TokenArgument[] $tokens
     * @dataProvider addTokenProvider
     */
    public function testSetTokens($tokens)
    {
        $this->setupTokens();
        $result = $this->model->setTokens($tokens);
        $this->assertEquals($this->model, $result);
        $result = $this->model->getTokens();
        $this->assertEquals($tokens, $result);
    }

    /**
     * test RemoveToken
     * @param TokenArgument[] $tokens
     * @dataProvider genericDataProvider
     */
    public function testRemoveToken($tokens)
    {
        $this->model->setTokens($tokens);
        $result = $this->model->removeToken(202);
        $this->assertEquals($this->model, $result);
        $result = $this->model->getTokens();
        $this->assertEquals(current($tokens), current($result));
    }

    /**
     * test GetString
     */
    public function testGetString()
    {

        $className = 'Magento\Migration\Code\Processor\TokenArgument';
        /** @var TokenArgument $tokenArgument */
        $tokenArgument = $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();

        $tokenArgument->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('a');

        $this->model->addToken($tokenArgument);
        $this->model->addToken($tokenArgument);
        $this->model->addToken($tokenArgument);
        $result = $this->model->getString();
        $this->assertEquals('aaa', $result);
    }

    protected function setupTokens()
    {
        foreach ($this->addTokenProvider() as $current) {
            $this->model->addToken(current($current));
        }
    }


    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function genericDataProvider()
    {
        return [
            [
                [
                    200 => [
                        0 => 310,
                        1 => 'getTable',
                        2 => 101,
                        3 => 'T_STRING',
                    ],
                    202 => ')'
                ]
            ]
        ];
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function addTokenProvider()
    {
        return [
            [
                [
                    0 => 379,
                    1 => ' ',
                    2 => 101,
                    3 => 'T_WHITESPACE',
                ],
            ],
            [
                [
                    0 => 364,
                    1 => '=>',
                    2 => 101,
                    3 => 'T_DOUBLE_ARROW',
                ],
            ],
            [
                [
                    0 => 379,
                    1 => ' ',
                    2 => 101,
                    3 => 'T_WHITESPACE',
                ],
            ],
            [
                [
                    0 => 312,
                    1 => '$this',
                    2 => 101,
                    3 => 'T_VARIABLE',
                ],
            ],
            [
                [
                    0 => 363,
                    1 => '->',
                    2 => 101,
                    3 => 'T_OBJECT_OPERATOR',
                ],
            ],
            [
                [
                    0 => 310,
                    1 => 'getTable',
                    2 => 101,
                    3 => 'T_STRING',
                ],
            ],
            [
                '(',
            ],
            [
                [
                    0 => 318,
                    1 => '\'catalogrule/rule_product\'',
                    2 => 101,
                    3 => 'T_CONSTANT_ENCAPSED_STRING',
                ],
            ],
            [
                ')',
            ],
            [
                ')',
            ],
            [
                ')',
            ]
        ];
    }
}
