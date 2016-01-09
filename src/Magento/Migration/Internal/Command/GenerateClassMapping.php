<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Internal\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateClassMapping extends Command
{
    /** @var \Magento\Migration\Utility\ClassMapGenerator */
    protected $classMapGenerator;

    /** @var \Magento\Migration\Logger\Logger */
    protected $logger;

    /**
     * @param \Magento\Migration\Utility\ClassMapGenerator $classMapGenerator
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Migration\Utility\ClassMapGenerator $classMapGenerator,
        \Magento\Migration\Logger\Logger $logger
    ) {
        parent::__construct();
        $this->classMapGenerator = $classMapGenerator;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('generateClassMapping')
            ->setDescription('Generate class mapping from M1 to M2')
            ->addArgument(
                'm1',
                InputArgument::REQUIRED,
                'Base directory of M1'
            )->addArgument(
                'm2',
                InputArgument::REQUIRED,
                'Base directory of M2'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $m1BaseDir = $input->getArgument('m1');
        $m2BaseDir = $input->getArgument('m2');

        if (!is_dir($m1BaseDir)) {
            $this->logger->error('m1 path doesn\'t exist or not a directory');
            return 255;
        }

        if (!is_dir($m2BaseDir)) {
            $this->logger->error('m2 path doesn\'t exist or not a directory');
            return 255;
        }

        $classMapping = [];

        $fileUtils = new \Magento\Migration\Utility\M1\File($m1BaseDir);
        $classFiles = $fileUtils->getClasses();

        $m2FileUtils = new \Magento\Migration\Utility\M2\File($m2BaseDir);

        $unmapped = [];
        foreach (array_keys($classFiles) as $className) {
            $m2ClassName = $this->classMapGenerator->mapM1Class($className);
            if ($m2FileUtils->isM2Class($m2ClassName)) {
                $classMapping[$className] = [
                    'm2class' => $m2ClassName,
                ];
            } else {
                $unmapped[] = $className;
            }
        }

        $outputFileName = BP . '/mapping/class_mapping.json';
        if (file_put_contents($outputFileName, json_encode($classMapping, JSON_PRETTY_PRINT))) {
            $this->logger->info($outputFileName . ' was generated');
        } else {
            $this->logger->error('Could not write '.$outputFileName . '. check writing permissions');
        }

        $unmappedPath = BP . '/mapping/unmapped_classes.json';
        if (file_put_contents($unmappedPath, json_encode($unmapped, JSON_PRETTY_PRINT))) {
            $this->logger->info($unmappedPath . ' was generated');
        } else {
            $this->logger->error('Could not write '.$unmappedPath . '. check writing permissions');
            return 255;
        }
        return 0;
    }
}
