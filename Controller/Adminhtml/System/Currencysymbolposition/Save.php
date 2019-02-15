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

namespace PHPCuong\CurrencySymbolPosition\Controller\Adminhtml\System\Currencysymbolposition;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PHPCuong_CurrencySymbolPosition::symbols_position';

    /**
     * Save custom Currency symbol position
     *
     * @return void
     */
    public function execute()
    {
        $symbolsDataArray = $this->getRequest()->getParam('custom_currency_symbol_position', null);

        try {
            $this->_objectManager->create(\PHPCuong\CurrencySymbolPosition\Model\System\CurrencySymbolPosition::class)
                ->setPositionData($symbolsDataArray);
            $this->messageManager->addSuccess(__('You applied the custom currency symbol positions.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
