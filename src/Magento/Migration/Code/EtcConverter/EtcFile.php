<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\EtcConverter;

use Magento\Migration\Utility\M1\File;

class EtcFile
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
     * @var \Magento\Migration\Code\EtcConverter\EtcTypeInterface
     */
    protected $etcType;

    /**
     * @var string
     */
    protected $moduleNamespace;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter $formatter,
     * @param \Magento\Migration\Code\EtcConverter\EtcTypeInterface $etcType
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter $formatter,
        \Magento\Migration\Code\EtcConverter\EtcTypeInterface $etcType
    ) {
        $this->file = $file;
        $this->formatter = $formatter;
        $this->etcType = $etcType;
        $this->parseModuleNameSpace();
    }

    /**
     * @return $this
     */
    protected function parseModuleNameSpace()
    {
        if ($this->etcType->getFileName()) {
            if (preg_match('/app\/code\/([^\/]+)\/([^\/]+)\/etc/is', $this->etcType->getFileName(), $match)) {
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
    public function createFileHandler()
    {
        if ($this->etcType->getFileName()) {
            $this->xml = "<?xml version=\"1.0\"?>" . $this->etcType->getXmlContent()->asXML();
            $this->xml = $this->formatFileHandler($this->xml);
            $this->mkpath(dirname($this->etcType->getFileName()));
            return $this->file->filePutContents($this->etcType->getFileName(), $this->xml);
        }
    }

    /**
     * @param string $xml
     * @return string
     */
    protected function formatFileHandler($xml)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = true;
        $doc->loadXML($xml);

        $stylesheet = new \DOMDocument();
        $stylesheet->preserveWhiteSpace = true;

        $files = glob(__DIR__ . '/XmlProcessors/_files/*.xsl');

        if ($files) {
            foreach ($files as $file) {
                $stylesheet->load($file);
                $xslt = new \XSLTProcessor();
                $xslt->registerPHPFunctions();
                $xslt->importStylesheet($stylesheet);
                $xslt->setParameter('', 'moduleName', $this->moduleNamespace . '_' . $this->moduleName);
                $doc->loadXML($xslt->transformToXml($doc));
            }
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
