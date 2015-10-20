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

class GenerateClassDependency extends Command
{
    /**
     * @var \Magento\Migration\Utility\ClassDependencyScanner
     */
    protected $classDependencyScanner;

    /**
     * @var \Magento\Migration\Mapping\ClassMapping
     */
    protected $classMapper;

    /** @var \Magento\Migration\Logger\Logger */
    protected $logger;

    /**
     * @param \Magento\Migration\Utility\ClassDependencyScanner\Proxy $classDependencyScanner
     * @param \Magento\Migration\Mapping\ClassMapping\Proxy $classMapper
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Migration\Utility\ClassDependencyScanner\Proxy $classDependencyScanner,
        \Magento\Migration\Mapping\ClassMapping\Proxy $classMapper,
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->classDependencyScanner = $classDependencyScanner;
        $this->classMapper = $classMapper;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('generateClassDependency')
            ->setDescription('Generate class dependency')
            ->addArgument(
                'm1',
                InputArgument::REQUIRED,
                'Base directory of M1'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $m1BaseDir = $input->getArgument('m1');

        if (!is_dir($m1BaseDir)) {
            $this->logger->error('m1 path doesn\'t exist or not a directory');
            return 255;
        }

        $fileUtils = new \Magento\Migration\Utility\M1\File($m1BaseDir);
        $classFiles = $fileUtils->getClasses();

        $dependencies = [];
        foreach ($classFiles as $className => $path) {
            $this->logger->info('Processing file ' . $path);
            $dependencies[$className] = $this->classDependencyScanner->getClassReferenceByFile($path);
            $this->logger->info('Finished processing file ' . $path);
        }

        $outputFileName = BP . '/mapping/class_dependency.json';
        file_put_contents($outputFileName, json_encode($dependencies, JSON_PRETTY_PRINT));

        $aggregate = [];
        foreach ($dependencies as $scopeClass => $dependenciesByClass) {
            $scopeModule = $this->getClassModule($scopeClass);
            if ($scopeModule == 'Mage_XmlConnect') { //ignore the dependencies by XmlConnect module
                continue;
            }
            foreach (array_keys($dependenciesByClass) as $className) {
                if (!$this->isMageClass($className)) {
                    continue;
                }
                if (strpos($className, $scopeModule) !== false) { //ignore dependency within the same module
                    continue;
                }
                if (!isset($aggregate[$className])) {
                    $mappedClass = $this->classMapper->mapM1Class($className);
                    if ($mappedClass && $mappedClass != "obsolete") {
                        $aggregate[$className] = $mappedClass;
                    } else {
                        $aggregate[$className] = "unmapped";
                    }
                }
            }
        }

        $aggregateClassMap = BP . '/mapping/class_dependency_aggregated.json';
        if (file_put_contents($aggregateClassMap, json_encode($aggregate, JSON_PRETTY_PRINT))) {
            $this->logger->info($aggregateClassMap . ' was generated');
        } else {
            $this->logger->error('Could not write '.$aggregateClassMap . '. check writing permissions');
        }
        return 0;
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function isMageClass($className)
    {
        if (strpos($className, "Mage_") !== false
            || strpos($className, "Enterprise_") !== false
            || strpos($className, "Varien_") !== false) {
            return true;
        }
        return false;
    }

    /**
     * @param string $className
     * @return string
     * @throws \Exception
     */
    protected function getClassModule($className)
    {
        $parts = explode("_", $className);
        if (count($parts) < 2) {
            throw new \Exception("unexpected class name: " . $className);
        }
        return $parts[0] . "_" . $parts[1];
    }
}
