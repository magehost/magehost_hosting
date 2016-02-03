<?php
/**
 * MageHost_Hosting
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category     MageHost
 * @package      MageHost_Hosting
 * @copyright    Copyright (c) 2015 MageHost BVBA (http://www.magentohosting.pro)
 */

class MageHost_Hosting_Model_Api extends Mage_Api_Model_Resource_Abstract
{

    /**
     * API Resource to call clean() on cache backend.
     *
     * @param $mode         - Cache Cleaning Mode
     * @param array $tags   - Cache Tags
     * @param $fromHostname - Server who called the API
     * @return bool         - True if successful
     * @throws Mage_Api_Exception
     */
    public function cacheClean( $mode, $tags=array(), $fromHostname ) {
        $result = false;
        /** @noinspection PhpUndefinedMethodInspection */
        $localHostname = Mage::helper('magehost_hosting')->getLocalHostname();
        Mage::log( sprintf( "Cache Clean Received from '%s' via API. Mode '%s', tags '%s'",
                            $fromHostname, $mode, implode(',',$tags) ) );
        if ( $fromHostname == $localHostname ) {
            $result = true;
            $message = sprintf("Ignoring cache clean because I am '%s'.",$localHostname);
            Mage::log( $message );
        } else {
            Mage::register('MageHost_cacheClean_via_Api',true);
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $result = Mage::app()->getCacheInstance()->getFrontEnd()->getBackend()->clean($mode, $tags);
                if ( !$result ) {
                    $message = sprintf("%s::%s: Clean command returned '%s'.", __CLASS__, __FUNCTION__, $result);
                    Mage::log( $message );
                    $this->_fault('command_failed', $message);
                }
            } catch (Mage_Core_Exception $e) {
                $message = sprintf("%s::%s: ERROR %s", __CLASS__, __FUNCTION__, $e->getMessage());
                Mage::log( $message );
                $this->_fault('command_failed', $message);
            }
        }
        return $result;
    }

}