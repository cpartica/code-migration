<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Logger;

use Magento\Framework\Filesystem\Driver\File;

/**
 * Processing logger handler creation for migration application
 */
class FileHandler extends \Monolog\Handler\AbstractHandler implements \Monolog\Handler\HandlerInterface
{
    const LOGFILE = "migration.log";
    /**
     * @var File
     */
    protected $filesystem;

    /**
     * Permissions for new sub-directories
     *
     * @var int
     */
    protected $permissions = 0755;

    /**
     * @param File $filesystem
     */
    public function __construct(File $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $logFile = $this::LOGFILE;
        $record['formatted'] = $this->getFormatter()->format($record);
        if ($logFile) {
            $filePath = $this->getFilePath($logFile);
            $this->filesystem->filePutContents($filePath, $record['formatted'] . PHP_EOL, FILE_APPEND);
        }
        return false === $this->bubble;
    }

    /**
     * @param string $logFile
     * @return string
     */
    protected function getFilePath($logFile)
    {
        $logFileDir = BP . '/var';
        if (!$this->filesystem->getRealPath($logFileDir)) {
            if (!$this->filesystem->isExists($logFileDir)) {
                $this->filesystem->createDirectory($logFileDir, $this->permissions);
            }
        }
        return $logFileDir . '/' . $logFile;
    }
}
