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

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PHPCuong_CurrencySymbolPosition::symbols_position';

    /**
     * Show Currency Symbol Position Management dialog
     *
     * @return void
     */
    public function execute()
    {
        // set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'PHPCuong_CurrencySymbolPosition::symbols_position'
        );

        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Currency Symbols Position'));
        $this->_view->renderLayout();
    }
}
