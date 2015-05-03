<?php

class JeroenVermeulen_Hosting_Model_Api extends Mage_Api_Model_Resource_Abstract
{

    public function cacheClean( $mode, $tags=array() ) {
        Mage::log( sprintf('Cache Clean Received via API:  mode:%s  tags:%s',$mode,implode(',',$tags)) );
        $result = false;
        $message = '';
        Mage::register('JeroenVermeulen_cacheClean_via_Api',true);
        try {
            $result = Mage::app()->getCacheInstance()->getFrontEnd()->getBackend()->clean($mode, $tags);
//            if ( !$result ) {
            $message = sprintf("%s::%s: Clean command returned '%s'.", __CLASS__, __FUNCTION__, $result);
            Mage::log( $message );
//                $this->_fault('command_failed', 'Clean command failed.');
//            }
        } catch (Mage_Core_Exception $e) {
            $message = sprintf("%s::%s: ERROR %s", __CLASS__, __FUNCTION__, $e->getMessage());
            Mage::log( $message );
            $this->_fault('command_failed', $message);
        }
        return $message;
    }

}