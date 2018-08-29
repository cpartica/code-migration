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

class GenerateModuleMapping extends Command
{
    /**
     * @var array
     */
    protected $moduleMapping = [
        "Mage_Sendfriend" => "Magento_SendFriend",
        "Mage_Uploader" => [
            "comment" => "obsolete",
        ],  
        "Enterprise_Catalog" => "Magento_AdvancedCatalog",
        "Enterprise_Checkout" => "Magento_AdvancedCheckout",
        "Enterprise_CatalogInventory" => [
            "comment" => "Indexing functionality moved to Magento_CatalogInventory module",
        ],
        "Enterprise_Bundle" => [
            "comment" => "Functionality no longer needed",
        ],
        "Enterprise_Search" => "Magento_AdvancedSearch",
        "Enterprise_CatalogSearch" => [
            "comment" => "Indexing functionality moved to Magento_CatalogSearch module",
        ],
        "Enterprise_Cms" => "Magento_VersionsCms",
        "Enterprise_Customer" => "Magento_CustomerCustomAttributes",
        "Enterprise_Eav" => "Magento_CustomAttributeManagement",
        "Enterprise_ImportExport" => "Magento_ScheduledImportExport",
        "Enterprise_Persistent" => "Magento_PersistentHistory",
        "Enterprise_Wishlist" => "Magento_MultipleWishlist",
        "Mage_Admin" => "Magento_Authorization",
        "Mage_CatalogIndex" => [
            "comment" => "obsolete",
        ],
        "Mage_ConfigurableSwatches" => "Magento_Swatches???",
        "Mage_Index" => "Magento_Indexer",
        "Mage_Contacts" => "Magento_Contact",
        "Mage_Dataflow" => [
            "comment" => "obsolete",
        ],
        "Mage_Log" => "Removed, some functionality moved to Magento_CustomerModule",
        "Mage_Media" => [
            "comment" => "obsolete, merged into Magento_Catalog module",
        ],
        "Mage_Oauth" => "Magento_Integration",
        "Mage_Api" => [
            "comment" => "obsolete",
        ],
        "Mage_Api2" => [
            "comment" => "obsolete",
        ],
        "Mage_Ogone" => [
            "comment" => "obsolete",
        ],
        "Mage_Page" => [
            "comment" => "Removed, functionality moved to framework and Magento_Theme module",
        ],
        "Mage_Paygate" => [
            "comment" => "Consolidated into Magento_Authorizenet module",
        ],
        "Mage_PaypalUk" => [
            "comment" => "Consolidated into Magento_Paypal module",
        ],
        "Mage_Poll" => [
            "comment" => "obsolete",
        ],
        "Mage_Rating" => "Magento_Review",
        "Mage_Tag" => [
            "comment" => "obsolete",
        ],
        "Mage_Usa" => [
            "comment" => "Splitted into modules for individual carriers, e.g., Dhl, Fedex, Ups, Usps.",
        ],
        "Mage_Adminhtml" => [
            "m2module" => "Magento_Backend",
            "comment" => "Many blocks and controller moved to individual modules",
        ],
        "Mage_Centinel" => [
            "comment" => "obsolete",
        ],
        "Mage_Compiler" => [
            "comment" => "obsolete",
        ],
        "Mage_Connect" => [
            "comment" => "obsolete",
        ],
        "Mage_Core" => [
            "comment" => "Functionalities moved to framework and several other modules",
        ],
        "Mage_GoogleBase" => [
            "comment" => "obsolete",
        ],
        "Mage_GoogleCheckout" => [
            "comment" => "obsolete",
        ],
        "Mage_Install" => [
            "comment" => "obsolete",
        ],
        "Mage_XmlConnect" => [
            "comment" => "obsolete",
        ],
        "Enterprise_GoogleAnalyticsUniversal" => "Magento_GoogleTagManager",
        "Enterprise_Index" => [
            "comment" => "Functionality moved to framework and Magento_Indexer",
        ],
        "Enterprise_License" => [
            "comment" => "obsolete",
        ],
        "Enterprise_Mview" => [
            "comment" => "Functionality moved to framework",
        ],
        "Enterprise_Pbridge" => [
            "comment" => "obsolete",
        ],
        "Enterprise_Pci" => "Magento_EncryptionKey",
        "Enterprise_Staging" => [
            "comment" => "obsolete",
        ],
    ];

    /** @var \Magento\Migration\Logger\Logger */
    protected $logger;

    /**
     * @param \Magento\Migration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Migration\Logger\Logger $logger
    ) {
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('generateModuleMapping')
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
     * @throws \Exception
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

        $moduleMapping = [];

        //first, get Mage modules
        $configFiles = glob($m1BaseDir . '/app/code/core/*/*/etc/config.xml');
        foreach ($configFiles as $configFile) {
            $content = file_get_contents($configFile);
            $config = new \Magento\Migration\Utility\M1\Config($content, $this->logger);
            $moduleName = $config->getModuleName();
            $mapping = $this->mapModuleName($moduleName, $m2BaseDir);
            if ($mapping !== null) {
                if (!is_array($mapping)) {
                    $moduleMapping[$moduleName] = [
                        "m2module" => $mapping,
                    ];
                } else {
                    $moduleMapping[$moduleName] = $mapping;
                }
            } else {
                $moduleMapping[$moduleName] = 'obsolete';
            }
        }

        $outputFileName = BP . '/mapping/module_mapping.json';
        if (file_put_contents($outputFileName, json_encode($moduleMapping, JSON_PRETTY_PRINT))) {
            $this->logger->info($outputFileName . " was generated");
        } else {
            $this->logger->error('Could not write '.$outputFileName . '. check writing permissions');
            return 255;
        }
        return 0;
    }

    /**
     * @param string $moduleName
     * @param string $m2BaseDir
     * @return string
     * @throws \Exception
     */
    protected function mapModuleName($moduleName, $m2BaseDir)
    {
        if (isset($this->moduleMapping[$moduleName])) {
            return $this->moduleMapping[$moduleName];
        } else {
            $parts = explode('_', $moduleName);

            $m2ModuleDir = $m2BaseDir . '/app/code/Magento/' . $parts[1];
            if (file_exists($m2ModuleDir)) {
                return 'Magento' . '_' . $parts[1];
            } else {
                throw new \Exception("unmapped module: " . $moduleName);
            }
        }
    }
}
