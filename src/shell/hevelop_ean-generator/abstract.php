<?php

if (!defined('MAGE_BASE_DIR')) {
    chdir(__DIR__ . '/../../../../../../htdocs/');
    define('MAGE_BASE_DIR', getcwd());
}

require_once MAGE_BASE_DIR . '/shell/local/abstract.php';

abstract class Hevelop_EanGenerator_Shell_Abstract extends Local_Shell_Abstract
{
    const INDEXER_CODE_CATALOG_PRODUCT_ATTRIBUTE = 'catalog_product_attribute';
    const INDEXER_CODE_CATALOG_PRODUCT_PRICE = 'catalog_product_price';
    const INDEXER_CODE_CATALOG_URL = 'catalog_url';
    const INDEXER_CODE_CATALOG_PRODUCT_FLAT = 'catalog_product_flat';
    const INDEXER_CODE_CATALOG_CATEGORY_FLAT = 'catalog_category_flat';
    const INDEXER_CODE_CATALOG_CATEGORY_PRODUCT = 'catalog_category_product';
    const INDEXER_CODE_CATALOGSEARCH_FULLTEXT = 'catalogsearch_fulltext';
    const INDEXER_CODE_CATALOGINVENTORY_STOCK = 'cataloginventory_stock';
    const INDEXER_CODE_TAG_SUMMARY = 'tag_summary';
    const INDEXER_CODE_CONTENTMANAGER_INDEXER = 'contentmanager_indexer';
    const INDEXER_CODE_MANA_DB_REPLICATOR = 'mana_db_replicator';

    /**
     * @var
     */
    protected $_skus;

    /**
     * @var
     */
    protected $_storeIds;

    /**
     * @var
     */
    protected $_action;

    /**
     * @var
     */
    protected $_method;

    /**
     * @var bool
     */
    protected $_dryRun = false;

    /**
     * @var bool
     */
    protected $_force = false;

    /**
     * @var array
     */
    protected $_domDocumentsCache = array();

    /**
     * @var array
     */
    protected $_xPathsCache = array();

    /**
     * @var array
     */
    protected $disabledProcesses = array();


    /**
     * Returns project export dir
     *
     * @return string
     */
    public function getExportDir()
    {
        $exportDir = Mage::getBaseDir('export');

        if (is_dir($exportDir) === false) {
            @mkdir($exportDir, 0777, true);
        }
        return $exportDir;

    }//end getExportDir()


    /**
     * Tessabit_Shell_Abstract constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Time limit to infinity.
        set_time_limit(0);
        ini_set('memory_limit', -1);
    }//end __construct()


    /**
     * _camelize
     *
     * @param $name
     *
     * @return string
     */
    protected function _camelize($name)
    {
        return lcfirst(uc_words($name, ''));

    }//end _camelize()


