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

namespace PHPCuong\CurrencySymbolPosition\Observer;

class DisplayOptions implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \PHPCuong\CurrencySymbolPosition\Model\System\CurrencySymbolPositionFactory
     */
    protected $_currencySymbolPosition;

    /**
     * @param \PHPCuong\CurrencySymbolPosition\Model\System\CurrencySymbolPositionFactory $symbolPositionSystemFactory
     */
    public function __construct(
        \PHPCuong\CurrencySymbolPosition\Model\System\CurrencySymbolPositionFactory $symbolPositionSystemFactory
    ) {
        $this->_currencySymbolPosition = $symbolPositionSystemFactory;
    }

    /**
     * hook to event currency_display_options_forming
     * change currency symbol position
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // Get the position value from configuration
        $positionValue = $this->_currencySymbolPosition->create()->unserializeStoreConfig();
        $baseCode = $observer->getEvent()->getBaseCode();
        $position = null;
        foreach ($positionValue as $key => $value) {
            if ($key == $baseCode) {
                $position = (int)$value;
            }
        }

        if (in_array($position, [\Magento\Framework\Currency::RIGHT, \Magento\Framework\Currency::LEFT])) {
            $currencyOptions = $observer->getEvent()->getCurrencyOptions();
            // change currency symbol position to $position
            $currencyOptions->setData('position', (int)$position);
        }

        return $this;
    }
}
