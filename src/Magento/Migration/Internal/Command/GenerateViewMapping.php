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

class GenerateViewMapping extends Command
{
    const REFERENCES_FILE = 'references.xml';

    /** @var bool */
    private $handlerFound;

    /** @var string */
    private $layoutResult;

    /** @var string */
    protected $m2LayoutHandles;

    /** @var array[] */
    protected $areas = [
        'adminhtml' => ['default', 'enterprise'],
        'frontend' => ['base', 'default', 'enterprise'],
    ];

    /**
     * @var array
     */
    protected $moduleMapping = [];

    /** @var \Magento\Framework\Simplexml\ConfigFactory */
    protected $configFactory;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    protected $file;

    /** @var \Magento\Migration\Logger\Logger */
    protected $logger;

    /**
     * @var \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter
     */
    protected $formatter;

    /**
     * @var \SimpleXMLElement
     */
    protected $referenceList;

    /**
     * @var string
     */
    protected $referencesFile;

    /**
     * @var array
     */
    protected $referencePattern = [
        'reference' => '//reference[@name]',
        'block' => '//block[@name and @type!=\'core/text_list\' and @type!=\'page/html_wrapper\']',
        'container' => '//block[@name and (@type=\'core/text_list\' or @type=\'page/html_wrapper\')]',
    ];

