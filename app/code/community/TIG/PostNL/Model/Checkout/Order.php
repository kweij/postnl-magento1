<?php
/**
 *                  ___________       __            __   
 *                  \__    ___/____ _/  |_ _____   |  |  
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/       
 *          ___          __                                   __   
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_ 
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |  
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|  
 *                  \/                           \/               
 *                  ________       
 *                 /  _____/_______   ____   __ __ ______  
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \ 
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/ 
 *                        \/                       |__|    
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL: 
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Model_Checkout_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('postnl_checkout/order');
    }
    
    /**
     * Gets the order associated with this PostNL Checkout Order
     * 
     * @return Mage_Sales_Model_Order | null
     */
    public function getOrder()
    {
        if ($this->getData('order')) {
            return $this->getData('order');
        }
        
        if (!$this->getOrderId()) {
            return null;
        }
        
        $order = Mage::getModel('sales/order')->load($this->getOrderId());
        
        $this->setOrder($order);
        return $order;
    }
    
    /**
     * Gets the quote associated with this PostNL Checkout Order
     * 
     * @return Mage_Sales_Model_Quote | null
     */
    public function getQuote()
    {
        if ($this->getData('quote')) {
            return $this->getData('quote');
        }
        
        if (!$this-getQuoteId()) {
            return null;
        }
        
        $order = Mage::getModel('sales/quote')->load($this->getQuoteId());
        
        $this->setQuote($order);
        return $order;
    }
    
    /**
     * Alias for getQuoteId()
     * 
     * @return int
     * 
     * @see TIG_PostNL_Model_Checkout_Order::getQuoteId()
     */
    public function getExtRef()
    {
        return $this->getQuoteId();
    }
}
