<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class DocBlockProcessor implements \Magento\Migration\Code\ProcessorInterface
{
    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\NamingHelper
     */
    protected $namingHelper;

    /**
     * @var string $filePath
     */
    protected $filePath;

    /**
     * @param TokenHelper $tokenHelper
     * @param NamingHelper $namingHelper
     */
    public function __construct(
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper,
        \Magento\Migration\Code\Processor\NamingHelper $namingHelper
    ) {
        $this->tokenHelper = $tokenHelper;
        $this->namingHelper = $namingHelper;
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
        foreach ($this->findDocBlockTokens($tokens) as $tokenIndex => $comment) {
            $tokens[$tokenIndex][1] = $this->replaceTypeAnnotations($comment);
        }
        $tokens = $this->tokenHelper->refresh($tokens);
        return $tokens;
    }

    /**
     * Substiture ocurrences of types in a given DocBlock comment
     *
     * @param string $comment
     * @return string
     */
    protected function replaceTypeAnnotations($comment)
    {
        //Ordinary type declaration or its array derivative, i.e., 'type[]'
        $singleTypePattern = '[[:alnum:]_\\\\]+(?:\s*\[\s*\])?';

        //Pipe-separated type declarations, i.e., 'array|type[]'
        $multiTypePattern = $singleTypePattern . '(?:\s*\|\s*' . $singleTypePattern . ')*';

        $callback = function (array $matches) {
            return $this->convertTypeAnnotation($matches[0]);
        };

        return preg_replace_callback(
            '/(?<=@var|@param|@return|@returns|@throw|@throws)\s+' . $multiTypePattern . '/',
            $callback,
            $comment
        );
    }

    /**
     * @param string $alternateTypes
     * @return string
     */
    protected function convertTypeAnnotation($alternateTypes)
    {
        $callback = function (array $matches) {
            return $this->convertType($matches[0]);
        };
        return preg_replace_callback('/[^|\s\[\]]+/', $callback, $alternateTypes);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function convertType($type)
    {
        if (!$this->isPrimitiveType($type)) {
            $m2ClassName = $this->namingHelper->getM2ClassName($type);
            if ($m2ClassName) {
                return $m2ClassName;
            }
        }
        return $type;
    }

    /**
     * Whether a given type is a primitive DocBlock type or pseudo-type
     *
     * @param string $type
     * @return bool
     */
    protected function isPrimitiveType($type)
    {
        $types = [
            'string',
            'bool', 'boolean', 'true', 'false',
            'int', 'integer', 'float', 'double', 'number',
            'array', 'object',
            'callback', 'callable',
            'null',
            'mixed',
            'void',
        ];
        return in_array(strtolower($type), $types);
    }

    /**
     * @param array $tokens
     * @return array
     */
    protected function findDocBlockTokens(array &$tokens)
    {
        $result = [];
        $index = -1;
        while ($index = $this->tokenHelper->getNextIndexOfTokenType($tokens, $index + 1, T_DOC_COMMENT)) {
            $value = $tokens[$index][1];
            $result[$index] = $value;
        }
        return $result;
    }
}
