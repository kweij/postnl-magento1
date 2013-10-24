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

$postnlShipmentStatusHistoryTable = $installer->getConnection()
    ->newTable($installer->getTable('postnl_core/shipment_status_history'))
    ->addColumn('status_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Status Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Parent Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Description')
    ->addColumn('phase', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
        'nullable'  => false,
        ), 'Phase')
    ->addColumn('timestamp', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Timestamp')
    ->addIndex($installer->getIdxName('postnl_core/shipment_status_history', array('parent_id')), 
        array('parent_id'))
    ->addForeignKey($installer->getFkName('postnl_core/shipment_status_history', 'parent_id', 'postnl_core/shipment', 'entity_id'),
        'parent_id', $installer->getTable('postnl_core/shipment'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('TIG PostNL Shipment Status History');

$installer->getConnection()->createTable($postnlShipmentStatusHistoryTable);

$installer->endSetup();