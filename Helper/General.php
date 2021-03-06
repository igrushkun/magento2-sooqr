<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\Sooqr\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\ResourceModel\Config as ConfigData;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigDataCollectionFactory;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magmodules\Sooqr\Logger\SooqrLogger;

/**
 * Class General
 *
 * @package Magmodules\Sooqr\Helper
 */
class General extends AbstractHelper
{

    const MODULE_CODE = 'Magmodules_Sooqr';
    const XPATH_EXTENSION_ENABLED = 'magmodules_sooqr/general/enable';
    const XPATH_FRONTEND_ENABLED = 'magmodules_sooqr/implementation/enable';
    const XPATH_HEADDATA_ENABLED = 'magmodules_sooqr/implementation/headdata';
    const XPATH_CRON_ENABLED = 'magmodules_sooqr/generate/cron';
    const XPATH_GENERATE_ENABLED = 'magmodules_sooqr/generate/enable';
    const XPATH_API_KEY = 'magmodules_sooqr/implementation/api_key';
    const XPATH_ACCOUNT_ID = 'magmodules_sooqr/implementation/account_id';
    const XPATH_PARENT = 'magmodules_sooqr/implementation/advanced_parent';
    const XPATH_VERSION = 'magmodules_sooqr/implementation/advanced_version';
    const XPATH_CUSTOM_JS = 'magmodules_sooqr/implementation/advanced_custom_js';
    const XPATH_STATISTICS = 'magmodules_sooqr/implementation/statistics';
    const XPATH_STAGING = 'magmodules_sooqr/implementation/advanced_staging';
    const XPATH_TOKEN = 'magmodules_sooqr/general/token';
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var ProductMetadataInterface
     */
    private $metadata;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ConfigDataCollectionFactory
     */
    private $configDataCollectionFactory;
    /**
     * @var ConfigData
     */
    private $coreDate;
    /**
     * @var TimezoneInterface
     */
    private $localeDate;
    /**
     * @var SooqrLogger
     */
    private $logger;

    /**
     * General constructor.
     *
     * @param Context                     $context
     * @param StoreManagerInterface       $storeManager
     * @param ModuleListInterface         $moduleList
     * @param ProductMetadataInterface    $metadata
     * @param ConfigDataCollectionFactory $configDataCollectionFactory
     * @param ConfigData                  $config
     * @param DateTime                    $coreDate
     * @param TimezoneInterface           $localeDate
     * @param SooqrLogger        $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $metadata,
        ConfigDataCollectionFactory $configDataCollectionFactory,
        ConfigData $config,
        DateTime $coreDate,
        TimezoneInterface $localeDate,
        SooqrLogger $logger
    ) {
        $this->storeManager = $storeManager;
        $this->moduleList = $moduleList;
        $this->metadata = $metadata;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
        $this->config = $config;
        $this->coreDate = $coreDate;
        $this->localeDate = $localeDate;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function getFrontendEnabled($storeId = null)
    {
        if (!$this->getEnabled()) {
            return false;
        }

        if (!$this->getStoreValue(self::XPATH_FRONTEND_ENABLED, $storeId)) {
            return false;
        }

        if (!$this->getAccountId($storeId) || !$this->getApiKey($storeId)) {
            return false;
        }

        return true;
    }

    /**
     * @param null $storeId
     *
     * @return bool|mixed
     */
    public function getHeadDataEnabled($storeId = null)
    {
        if (!$this->getEnabled()) {
            return false;
        }

        if (!$this->getStoreValue(self::XPATH_HEADDATA_ENABLED, $storeId)) {
            return false;
        }

        return true;
    }

