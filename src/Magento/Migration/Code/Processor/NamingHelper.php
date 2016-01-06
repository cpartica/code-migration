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
     * @var \Magento\Migration\Code\Processor\ClassNameValidator
     */
    protected $classNameValidator;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Migration\Mapping\ClassMapping $classMapper
     * @param \Magento\Migration\Mapping\Alias $aliasMapper
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\Processor\ClassNameValidator $classNameValidator
     */
    public function __construct(
        \Magento\Migration\Mapping\ClassMapping $classMapper,
        \Magento\Migration\Mapping\Alias $aliasMapper,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\ClassNameValidator $classNameValidator
    ) {
        $this->classMapper = $classMapper;
        $this->aliasMapper = $aliasMapper;
        $this->logger = $logger;
        $this->classNameValidator = $classNameValidator;
    }

    /**
     * @param string $m1ClassAlias
     * @param string $type
     * @return null|string
     */
    public function getM1ClassName($m1ClassAlias, $type)
    {
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
     * @param string $m1ClassName
     * @return null|string
     */
    public function getM2ClassName($m1ClassName)
    {
        if (!$m1ClassName) {
            return null;
        }
        $m2ClassName = $this->classMapper->mapM1Class($m1ClassName);
        if ($m2ClassName == 'obsolete') {
            $this->logger->warn(sprintf('Class "%s" is obsolete', $m1ClassName));
            return null;
        }
        if (!$m2ClassName) {
            if (!$this->classNameValidator->isNativeClass($m1ClassName)
                && $this->classNameValidator->isKnownClass($m1ClassName)
            ) {
                $m2ClassName = $this->buildNamespaceClassName($this->fixControllerClassName($m1ClassName));
            } else if (class_exists($m1ClassName)) {
                $m2ClassName = '\\' . $m1ClassName;
            }
        }
        return $m2ClassName;
    }

    /**
     * @param string $m1ClassName
     * @return string|null
     */
    public function getM2FactoryClassName($m1ClassName)
    {
        $result = $this->getM2ClassName($m1ClassName);
        if ($result) {
            $result .= 'Factory';
        }
        return $result;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function fixControllerClassName($className)
    {
        $result = preg_replace(
            '/^(?P<prefix>[\\\\]?[^\\\\_]+(?P<separator>[\\\\_])[^\\\\_]+[\\\\_])(?P<suffix>.+)Controller$/',
            '\\1Controller\\2\\3',
            $className
        );
        return $result;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function buildNamespaceClassName($className)
    {
        return '\\' . str_replace('_', '\\', ltrim($className, '\\'));
    }

    /**
     * @param string $className
     * @return string
     */
    public function generateVariableName($className)
    {
        $parts = explode('\\', trim($className, '\\'));
        $partsCount = count($parts);
        list($vendor, $module) = $parts;
        if ($partsCount > 2) {
            $parts[0] = '';
            if ($vendor == 'Magento' && $module == 'Framework') {
                $parts[1] = '';
            }
            if ($partsCount > 3) {
                $parts[2] = '';
            }
        }
        $result = lcfirst(str_replace(' ', '', ucwords(implode(' ', $parts))));
        return $result;
    }
}
