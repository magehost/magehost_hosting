<?php

class JeroenVermeulen_Hosting_Model_Api extends Mage_Api_Model_Resource_Abstract
{

    public function cacheClean( $mode, $tags=array(), $fromHostname ) {
        $localHostname = Mage::helper('jeroenvermeulen_hosting')->getLocalHostname();
        Mage::log( sprintf( "Cache Clean Received from '%s' via API. Mode '%s', tags '%s'",
                            $fromHostname, $mode, implode(',',$tags) ) );
        if ( $fromHostname == $localHostname ) {
            $message = sprintf("Ignoring cache clean because I am '%s'.",$localHostname);
            Mage::log( $message );
        } else {
            Mage::register('JeroenVermeulen_cacheClean_via_Api',true);
            try {
                $result = Mage::app()->getCacheInstance()->getFrontEnd()->getBackend()->clean($mode, $tags);
                $message = sprintf("%s::%s: Clean command returned '%s'.", __CLASS__, __FUNCTION__, $result);
                Mage::log( $message );
            } catch (Mage_Core_Exception $e) {
                $message = sprintf("%s::%s: ERROR %s", __CLASS__, __FUNCTION__, $e->getMessage());
                Mage::log( $message );
                $this->_fault('command_failed', $message);
            }
        }
        return $message;
    }

}