    /**
     * General check if Extension is enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return (boolean)$this->getStoreValue(self::XPATH_EXTENSION_ENABLED);
    }

    /**
     * Get configuration data.
     *
     * @param      $path
     * @param      $scope
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStoreValue($path, $storeId = null, $scope = null)
    {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAccountId($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_ACCOUNT_ID, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getApiKey($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_API_KEY, $storeId);
    }

    /**
     * Check if cron is enabled.
     *
     * @return bool
     */
    public function getCronEnabled()
    {
        return (boolean)$this->getStoreValue(self::XPATH_CRON_ENABLED);
    }

    /**
     * Get configuration data array.
     *
     * @param      $path
     * @param null $storeId
     * @param null $scope
     *
     * @return array|mixed
     */
    public function getStoreValueArray($path, $storeId = null, $scope = null)
    {
        $value = $this->getStoreValue($path, $storeId, $scope);

        $result = json_decode($value, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            if (is_array($result)) {
                return $result;
            }
            return [];
        }

        $value = @unserialize($value);
        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    /**
     * Get Uncached Value from core_config_data
     *
     * @param      $path
     * @param null $storeId
     *
     * @return mixed
     */
    public function getUncachedStoreValue($path, $storeId)
    {
        $collection = $this->configDataCollectionFactory->create()
            ->addFieldToSelect('value')
            ->addFieldToFilter('path', $path);

        if ($storeId > 0) {
            $collection->addFieldToFilter('scope_id', $storeId);
            $collection->addFieldToFilter('scope', 'stores');
        } else {
            $collection->addFieldToFilter('scope_id', 0);
            $collection->addFieldToFilter('scope', 'default');
        }

        $collection->getSelect()->limit(1);

        return $collection->getFirstItem()->getData('value');
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getParent($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_PARENT, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getVersion($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_VERSION, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCustomJs($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_CUSTOM_JS, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStatistics($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_STATISTICS, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getStaging($storeId = null)
    {
        return $this->getStoreValue(self::XPATH_STAGING, $storeId);
    }

    /**
     * Set configuration data function
     *
     * @param      $value
     * @param      $key
     * @param null $storeId
     */
    public function setConfigData($value, $key, $storeId = null)
    {
        if ($storeId) {
            $this->config->saveConfig($key, $value, 'stores', $storeId);
        } else {
            $this->config->saveConfig($key, $value, 'default', 0);
        }
    }

    /**
     * Returns current version of the extension
     *
     * @return mixed
     */
    public function getExtensionVersion()
    {
        $moduleInfo = $this->moduleList->getOne(self::MODULE_CODE);

        return $moduleInfo['setup_version'];
    }

    /**
     * Returns current version of Magento
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->metadata->getVersion();
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->getStoreValue(self::XPATH_TOKEN);
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function getEnabledArray($path)
    {
        $storeIds = [];
        if (!$this->getEnabled()) {
            return $storeIds;
        }

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            if ($this->getStoreValue($path, $store->getId())) {
                $storeIds[] = $store->getId();
            }
        }

        return $storeIds;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getCurrecyCode($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCode();
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getStoreName($storeId = null)
    {
        $baseUrl = $this->getBaseUrl($storeId);
        return str_replace(['https://', 'http://', 'www'], '', $baseUrl);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBaseUrl($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }

    /**
     * @param $storeId
     * @param $param
     *
     * @return mixed
     */
    public function getUrl($storeId, $param)
    {
        return $this->storeManager->getStore($storeId)->getUrl($param);
    }

    /**
     * General check if Generation is enabled.
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function getGenerateEnabled($storeId = null)
    {
        return (boolean)$this->getStoreValue(self::XPATH_GENERATE_ENABLED, $storeId);
    }

    /**
     * @param $id
     * @param $data
     */
    public function addTolog($id, $data)
    {
        $debug = true;
        if ($debug) {
            $this->logger->add($id, $data);
        }
    }

    /**
     * @return string
     */
    public function getDateTime()
    {
        return $this->coreDate->date("Y-m-d H:i:s");
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getLocaleDate($storeId)
    {
        return $this->localeDate->scopeDate($storeId);
    }
}
