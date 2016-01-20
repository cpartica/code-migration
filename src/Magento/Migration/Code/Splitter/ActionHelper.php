<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Splitter;

use Magento\Migration\Command\ConvertPhpCode;

class ActionHelper
{
    /**
     * @var array
     */
    protected $tokens;

    /**
     * @var \Magento\Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /** @var string */
    protected $abstractNamespace;

    /** @var string */
    protected $abstractFileName;

    /** @var  string */
    protected $parentClass;

    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    ) {
        $this->tokenHelper = $tokenHelper;
        $this->logger = $logger;
    }

    /**
     * @param array $arrayOfTokens
     * @return $this
     */
    public function setContext($arrayOfTokens)
    {
        $this->tokens = $arrayOfTokens;
        return $this;
    }

    /**
     * @param string $abstractFileName
     * @return $this
     */
    public function setAbstractFileName($abstractFileName)
    {
        $this->abstractFileName = $abstractFileName;
        return $this;
    }

    /**
     * @param string $abstractNamespace
     * @return $this
     */
    public function setAbstractNamespace($abstractNamespace)
    {
        $this->abstractNamespace = $abstractNamespace;
        return $this;
    }

    /**
     * @param array $resultFiles
     * @return string[]
     */
    public function createActions(array &$resultFiles)
    {
        if (!empty($this->tokens) && $this->abstractFileName && $this->abstractNamespace) {
            foreach ($this->tokens as $className => $arrayOfTokens) {
                if (is_array($arrayOfTokens)) {
                    $this->changeMethodNameToExecute($arrayOfTokens);
                    $actionFilePath =  $this->getNewFileName($className);
                    if (!file_exists(dirname($actionFilePath))) {
                        @mkdir(dirname($actionFilePath));
                    }
                    if (@file_put_contents($actionFilePath, $this->wrapClass($className, $arrayOfTokens))) {
                        $this->logger->info('Creating file '.$actionFilePath, []);
                        $resultFiles[] = $actionFilePath;
                    } else {
                        $this->logger->warn('Error creating '.$actionFilePath, []);
                    }
                }
            }
        }
    }

    /**
     * @param string $parentClass
     * @return $this
     */
    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * @param string $className
     * @return string
     */
    private function getNewFileName($className)
    {
        return dirname($this->abstractFileName) .
        DIRECTORY_SEPARATOR . ucfirst(preg_replace('/^.+_([a-zA-Z0-9]+)$/', '$1', $this->abstractNamespace)) .
        DIRECTORY_SEPARATOR . ($className == 'New' ? 'NewAction' :  $className) . ".php" .
        ConvertPhpCode::CONVERTED_FILE_EXT;
    }

    /**
     * @param string $className
     * @param string $tokens
     * @return string
     */
    private function wrapClass($className, $tokens)
    {
        return '<?php' . "\n" .
        'class ' . $this->abstractNamespace . '_' . ($className == 'New' ? 'NewAction' :  $className) .
        'Controller extends ' . $this->getParentClass() . "\n" .
        '{' . "\n" .
        $this->tokenHelper->reconstructContent($tokens) .
        '}' . "\n";
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function changeMethodNameToExecute(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_FUNCTION);
        $methodNameIndex = $this->tokenHelper->getNextTokenIndex($tokens, $indexClass);
        $tokens[$methodNameIndex][1] = 'execute';
        return $this;
    }
}
