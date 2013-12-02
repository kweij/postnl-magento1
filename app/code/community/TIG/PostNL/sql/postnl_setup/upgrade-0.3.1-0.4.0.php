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
 
$installer = $this;

$installer->startSetup();

$postnlOrderTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_checkout/order'))
    /**
     * Entity ID
     */
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Entity Id')
    /**
     * Mage_Sales_Model_Order ID
     */
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Order Id')
    /**
     * Mage_Sales_Model_Quote ID
     */
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        ), 'Quote Id')
    /**
     * PostNL Checkout ordertoken used by PostNL to reference an order
     */
    ->addColumn('token', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Token')
    /**
     * Optional product code required to ship PakjeGemak orders
     */
    ->addColumn('product_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable' => true,
        ), 'Product Code')
    /**
     * Flag that determines whether or not this is a PakjeGemak shipment
     */
    ->addColumn('is_pakje_gemak', Varien_Db_Ddl_Table::TYPE_BOOLEAN, false, array(
        'unsigned' => true,
        'default'  => 0,
        ), 'Is PakjeGemak')
    /**
     * Date on which the shipment has to be confirmed in order to be delivered on time
     */
    ->addColumn('confirm_date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => true,
        ), 'Confirm Date')
    ->addIndex($installer->getIdxName('postnl_checkout/order', array('order_id')), 
        array('order_id'))
    ->addIndex($installer->getIdxName('postnl_checkout/order', array('quote_id')), 
        array('quote_id'))
    ->addForeignKey($installer->getFkName('postnl_checkout/order', 'order_id', 'sales/order', 'entity_id'),
        'order_id', $installer->getTable('sales/order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('postnl_checkout/order', 'quote_id', 'sales/quote', 'entity_id'),
        'quote_id', $installer->getTable('sales/quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Order');

$installer->getConnection()->createTable($postnlOrderTable);

$installer->getConnection()
          ->addColumn(
               $installer->getTable('postnl_core/shipment'),
               'is_pakje_gemak',
               array(
                   'default'  => 0,
                   'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
                   'comment'  => 'Is PakjeGemak',
               )
          );

$installer->endSetup();