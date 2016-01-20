<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class MageProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var string $filePath
     */
    protected $filePath;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MatcherInterface
     */
    protected $matcher;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Migration\Code\Processor\DiVariablesPersistent
     */
    protected $diVariablesPersistent;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Migration\Code\Processor\DiVariablesPersistent $diVariablesPersistent
     * @param \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
     * @param Mage\MageFunctionMatcher $matcher
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Migration\Code\Processor\DiVariablesPersistent $diVariablesPersistent,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\Mage\MageFunctionMatcher $matcher
    ) {
        $this->objectManager = $objectManager;
        $this->diVariablesPersistent = $diVariablesPersistent;
        $this->tokenHelper = $tokenHelper;
        $this->matcher = $matcher;
    }

    /**
     * @param string $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function process(array $tokens)
    {
        if (!$this->isClass($tokens)) {
            return $tokens;
        }

        $index = 0;
        $length = count($tokens);

        $diVariables = [];
        while ($index < $length - 3) {
            $matchedFunction = $this->matcher->match($tokens, $index);
            if ($matchedFunction) {
                $matchedFunction->convertToM2();
                if ($matchedFunction->getDiVariableName() !== null) {
                    $diVariables[$matchedFunction->getDiVariableName()] = [
                        'variable_name' => $matchedFunction->getDiVariableName(),
                        'type' => $matchedFunction->getClass(),
                    ];
                }
            }
            $index++;
        }

        //TODO: avoid adding di variable if parent class already has variable of the same type
        if (!empty($diVariables) || $this->applyParentClassDiVariables($tokens)) {
            /** @var \Magento\Migration\Code\Processor\ConstructorHelper $constructorHelper */
            $constructorHelper = $this->objectManager->create(
                '\Magento\Migration\Code\Processor\ConstructorHelper'
            );
            $constructorHelper->setContext($tokens);
            if ($this->applyParentClassDiVariables($tokens)) {
                $diVariables = $this->mergeDiVariables($this->diVariablesPersistent->getDiVariables(), $diVariables);
            }

            $orderArray = $constructorHelper->injectArguments($diVariables);

            if ($this->tokenHelper->isAbstract($tokens)) {
                $this->diVariablesPersistent->setDiVariables($orderArray);
            }
        }

        //reconstruct tokens
        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function isClass(array &$tokens)
    {
        return $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_CLASS) !== null;
    }

    /**
     * @param array $diVariables
     * @param array $newDiVariables
     * @return bool
     */
    protected function mergeDiVariables($diVariables, $newDiVariables)
    {
        foreach ($newDiVariables as $key => $diVar) {
            if (!array_key_exists($key, $diVariables)) {
                $diVariables[$key] = $diVar;
            }
        }
        return $diVariables;
    }

    /**
     * @param array $tokens
     * @return bool
     */
    protected function applyParentClassDiVariables($tokens)
    {
        //TODO: add more cases like any derived class should have this
        if (($this->tokenHelper->isController($tokens) && !$this->tokenHelper->isAbstract($tokens))) {
            return true;
        }
        return false;
    }
}
