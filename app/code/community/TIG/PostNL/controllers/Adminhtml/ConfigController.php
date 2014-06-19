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
 * @copyright   Copyright (c) 2014 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Base XML path of config settings that will be checked
     */
    const XML_BASE_PATH = 'postnl/cif';

    /**
     * XML paths to passwords
     */
    const XPATH_LIVE_PASSWORD = 'postnl/cif/live_password';
    const XPATH_TEST_PASSWORD = 'postnl/cif/test_password';

    /**
     * Max size for individual log files and the total size of all logs (in bytes).
     */
    const LOG_MAX_SIZE       = '104857600'; //100MB
    const LOG_MAX_TOTAL_SIZE = '1073741824'; //1GB

    /**
     * @var boolean
     */
    protected $_isTestMode = false;

    /**
     * Validate the extension's account settings.
     *
     * @return $this
     */
    public function validateAccountAction()
    {
        /**
         * Get all post data
         */
        $data = $this->getRequest()->getPost();

        /**
         * Validate that all required fields are entered
         */
        if (!isset($data['customerNumber'])
            || !isset($data['customerCode'])
            || !isset($data['username'])
            || !isset($data['password'])
            || !isset($data['locationCode'])
            || !isset($data['isTestMode'])
        ) {
            $this->getResponse()
                 ->setBody('missing_data');

            return $this;
        }

        $data = $this->_getInheritedValues($data);

        if ($data['isTestMode'] === 'true') {
            $this->_isTestMode = true;
        }

        /**
         * Attempt to generate a barcode to test the account settings. This will result in an exception if the settings
         * are invalid.
         */
        try {
            /**
             * If the password field has not been edited since the last time it was saved, it will contain 6 asterisks
             * for security reasons. In that case, we need to read and decrypt the password from the database.
             */
            if ($data['password'] == '******') {
                $data['password'] = $this->_getPassword(false);
            } elseif ($data['password'] == 'inherit') {
                $data['password'] = $this->_getPassword(true);
            }

            /**
             * Hash the password
             */
            $data['password'] = sha1($data['password']);

            /**
             * Load the CIF model and set to test mode to false
             *
             * @var TIG_PostNL_Model_Core_Cif $cif
             */
            $cif = Mage::getModel('postnl_core/cif')
                       ->setTestMode($this->_isTestMode);

            $response = $cif->generateBarcodePing($data);
        } catch (Exception $e) {
            $this->getResponse()
                 ->setBody('error');

            return $this;
        }

        /**
         * A positive result would be a string, namely a barcode.
         */
        if (!is_string($response)) {
            $this->getResponse()
                 ->setBody('invalid_response');

            return $this;
        }

        $this->getResponse()
             ->setBody('ok');

        return $this;
    }

    /**
     * Checks each field to see if it has used the 'use default checkbox'. If so, get the default value from the
     * database.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _getInheritedValues($data)
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $baseXpath = self::XML_BASE_PATH;

        $usernameXpath = $baseXpath . '/live_username';
        if ($this->_isTestMode) {
            $usernameXpath = $baseXpath . '/test_username';
        }

        foreach ($data as $key => &$value) {
            if ($value != 'inherit') {
                continue;
            }

            switch ($key) {
                case 'customerNumber':
                    $value = Mage::getStoreConfig($baseXpath . '/customer_number', $storeId);
                    break;
                case 'customerCode':
                    $value = Mage::getStoreConfig($baseXpath . '/customer_code', $storeId);
                    break;
                case 'username':
                    $value = Mage::getStoreConfig($usernameXpath, $storeId);
                    break;
                case 'locationCode':
                    $value = Mage::getStoreConfig($baseXpath . '/collection_location', $storeId);
                    break;
                //No default
                //Note that the password field is not checked. That field has it's own check later on.
            }
        }

        return $data;
    }

    /**
     * Gets the password from system/config.
     * Passwords will be decrypted using Magento's encryption key and then hashed using sha1
     *
     * @param boolean $inherit
     *
     * @return string
     */
    protected function _getPassword($inherit = false)
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

        $xpath = self::XPATH_LIVE_PASSWORD;
        if ($this->_isTestMode) {
            $xpath = self::XPATH_TEST_PASSWORD;
        }

        $websiteCode = $this->getRequest()->getParam('website');
        if (!$inherit && !empty($websiteCode)) {
            $website = Mage::getModel('core/website')->load($websiteCode, 'code');
            $password = $website->getConfig($xpath);
        } else {
            $password = Mage::getStoreConfig($xpath, $storeId);
        }

        $password = Mage::helper('core')->decrypt($password);

        return trim($password);
    }

    /**
     * Export shipping table rates in csv format.
     *
     * @return $this
     */
    public function exportTableratesAction()
    {
        $fileName   = 'tablerates.csv';

        /**
         * @var TIG_PostNL_Block_Adminhtml_Carrier_Postnl_Tablerate_Grid $gridBlock
         */
        $gridBlock  = $this->getLayout()->createBlock('postnl_adminhtml/carrier_postnl_tablerate_grid');
        $website    = Mage::app()->getWebsite($this->getRequest()->getParam('website'));

        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $website->getConfig('carriers/postnl/condition_name');
        }

        $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);

        $content = $gridBlock->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);

        return $this;
    }

    /**
     * Download all PostNL log files as a zip file.
     *
     * @return $this
     */
    public function downloadLogsAction()
    {
        $helper = Mage::helper('postnl');

        /**
         * Get the folder where all PostNL logs are stored and make sure it exists.
         */
        $logFolder = Mage::getBaseDir('var') . DS . 'log' . DS . 'TIG_PostNL';
        if (!is_dir($logFolder)) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0172', 'error', 'No valid log files were found.');

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }

        /**
         * Get all log files in the log folder and a list of all logs that are allowed for this download.
         */
        $logs          = glob($logFolder . DS . '*.log');
        $allowedLogs   = Mage::helper('postnl')->getLogFiles();

        /**
         * Make sure each log is valid and put the valid logs in an array with the log's filename as the key. We need
         * this later on to prevent the entire directory structure from being included in the zip file.
         */
        $logsWithNames = array();
        $totalSize     = 0;
        foreach ($logs as $log) {
            $logName = explode(DS, $log);
            $logName = end($logName);

            /**
             * Make sure this log is allowed.
             */
            if (!in_array($logName, $allowedLogs)) {
                continue;
            }

            /**
             * Make sure the log is a file and is readable.
             */
            if (!is_file($log) || !is_readable($log)) {
                continue;
            }

            /**
             * Make sure the log is not too large. Otherwise we won't be able to read it anyway.
             */
            $fileSize = filesize($log);
            if ($fileSize > self::LOG_MAX_SIZE) {
                $helper->addSessionMessage(
                    'adminhtml/session',
                    'POSTNL-0173',
                    'warning',
                    $this->__('Log %s is too large and was skipped.', $logName)
                );
                continue;
            }

            /**
             * Add the log's filesize to the total size of all valid logs and add the log to the array.
             */
            $totalSize += $fileSize;
            $logsWithNames[$logName] = $log;
        }

        /**
         * If we have no valid logs, there is nothing to do.
         */
        if (empty($logsWithNames)) {
            $helper->addSessionMessage('adminhtml/session', 'POSTNL-0172', 'error', 'No valid log files were found.');

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }

        /**
         * Make sure the total size of all logs is not too large.
         */
        if ($totalSize > self::LOG_MAX_TOTAL_SIZE) {
            $helper->addSessionMessage(
                'adminhtml/session',
                'POSTNL-0174',
                'error',
                'The total size of all log files exceeds the maximum size allowed.'
            );

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'postnl'));
            return $this;
        }

        /**
         * Creating the zip file for large logs may take a while, so disable the PHP time limit.
         */
        set_time_limit(0);

        /**
         * Get the name of the final zip file.
         */
        $zipName = 'TIG_PostNL-logs-'
                 . date('Ymd-His', Mage::getSingleton('core/date')->timestamp())
                 . '.zip';

        /**
         * Open the zip file. Overwriting the previous file if it exists.
         */
        $zip = new ZipArchive();
        $zip->open($logFolder . DS . $zipName, ZipArchive::OVERWRITE);

        /**
         * Add all the log files.
         */
        foreach ($logsWithNames as $name => $log) {
            $zip->addFile($log, $name);
        }

        /**
         * Close the zip file.
         */
        $zip->close();

        /**
         * Offer the zip file as a download response. The 'rm' key will cause Magento to remove the zip file from the
         * server after it's finished.
         */
        $content = array(
            'type'  => 'filename',
            'value' => $logFolder . DS . $zipName,
            'rm'    => true,
        );
        $this->_prepareDownloadResponse($zipName, $content);

        return $this;
    }
}