<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class ClassNameValidator
{
    /**
     * @var \Magento\Migration\Mapping\Alias
     */
    protected $aliasMapper;

    /**
     * @var array
     */
    protected $nativeClassPrefixes = [];

    /**
     * @var array|null
     */
    protected $knownClassPrefixes = null;

    /**
     * @param \Magento\Migration\Mapping\Alias $aliasMapper
     * @param array $nativeClassPrefixes
     */
    public function __construct(
        \Magento\Migration\Mapping\Alias $aliasMapper,
        array $nativeClassPrefixes = []
    ) {
        $this->aliasMapper = $aliasMapper;
        $this->nativeClassPrefixes = $nativeClassPrefixes;
    }

    /**
     * @param string $m1ClassName
     * @return bool
     */
    public function isNativeClass($m1ClassName)
    {
        return $this->hasClassPrefix($m1ClassName, $this->nativeClassPrefixes);
    }

    /**
     * @param string $m1ClassName
     * @return bool
     */
    public function isKnownClass($m1ClassName)
    {
        return $this->hasClassPrefix($m1ClassName, $this->getKnownClassPrefixes());
    }

    /**
     * @param string $m1ClassName
     * @param array $prefixes
     * @return bool
     */
    protected function hasClassPrefix($m1ClassName, array $prefixes)
    {
        $prefixesEscaped = array_map('preg_quote', $prefixes);
        $pattern = '/^(?:' . implode('|', $prefixesEscaped) . ')_/';
        return (bool)preg_match($pattern, $m1ClassName);
    }

    /**
     * @return array
     */
    protected function getKnownClassPrefixes()
    {
        if ($this->knownClassPrefixes === null) {
            $this->knownClassPrefixes = [];
            foreach ($this->aliasMapper->getAllMapping() as $map) {
                $this->knownClassPrefixes = array_merge($this->knownClassPrefixes, array_values($map));
            }
        }
        return $this->knownClassPrefixes;
    }
}