    /**
     * _underscore
     *
     * @param $name
     *
     * @return string
     */
    protected function _underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        return $result;

    }//end _underscore()


    /**
     * Cache loaded DOMDocument and return the object.
     *
     * @param string $filePath file path to load into DOMDocument
     * @param bool $validateDtd flag to validate also DTD strictly
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function _getDomDocument($filePath, $validateDtd = false)
    {
        try {
            if (file_exists($filePath) === false) {
                throw new InvalidArgumentException(
                    "File not found in path $filePath"
                );
            }

            $hash = md5($filePath);
            if (array_key_exists($hash, $this->_domDocumentsCache) === false) {
                $obj = new DOMDocument();
                $obj->load($filePath);

                $check = $obj->getElementsByTagName('DocumentElement');

                if ($check->length === 0) {
                    throw new InvalidArgumentException(
                        "File $filePath is not a valid XML to load with DOMDocument"
                    );
                }

                if ($validateDtd === true && $obj->validate() === false) {
                    throw new InvalidArgumentException(
                        "File $filePath is not a valid XML to load with DOMDocument"
                    );
                }

                $this->_domDocumentsCache[$hash] = $obj;
            }//end if
        } catch (Exception $e) {
            $this->log->emerg($e->getMessage());
            exit();
        }//end try
        return $this->_domDocumentsCache[$hash];

    }//end _getDomDocument()


    /**
     * _getXPath
     *
     * @param DOMDocument $domDocument
     * @param $key
     *
     * @return mixed
     */
    public function _getXPath(DOMDocument $domDocument, $key)
    {
        $hash = md5($key);
        if (array_key_exists($hash, $this->_xPathsCache) === false) {
            $this->_xPathsCache[$hash] = new DOMXPath($domDocument);
        }

        return $this->_xPathsCache[$hash];

    }//end _getXPath()


    /**
     * Unsets loaded DOMDocument object.
     *
     * @param $filePath
     *
     * @return $this
     */
    public function _clearDomDocumentCache($filePath)
    {
        $hash = md5($filePath);
        unset($this->_domDocumentsCache[$hash]);
        return $this;

    }//end _clearDomDocumentCache()


    /**
     * disableIndexer
     *
     * @return $this
     */
    public function disableIndexer(array $filterProcesses = array())
    {
        $indexer = Mage::getModel('index/indexer');
        $processesCollection = $indexer->getProcessesCollection();

        if (count($filterProcesses) > 0) {
            $processesCollection->addFieldToFilter(
                'indexer_code',
                array('in' => $filterProcesses)
            );
        }

        foreach ($processesCollection as $process) {
            $this->disabledProcesses[$process->getIndexerCode()] = $process->getMode();
            $this->log->info('Disabling process ' . $process->getIndexerCode());
            if ($process->getMode() !== Mage_Index_Model_Process::MODE_MANUAL) {
                $process->setData('mode', 'manual')->save();
            }
        }

        return $this;

    }//end disableIndexer()


    /**
     * runIndexer
     *
     * @return $this
     */
    public function runIndexer(array $filterProcesses = array())
    {
        $indexer = Mage::getModel('index/indexer');
        $processesCollection = $indexer->getProcessesCollection();

        if (count($filterProcesses) > 0) {
            $processesCollection->addFieldToFilter(
                'indexer_code',
                array('in' => $filterProcesses)
            );

            foreach ($processesCollection as $process) {
                $this->log->info('Running process ' . $process->getIndexerCode());
                try {
                    $process->reindexEverything();
                } catch (Exception $e) {
                    $this->log->err($e->getMessage());
                    Mage::logException($e);
                }
            }

        }

        return $this;

    }//end runIndexer()


    /**
     * restoreIndexer
     *
     * @return $this
     */
    public function restoreIndexer()
    {
        $processes = array();
        $indexer = Mage::getModel('index/indexer');
        $processesCollection = $indexer->getProcessesCollection();

        $this->log->info('Restoring indexer processes');

        $processesCollection->addFieldToFilter(
            'indexer_code',
            array('in' => array_keys($this->disabledProcesses))
        );

        foreach ($processesCollection as $process) {
            $previousMode = $this->disabledProcesses[$process->getIndexerCode()];
            $this->log->info('Process ' . $process->getIndexerCode() . ' set to ' . $previousMode);
            $process->setData(
                'mode',
                $previousMode
            )->save();
        }//end foreach

        return $this;

    }//end restoreIndexer()


    /**
     * __destruct
     */
    public function __destruct()
    {

        if (count($this->disabledProcesses) > 0) {
            $this->restoreIndexer();
        }

        parent::__destruct();
    }//end __destruct()


    /**
     * getStoreIds
     *
     * @return array
     */
    public function getStoreIds()
    {
        $storeIds = $this->_storeIds;
        return $storeIds;

    }//end getStoreIds


    public function run()
    {

        if (!defined('SHELL_INITED')) {
            define('SHELL_INITED', true);

            $this->_action = $this->getArg('action');
            $this->_method = $this->_camelize($this->_action . '_action');
            $this->_skus = $this->getArg('skus') ? explode(',', $this->getArg('skus')) : array();
            $this->_dryRun = $this->getArg('dry-run') ? true : false;
            $this->_force = $this->getArg('force') ? true : false;
            $this->_storeIds = $this->getArg('store-ids') ? explode(',', $this->getArg('store-ids')) : array();

            if (method_exists($this, $this->_method)) {
                $this->log->info("Starting execution of action {$this->_action}");
                $this->{$this->_method}();
                $this->log->info("Ended execution of action {$this->_action}");
            } else {
                $this->log->err("Action {$this->_action} unavailable");
                echo $this->usageHelp();
            }
        }
        return $this;

    }


    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {

        $actionList = '';

        $class = new ReflectionClass($this);
        $filename = basename($class->getFileName());
        $methods = $class->getMethods();

        usort($methods, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        foreach ($methods as $method) {
            $methodName = $method->getName();
            $position = strpos($methodName, 'Action');
            if ($position !== false && $position + strlen('Action') === strlen($methodName)) {
                $actionList .= "                 - {$this->_underscore(substr($methodName,0,$position))}\n";
            }
        }

        $usage = <<<USAGE
Usage:  php -f {$filename} -- [options]

  --action      an action defined

                list of available methods:

{$actionList}

  -h            Short alias for help
  help          This help

USAGE;
        return $usage;
    }


    public function getDryRun()
    {
        return $this->_dryRun;
    }

    public function setDryRun($dryRun)
    {
        $this->_dryRun = $dryRun;
        return $this;
    }

    public function getElapsedTime()
    {
        return round(microtime(true) - $this->_timeStart, 3);
    }

    public function logElapsedTime()
    {
        $this->log->debug("Elapsed time: " . $this->getElapsedTime());
    }


}