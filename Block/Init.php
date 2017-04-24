<?php

namespace Magmodules\Sooqr\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magmodules\Sooqr\Helper\General as GeneralHelper;
use Magento\Framework\Locale\Resolver;

class Init extends Template
{

    private $scopeConfig;
    private $generalHelper;
    private $storeManager;
    private $storeId;
    private $localeResolver;

    /**
     * Init constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param GeneralHelper         $generalHelper
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        GeneralHelper $generalHelper,
        Resolver $localeResolver,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->generalHelper = $generalHelper;
        $this->storeManager = $storeManager;
        $this->storeId = $this->storeManager->getStore()->getId();
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|mixed
     */
    public function getFrontendEnabled()
    {
        return $this->generalHelper->getFrontendEnabled($this->storeId);
    }

    /**
     * @return array
     */
    public function getSooqrOptions()
    {
        $accountId = $this->generalHelper->getAccountId($this->storeId);
        $options = ['account' => $accountId, 'fieldId' => 'search'];

        if ($parent = $this->generalHelper->getParent($this->storeId)) {
            $options['containerParent'] = $parent;
        }

        if ($version = $this->generalHelper->getVersion($this->storeId)) {
            $options['version'] = $version;
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getSooqrLanguage()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return mixed
     */
    public function getSooqrJavascript()
    {
        if ($customJs = $this->generalHelper->getCustomJs($this->storeId)) {
            return $customJs;
        }
    }

    /**
     * @return mixed
     */
    public function isTrackingEnabled()
    {
        return $this->generalHelper->getStatistics($this->storeId);
    }

    /**
     * @return string
     */
    public function getSooqrScriptUri()
    {
        if ($statging = $this->generalHelper->getStaging($this->storeId)) {
            return 'static.staging.sooqr.com/sooqr.js';
        }
        return 'static.sooqr.com/sooqr.js';
    }

    /**
     * @return mixed
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}