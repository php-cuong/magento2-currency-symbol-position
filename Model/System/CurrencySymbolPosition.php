<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    PHPCuong
 * @package     PHPCuong_CurrencySymbolPosition
 * @copyright   Copyright (c) 2019-2020 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace PHPCuong\CurrencySymbolPosition\Model\System;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Serialize\Serializer\Json;

class CurrencySymbolPosition
{
    /**
     * Custom currency symbol position properties
     *
     * @var array
     */
    protected $_positionsData = [];

    /**
     * Store id
     *
     * @var string|null
     */
    protected $_storeId;

    /**
     * Website id
     *
     * @var string|null
     */
    protected $_websiteId;

    /**
     * Cache types which should be invalidated
     *
     * @var array
     */
    protected $_cacheTypes = [
        \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
        \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
        \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
        \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
    ];

    /**
     * Config path to custom currency symbol value
     */
    const XML_PATH_CUSTOM_CURRENCY_SYMBOL_POSITION = 'currency/options/symbol_position';

    const XML_PATH_ALLOWED_CURRENCIES = \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW;

    /*
     * Separator used in config in allowed currencies list
     */
    const ALLOWED_CURRENCIES_CONFIG_SEPARATOR = ',';

    /**
     * Config currency section
     */
    const CONFIG_SECTION = 'currency';

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_coreConfig;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $serializer = null
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_configFactory = $configFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->_systemStore = $systemStore;
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return currency symbol position properties array based on configuration values
     *
     * @return array
     */
    public function getPositionsData()
    {
        if ($this->_positionsData) {
            return $this->_positionsData;
        }

        $this->_positionsData = [];

        $currentSymbols = $this->unserializeStoreConfig();

        foreach ($this->getAllowedCurrencies() as $code) {
            $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'];
            $symbol = $currencies[$code][0] ? '' : $code;
            $name = $currencies[$code][1] ?: $code;
            $this->_positionsData[$code] = ['parentPosition' => $symbol, 'displayName' => $name];

            if (isset($currentSymbols[$code]) && !empty($currentSymbols[$code])) {
                $this->_positionsData[$code]['displayPosition'] = $currentSymbols[$code];
            } else {
                $this->_positionsData[$code]['displayPosition'] = $this->_positionsData[$code]['parentPosition'];
            }
            $this->_positionsData[$code]['inherited'] =
                ($this->_positionsData[$code]['parentPosition'] == $this->_positionsData[$code]['displayPosition']);
        }

        return $this->_positionsData;
    }

    /**
     * Save currency symbol postion to config
     *
     * @param  $symbols array
     * @return $this
     */
    public function setPositionData($symbols = [])
    {
        foreach ($this->getPositionsData() as $code => $values) {
            if (isset($symbols[$code]) && ($symbols[$code] == $values['parentPosition'] || empty($symbols[$code]))) {
                unset($symbols[$code]);
            }
        }
        $value = [];
        if ($symbols) {
            $value['options']['fields']['symbol_position']['value'] = $this->serializer->serialize($symbols);
        } else {
            $value['options']['fields']['symbol_position']['inherit'] = 1;
        }

        $this->_configFactory->create()
            ->setSection(self::CONFIG_SECTION)
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups($value)
            ->save();

        $this->_eventManager->dispatch(
            'admin_system_config_changed_section_currency_symbol_position_before_reinit',
            ['website' => $this->_websiteId, 'store' => $this->_storeId]
        );

        // reinit configuration
        $this->_coreConfig->reinit();

        $this->clearCache();
        //Reset position cache since new data is added
        $this->_positionsData = [];

        $this->_eventManager->dispatch(
            'admin_system_config_changed_section_currency_symbol_position',
            ['website' => $this->_websiteId, 'store' => $this->_storeId]
        );

        return $this;
    }

    /**
     * Return custom currency symbol position by currency code
     *
     * @param string $code
     * @return string|false
     */
    public function getCurrencySymbolPosition($code)
    {
        $customSymbols = $this->unserializeStoreConfig();
        if (array_key_exists($code, $customSymbols)) {
            return $customSymbols[$code];
        }

        return false;
    }

    /**
     * Clear translate cache
     *
     * @return $this
     */
    protected function clearCache()
    {
        // clear cache for frontend
        foreach ($this->_cacheTypes as $cacheType) {
            $this->_cacheTypeList->invalidate($cacheType);
        }
        return $this;
    }

    /**
     * Unserialize data from Store Config.
     *
     * @param int $storeId
     * @return array
     */
    public function unserializeStoreConfig($storeId = null)
    {
        $configPath = self::XML_PATH_CUSTOM_CURRENCY_SYMBOL_POSITION;
        $result = [];
        $configData = (string)$this->_scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($configData) {
            $result = $this->serializer->unserialize($configData);
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Return allowed currencies
     *
     * @return array
     */
    protected function getAllowedCurrencies()
    {
        $allowedCurrencies = explode(
            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
            $this->_scopeConfig->getValue(
                self::XML_PATH_ALLOWED_CURRENCIES,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
        );

        $storeModel = $this->_systemStore;
        /** @var \Magento\Store\Model\Website $website */
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            /** @var \Magento\Store\Model\Group $group */
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                /** @var \Magento\Store\Model\Store $store */
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $websiteSymbols = $website->getConfig(self::XML_PATH_ALLOWED_CURRENCIES);
                        $allowedCurrencies = array_merge(
                            $allowedCurrencies,
                            explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $websiteSymbols)
                        );
                    }
                    $storeSymbols = $this->_scopeConfig->getValue(
                        self::XML_PATH_ALLOWED_CURRENCIES,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $allowedCurrencies = array_merge(
                        $allowedCurrencies,
                        explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $storeSymbols)
                    );
                }
            }
        }
        return array_unique($allowedCurrencies);
    }
}
