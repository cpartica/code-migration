<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\ConfigConverter;

use Magento\Migration\Utility\M1\File;

class ConfigFile implements ConfigFileInterface
{
    const FILE_PERMS = 0755;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter
     */
    protected $formatter;

    /**
     * @var \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface
     */
    protected $configType;

    /**
     * @var string
     */
    protected $moduleNamespace;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var string
     */
    protected $xml;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter $formatter,
     * @param \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface $configType
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter $formatter,
        \Magento\Migration\Code\ConfigConverter\ConfigTypeInterface $configType
    ) {
        $this->file = $file;
        $this->formatter = $formatter;
        $this->configType = $configType;
        $this->parseModuleNameSpace();
    }

    /**
     * @return $this
     */
    protected function parseModuleNameSpace()
    {
        if ($this->configType->getFileName()) {
            if (preg_match('/app\/code\/([^\/]+)\/([^\/]+)\/etc/is', $this->configType->getFileName(), $match)) {
                if (count($match) == 3) {
                    $this->moduleNamespace = $match[1];
                    $this->moduleName = $match[2];
                }

            }
        }
        return $this;
    }

    /**
     * @return int|void
     */
    public function createFile()
    {
        if ($this->configType->getFileName()) {
            $this->xml = $this->configType->getXmlContent()->asXML();
            $this->xml = $this->formatFile($this->xml);
            $this->mkpath(dirname($this->configType->getFileName()));
            return $this->file->filePutContents($this->configType->getFileName(), $this->xml);
        }
    }

    /**
     * @param string $xml
     * @return string
     */
    protected function formatFile($xml)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = true;
        $doc->loadXML($xml);

        $stylesheet = new \DOMDocument();
        $stylesheet->preserveWhiteSpace = true;

        foreach ($this->configType->getXsls() as $file) {
            $stylesheet->load(
                __DIR__ . DIRECTORY_SEPARATOR . 'XmlProcessors' . DIRECTORY_SEPARATOR .'_files' .
                DIRECTORY_SEPARATOR . $file
            );
            $xslt = new \XSLTProcessor();
            $xslt->registerPHPFunctions();
            $xslt->importStylesheet($stylesheet);
            $xslt->setParameter('', 'moduleName', $this->moduleNamespace . '_' . $this->moduleName);
            $xslt->setParameter('', 'schema', $this->configType->getXmlSchema());
            $xslt->setParameter('', 'tagName', $this->configType->getTagName());
            $doc->loadXML($xslt->transformToXml($doc));
        }

        return  $this->formatter->format($doc->saveXML());
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function mkpath($path)
    {
        if ($this->file->isExists($path) ? true : $this->file->createDirectory($path, self::FILE_PERMS) ||
            $this->file->isExists($path)
        ) {
            return true;
        }
        return (
            $this->mkpath($this->file->getParentDirectory($path)) &&
            $this->file->isExists($path) ? true : $this->file->createDirectory($path, self::FILE_PERMS)
        );
    }
}
