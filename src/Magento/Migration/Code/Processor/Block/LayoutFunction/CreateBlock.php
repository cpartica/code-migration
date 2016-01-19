<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Block\LayoutFunction;

use Magento\Migration\Code\Processor\Block\AbstractFunction;
use Magento\Migration\Mapping\Alias;

class CreateBlock extends AbstractFunction
{
    /**
     * @inheritdoc
     */
    public function convertToM2()
    {
        $arguments = $this->tokenHelper->getCallArguments($this->tokens, $this->index);

        if ($arguments->getFirstArgument()) {
            if ($arguments->getFirstArgument()->getFirstToken()->getType() != T_VARIABLE) {
                $classAlias = trim($arguments->getFirstArgument()->getString(), '\'"');
                $className = $this->getBlockClass($classAlias);
                if ($className) {
                    $argumentsNew = "'" . $className . "'";
                    for ($i = 2; $i <= $arguments->getCount(); $i++) {
                        $argumentsNew .= ', ' . $arguments->getArgument($i)->getString();
                    }
                    $this->tokenHelper->replaceCallArgumentsTokens($this->tokens, $this->index, $argumentsNew);
                }
            } else {
                $this->logger->warn(sprintf(
                    'Variable inside a Mage_Core_Model_Layout::createBlock call not converted at %s',
                    $arguments->getFirstArgument()->getFirstToken()->getLine()
                ));
            }
        }

        return $this;
    }

    /**
     * @param string $m1ClassAlias
     * @return null|string
     */
    protected function getBlockClass($m1ClassAlias)
    {
        $m1ClassName = $this->namingHelper->getM1ClassName($m1ClassAlias, Alias::TYPE_BLOCK);
        $m2ClassName = $this->namingHelper->getM2ClassName($m1ClassName);
        return $m2ClassName;
    }
}