    /**
     * @param \Magento\Framework\Simplexml\ConfigFactory $configFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Migration\Logger\Logger $logger
     * @param \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter $formatter
     * @throws \LogicException When the command name is empty
     */
    public function __construct(
        \Magento\Framework\Simplexml\ConfigFactory $configFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Migration\Logger\Logger $logger,
        \Magento\Migration\Code\LayoutConverter\XmlProcessors\Formatter $formatter
    ) {
        $this->configFactory = $configFactory;
        $this->file = $file;
        $this->logger = $logger;
        $this->formatter = $formatter;

        $this->referencesFile = BP . '/mapping/' . self::REFERENCES_FILE;

        $contents = '<list/>';

        $this->referenceList = new \SimpleXMLElement($contents);
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('generateViewMapping')
            ->setDescription('Generate view mapping from M1 to M2')
            ->addArgument(
                'm1',
                InputArgument::REQUIRED,
                'Base directory of M1'
            )
            ->addArgument(
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

        //we only support base, default and enterprise
        foreach (array_keys($this->areas) as $area) {
            //get area layout files
            $m1ConfigFiles = $this->searchM1LayoutFiles($area, $m1BaseDir);
            $this->buildM1LayoutStringFile($area, $m2BaseDir);


            $this->writeLayoutHandlers($m1ConfigFiles, $area);
            $this->writeReferences($m1ConfigFiles);
        }

    }

    /**
     * @param string[] $m1ConfigFiles
     * @param string $area
     * @return int
     */
    protected function writeLayoutHandlers($m1ConfigFiles, $area)
    {
        $tableNamesMapping = [];

        foreach ($m1ConfigFiles as $configFile) {
            $content = $this->file->fileGetContents($configFile);
            $layoutM1 = new \Magento\Migration\Utility\M1\Layout($content);
            $mappingM1 = $this->mapView($layoutM1);

            //search m2 for layout handlers
            foreach ($mappingM1 as $layoutHandler) {
                $this->handlerFound = false;
                $this->layoutResult = $layoutHandler;

                $this->isInM2Layout($layoutHandler);

                //match some plurals
                $layoutHandlerReplacement = $this->regexPlural($layoutHandler);
                $this->isInM2Layout($layoutHandlerReplacement);

                //some adminhtml prefixes were removed
                if ($area == 'adminhtml') {
                    $layoutHandlerReplacement = $this->replaceAdminhtmlPrefix($layoutHandler);
                    $this->matchProductType($layoutHandlerReplacement);
                    $this->matchInvitations($layoutHandlerReplacement);
                    $this->matchWordinBetween($layoutHandlerReplacement);
                    $this->matchOneWordAsPrefix($layoutHandlerReplacement);
                    $this->matchTwoWordsAsPrefix($layoutHandlerReplacement);
                } else {
                    $this->matchProductType($layoutHandler);
                    $this->processEnterprisePrefix($layoutHandler);
                    $this->processPagePrefix($layoutHandler);
                    $this->switchFirstTwoWords($layoutHandler);
                }

                $mappingM1[$layoutHandler] =
                    $this->handlerFound ? $this->layoutResult : 'obsolete';
            }
            $tableNamesMapping = array_merge($mappingM1, $tableNamesMapping);
        }

        $outputFileName = BP . '/mapping/view_mapping_' . $area . '.json';
        if (file_put_contents($outputFileName, strtolower(json_encode($tableNamesMapping, JSON_PRETTY_PRINT)))) {
            $this->logger->info($outputFileName . ' was generated');
        } else {
            $this->logger->error('Could not write ' . $outputFileName . '. check writing permissions');
            return 255;
        }
        return 0;
    }

    /**
     * @param \Magento\Migration\Utility\M1\Layout $layout
     * @return array
     */
    protected function mapView($layout)
    {
        return $layout->getLayoutHandlers();
    }

    /**
     * @param string $area
     * @param string $m1BaseDir
     * @return string[]
     */
    protected function searchM1LayoutFiles($area, $m1BaseDir)
    {
        $m1ConfigFiles = [];
        foreach ($this->areas[$area] as $subarea) {
            $m1ConfigFiles = array_merge(
                $m1ConfigFiles,
                $this->file->search(
                    'design/' . $area . '/' . $subarea . '/default/layout/*.xml',
                    $m1BaseDir . '/app'
                ),
                $this->file->search(
                    'design/' . $area . '/' . $subarea . '/default/layout/*/*.xml',
                    $m1BaseDir . '/app'
                ),
                $this->file->search(
                    'design/' . $area . '/' . $subarea . '/default/layout/*/*/*.xml',
                    $m1BaseDir . '/app'
                )
            );
        }
        return $m1ConfigFiles;
    }

    /**
     * @param string $area
     * @param string $m2BaseDir
     * @return string
     */
    protected function buildM1LayoutStringFile($area, $m2BaseDir)
    {
        $search = array_merge(
            glob($m2BaseDir . '/app/code/*/*/view/' . $area . '/layout/*.xml'),
            glob($m2BaseDir . '/app/code/*/*/view/' . $area . '/layout/*/*.xml'),
            glob($m2BaseDir . '/app/code/*/*/view/' . $area . '/layout/*/*/*.xml'),
            glob($m2BaseDir . '/app/code/*/*/view/base/layout/*.xml'),
            glob($m2BaseDir . '/app/code/*/*/view/base/layout/*/*.xml'),
            glob($m2BaseDir . '/app/code/*/*/view/base/layout/*/*/*.xml')
        );
        $m2LayoutHandles = '';
        foreach ($search as $fileName) {
            $m2LayoutHandles .= ' ' . preg_replace('/\.xml$/is', '', basename($fileName)) . ' ';
        }
        $this->m2LayoutHandles = $m2LayoutHandles;
        return $m2LayoutHandles;
    }

    /**
     * @param string $str
     * @return string|false
     */
    private function isInM2Layout($str)
    {
        if (!$this->handlerFound) {
            $match = [];
            $this->handlerFound =
                $this->handlerFound || preg_match('/ (' . $str . ') /is', $this->m2LayoutHandles, $match);
            if ($this->handlerFound) {
                $this->layoutResult = $match[1];
                return $match[1];
            }
        }
        return false;
    }

    /**
     * @param string $str
     * @return string
     */
    private function regexPlural($str)
    {
        return str_replace('_', '.{0,1}_', $str);

    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function processEnterprisePrefix($layoutHandler)
    {
        if (preg_match('/^enterprise\_/is', $layoutHandler)) {
            $layoutHandlerReplacement = preg_replace('/^enterprise_/is', 'magento_', $layoutHandler);
            $this->isInM2Layout($layoutHandlerReplacement);
            $this->switchFirstTwoWords($layoutHandlerReplacement);
            $layoutHandlerReplacement = $this->regexPlural($layoutHandlerReplacement);
            $this->isInM2Layout($layoutHandlerReplacement);
        }
    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function processPagePrefix($layoutHandler)
    {
        //page_ prefix
        $this->isInM2Layout('page_' . $layoutHandler);
    }

    /**
     * @param string $layoutHandler
     * @return string
     */
    private function replaceAdminhtmlPrefix($layoutHandler)
    {
        $layoutHandlerReplacement = preg_replace('/^adminhtml\_/is', '', $layoutHandler);
        $this->isInM2Layout($layoutHandlerReplacement);
        return $layoutHandlerReplacement;

    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function matchProductType($layoutHandler)
    {
        if (preg_match('/product_type/is', $layoutHandler)) {
            //replace product_type with product_view_type to match hanlders like catalog_product_view_type_bundle
            $this->isInM2Layout(str_replace('product_type', 'catalog_product_view_type', strtolower($layoutHandler)));
        }
    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function matchInvitations($layoutHandler)
    {
        if (preg_match('/invitation_/is', $layoutHandler)) {
            //replace adminhtml_invitation_index to match hanlders like invitations_index_index
            $this->isInM2Layout(str_replace('invitation_', 'invitations_index_', strtolower($layoutHandler)));
        }
    }


    /**
     * @param string $layoutHandler
     * @return void
     */
    private function switchFirstTwoWords($layoutHandler)
    {
        if (preg_match('/^([^\_]+)\_([^\_]+)\_(.+)$/', $layoutHandler)) {
            //switch the first 2 words
            $this->isInM2Layout(preg_replace('/^([^\_]+)\_([^\_]+)\_(.+)$/', '$2_$1_$3', $layoutHandler));
        }
    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function matchWordinBetween($layoutHandler)
    {
        //insert one word in between
        $this->isInM2Layout(str_replace('_', '_([^_]+)_', $layoutHandler));
    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function matchOneWordAsPrefix($layoutHandler)
    {
        $this->isInM2Layout('([^_]+)_' . $layoutHandler);
    }

    /**
     * @param string $layoutHandler
     * @return void
     */
    private function matchTwoWordsAsPrefix($layoutHandler)
    {
        $this->isInM2Layout('([^_]+)_([^_]+)_' . $layoutHandler);
    }


    /**
     * Retrieve & write references and referenced names from $layouts files
     *
     * @param array $layouts
     * @return $this
     * @throws \Exception
     */
    public function writeReferences($layouts)
    {
        if (empty($layouts)) {
            throw new \Exception("No layouts found");
        }
        $references = [];
        foreach ($this->referencePattern as $patternName => $xpath) {
            $result = [];
            foreach ($layouts as $layout) {
                $xml = simplexml_load_file($layout);
                $nodes = $xml->xpath($xpath);
                foreach ($nodes as $node) {
                    $result[(string)$node['name']] = '';
                }
            }
            $resultPrint = array_keys($result);
            sort($resultPrint);
            $references[$patternName] = $resultPrint;
        }


        $conflictReferences = $references['reference'];
        foreach ($references as $key => $names) {
            $this->addElements($names, $key);
            if ($key != 'reference') {
                $conflictReferences = array_diff($conflictReferences, $names);
            }
        }
        $this->addElements($conflictReferences, 'conflictReferences');

        $this->addElements(array_intersect($references['block'], $references['container']), 'conflictNames');


        $result = $this->formatter->format($this->referenceList->asXML());
        file_put_contents($this->referencesFile, $result);
        return $this;
    }

    /**
     * Create list from array
     *
     * @param array $data
     * @param string $type
     * @return $this
     */
    protected function addElements($data, $type)
    {
        array_walk_recursive(
            $data,
            function ($value) use ($type) {
                if (!$this->referenceList->xpath("//item[@type='{$type}' and @value='{$value}']")) {
                    $element = $this->referenceList->addChild('item');
                    $element->addAttribute('type', $type);
                    $element->addAttribute('value', $value);
                }
            }
        );
        return $this;
    }
}
