<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class NamingHelper
{
    /**
     * @var \Magento\Migration\Mapping\ClassMapping
     */
    protected $classMapper;

    /**
     * @var \Magento\Migration\Mapping\Alias
     */
    protected $aliasMapper;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Migration\Mapping\ClassMapping $classMapper
     * @param \Magento\Migration\Mapping\Alias $aliasMapper
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMapper,
        \Magento\Migration\Mapping\Alias $aliasMapper,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->classMapper = $classMapper;
        $this->aliasMapper = $aliasMapper;
        $this->logger = $logger;
    }

    /**
     * @param string $m1ClassAlias
     * @param string $type
     * @return null|string
     */
    public function getM1ClassName($m1ClassAlias, $type)
    {
        $m1ClassAlias = trim($m1ClassAlias, '\'"');
        if (strpos($m1ClassAlias, '/') === false) {
            $result = $m1ClassAlias;
        } else {
            list($m1ModuleAlias, $m1ClassSuffix) = explode('/', $m1ClassAlias, 2);
            $m1ClassPrefix = $this->aliasMapper->mapAlias($m1ModuleAlias, $type);
            if (!$m1ClassPrefix) {
                $this->logger->warn(sprintf('Class not found for alias "%s"', $m1ModuleAlias));
                return null;
            }
            $m1ClassSuffix = ucwords($m1ClassSuffix, '_');
            $result = $m1ClassPrefix . '_' . $m1ClassSuffix;
        }
        return $result;
    }

    /**
     * @param string $m1ClassAlias
     * @param string $type
     * @return null|string
     */
    public function getM2ClassName($m1ClassAlias, $type)
    {
        $m1ClassName = $this->getM1ClassName($m1ClassAlias, $type);
        $m2ClassName = $this->classMapper->mapM1Class($m1ClassName);
        if (!$m2ClassName) {
            $m2ClassName = '\\' . str_replace('_', '\\', $m1ClassName);
        } else if ($m2ClassName == 'obsolete') {
            $this->logger->warn(sprintf('Class "%s" is obsolete', $m1ClassName));
            return null;
        }
        return $m2ClassName;
    }

    /**
     * @param string $m1ClassAlias
     * @param string $type
     * @return null|string
     */
    public function getM2FactoryClassName($m1ClassAlias, $type)
    {
        $result = $this->getM2ClassName($m1ClassAlias, $type);
        if ($result) {
            $result .= 'Factory';
        }
        return $result;
    }

    /**
     * @param string $className
     * @return string
     */
    public function generateVariableName($className)
    {
        $parts = explode('\\', trim($className, '\\'));
        $parts[0] = '';
        $parts[2] = '';
        $result = lcfirst(str_replace(' ', '', ucwords(implode(' ', $parts))));
        return $result;
    }
}
