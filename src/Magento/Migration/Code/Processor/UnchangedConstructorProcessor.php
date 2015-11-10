<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

/**
 * Class ClassProcessor
 * @package Magento\Migration\Code\Processor
 *
 * This class processes name spaces and class inheritance
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UnchangedConstructorProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\ConstructorHelperFactory
     */
    protected $constructorHelperFactory;

    /**
     * @param \Magento\Migration\Mapping\ClassMapping $classMap
     * @param \Magento\Migration\Mapping\Alias $aliasMap
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Mapping\Context $context
     * @param Mage\MageFunction\ConstructorFactory $constructorHelperFactory
     * @param TokenHelper $tokenHelper
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\ConstructorHelperFactory $constructorHelperFactory,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->logger = $logger;
        $this->constructorHelperFactory = $constructorHelperFactory;
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * Take an array of tokens as input, return modified array of tokens
     *
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens)
    {
        /** @var \Magento\Migration\Code\Processor\ConstructorHelper $contructorHelper */
        $contructorHelper = $this->constructorHelperFactory->create();
        $contructorHelper->setContext($tokens);

        $x=$contructorHelper->getConstructorIndex();
        /** @var \Magento\Migration\Code\Processor\CallArgumentCollection $argCollection */
        $argCollection = $this->tokenHelper->getCallArguments($tokens, $x);
        $parentClass = $this->tokenHelper->getExtendsClass($tokens);
        if ($parentClass) {
            $reflectionClass = new \ReflectionClass($parentClass);
            $parentConstructor = $reflectionClass->getConstructor();
            if ($parentConstructor == null) {
                //parent doesn't have any constructor
            }
        }
        $x=1;
    }

}
