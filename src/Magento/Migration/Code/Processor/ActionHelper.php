<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

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

    public function __construct(
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\Processor\TokenHelper $tokenHelper
    )
    {
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
     * @return $this
     */
    public function createActions()
    {
        if (!empty($this->tokens) && $this->abstractFileName && $this->abstractNamespace) {
            foreach ($this->tokens as $className => $arrayOfTokens) {
                if (is_array($arrayOfTokens)) {
                    $this->changeClassToExecute($arrayOfTokens);
                    $actionFilePath =  $this->getNewFileName($className);
                    if (!file_exists(dirname($actionFilePath))) {
                        @mkdir(dirname($actionFilePath));
                    }
                    if (@file_put_contents($actionFilePath, $this->wrapClass($className, $arrayOfTokens))) {
                        $this->logger->info('Creating file '.$actionFilePath, []);
                    } else {
                        $this->logger->warn('Error creating '.$actionFilePath, []);
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    private function getParentClass()
    {
        return rtrim(basename($this->abstractFileName), '.php');
    }

    /**
     * @return string
     */
    private function getNewFileName($className)
    {
        return dirname($this->abstractFileName)  . DIRECTORY_SEPARATOR . ucfirst($this->getParentClass()) .
        DIRECTORY_SEPARATOR . $className . ".php.converted";
    }

    /**
     * @param string $className
     * @param string $tokens
     * @return string
     */
    private function wrapClass($className, $tokens)
    {
        return '<?php' . "\n" .
            'namespace ' . $this->abstractNamespace . '\\' . $this->getParentClass() . ";" . "\n\n" .
            'class ' . $className . ' extends \\' . $this->abstractNamespace . '\\' . $this->getParentClass() . "\n" .
            '{' . "\n" .
            $this->tokenHelper->reconstructContent($tokens) .
            '}' . "\n" .
            '?>' . "\n";
    }

    /**
     * @param array $tokens
     * @return $this
     */
    protected function changeClassToExecute(array &$tokens)
    {
        $indexClass = $this->tokenHelper->getNextIndexOfTokenType($tokens, 0, T_FUNCTION);
        $classNameIndex = $this->tokenHelper->getNextTokenIndex($tokens, $indexClass);
        $tokens[$classNameIndex][1] = 'execute';
        return $this;
    }
}
