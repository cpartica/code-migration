<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage;

use Magento\Migration\Mapping\Alias;

class MageFunctionMatcher implements MatcherInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return MageFunctionInterface|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function match(&$tokens, $index)
    {
        if (!$this->isMageCall($tokens, $index)) {
            return null;
        }

        $methodName = $tokens[$index + 2][1];

        switch ($methodName) {
            case 'helper':
            case 'getHelper':
                /** @var \Magento\Migration\Code\Processor\Mage\MageFunction\Helper $helperFunction */
                $helperFunction = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\Helper'
                );
                $helperFunction->setContext($tokens, $index);
                return $helperFunction;
            case 'app':
                $appConverter = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\App'
                );
                $appConverter->setContext($tokens, $index);
                return $appConverter;
            case 'getStoreConfig':
                $getStoreConfigCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\GetStoreConfig'
                );
                $getStoreConfigCall->setContext($tokens, $index);
                return $getStoreConfigCall;
            case 'getStoreConfigFlag':
                $getStoreConfigCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\GetStoreConfigFlag'
                );
                $getStoreConfigCall->setContext($tokens, $index);
                return $getStoreConfigCall;
            case 'getBlockSingleton':
            case 'getResourceSingleton':
            case 'getSingleton':
                $classAliasTypeMap = [
                    'getBlockSingleton'     => Alias::TYPE_BLOCK,
                    'getResourceSingleton'  => Alias::TYPE_RESOURCE_MODEL,
                    'getSingleton'          => Alias::TYPE_MODEL,
                ];
                $classAliasType = $classAliasTypeMap[$methodName];
                $getSingletonCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\GetSingleton',
                    ['classAliasType' => $classAliasType]
                );
                $getSingletonCall->setContext($tokens, $index);
                return $getSingletonCall;
            case 'getModel':
                $getModelCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\GetModel'
                );
                $getModelCall->setContext($tokens, $index);
                return $getModelCall;
            case 'getResourceModel':
                $getResourceModelCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\GetResourceModel'
                );
                $getResourceModelCall->setContext($tokens, $index);
                return $getResourceModelCall;
            case 'dispatchEvent':
                $dispatchEventCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\DispatchEvent'
                );
                $dispatchEventCall->setContext($tokens, $index);
                return $dispatchEventCall;
            case 'register':
            case 'unregister':
            case 'registry':
                $registryCall = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\Registry'
                );
                $registryCall->setContext($tokens, $index);
                return $registryCall;
            case 'throwException':
                $throwException = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\ThrowException'
                );
                $throwException->setContext($tokens, $index);
                return $throwException;
            case 'log':
                $log = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\Log'
                );
                $log->setContext($tokens, $index);
                return $log;
            case 'logException':
                $logException = $this->objectManager->create(
                    '\Magento\Migration\Code\Processor\Mage\MageFunction\LogException'
                );
                $logException->setContext($tokens, $index);
                return $logException;
            default:
                return null;

        }
    }

    /**
     * @param array $tokens
     * @param int $index
     * @return bool
     */
    protected function isMageCall(&$tokens, $index)
    {
        if (!is_array($tokens[$index]) || $tokens[$index][0] != T_STRING || $tokens[$index][1] != 'Mage') {
            return false;
        }

        if (!isset($tokens[$index + 1]) || !isset($tokens[$index + 1])) {
            return false;
        }

        return is_array($tokens[$index + 1]) && $tokens[$index + 1][0] == T_DOUBLE_COLON
            && is_array($tokens[$index + 2]) && $tokens[$index + 2][0] == T_STRING;
    }

    /**
     * @param array $tokens
     * @param int $index
     * @param string $methodName
     * @return bool
     */
    protected function isMageGetGenericCall(&$tokens, $index, $methodName)
    {
        if (!$this->isMageCall($tokens, $index)) {
            return false;
        }

        if (!isset($tokens[$index + 2])) {
            return false;
        }
        return is_array($tokens[$index + 2])
        && $tokens[$index + 2][0] == T_STRING
        && $tokens[$index + 2][1] == $methodName;
    }
}
