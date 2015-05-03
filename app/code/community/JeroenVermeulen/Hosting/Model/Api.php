<?php

class JeroenVermeulen_Hosting_Model_Api extends Mage_Api_Model_Resource_Abstract
{

    public function cacheClean( $mode, $tags=array() ) {
Mage::log($mode);
Mage::log($tags);
        $result = false;
        Mage::register('JeroenVermeulen_cacheClean_via_Api',true);
        try {
            $result = Mage::app()->getCacheInstance()->getFrontEnd()->getBackend()->clean($mode, $tags);
//            if ( !$result ) {
                Mage::log( sprintf("%s::%s: Clean command returned '%s'.", __CLASS__, __FUNCTION__, $result) );
//                $this->_fault('command_failed', 'Clean command failed.');
//            }
        } catch (Mage_Core_Exception $e) {
            Mage::log( sprintf("%s::%s: ERROR %s", __CLASS__, __FUNCTION__, $e->getMessage()) );
            $this->_fault('command_failed', $e->getMessage());
        }
        return $result;
    }

}