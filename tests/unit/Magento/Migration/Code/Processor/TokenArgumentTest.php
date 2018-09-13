<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

use Magento\Migration\Code\Processor\TokenArgument;

/**
 * Class TokenArgumentTest
 */
class TokenArgumentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\TokenArgument
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
            '\Magento\Migration\Code\Processor\TokenArgument',
            [
            ]
        );
    }

    /**
     * test SetToken
     * tests GetToken, setToken,
     * @param mixed[]|string $token
     * @dataProvider setTokenProvider
     */
    public function testSetToken($token)
    {
        $this->model->setToken($token);
        $this->assertEquals($token, $this->model->getToken());

        if (is_array($token)) {
            $this->assertEquals($token[1], $this->model->getName());
            $this->assertEquals($token[0], $this->model->getType());
            $this->assertEquals($token[2], $this->model->getLine());
        } else {
            $this->assertEquals($token, $this->model->getName());
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setTokenProvider()
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
