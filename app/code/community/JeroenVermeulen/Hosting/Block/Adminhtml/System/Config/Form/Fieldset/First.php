<?php
/**
 * JeroenVermeulen_Hosting
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category    JeroenVermeulen
 * @package     JeroenVermeulen_Hosting
 * @copyright   Copyright (c) 2015 Jeroen Vermeulen (http://www.jeroenvermeulen.eu)
 */
/** @noinspection PhpUndefinedClassInspection */

class JeroenVermeulen_Hosting_Block_Adminhtml_System_Config_Form_Fieldset_First
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    const CONFIG_SECTION  = 'jeroenvermeulen_hosting';

    /**
     * Show explanation about Cache Backend that needs to be used
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element) {
        $result = '';
        $message = '';
        if ( Mage::getStoreConfigFlag(self::CONFIG_SECTION.'/cluster/enable_pass_cache_clean') ) {
            $currentBackEnd = get_class( Mage::app()->getCacheInstance()->getFrontEnd()->getBackend() );
            $currentBackEnd = preg_replace( '/^Zend_Cache_Backend_/','', $currentBackEnd );
            $goodBackEnds = array('JeroenVermeulen_Cm_Cache_Backend_File', 'JeroenVermeulen_Cm_Cache_Backend_Redis');
            $or = "' " . $this->__('or') . " '";
            if ( ! in_array( $currentBackEnd, $goodBackEnds ) ) {
                $message .= 'ERROR:';
                $message .= '<br />' . $this->__("This extension requires cache backend: '%s'", join($or,$goodBackEnds) );
                $message .= '<br />' . $this->__("Current setting: '%s'", $currentBackEnd);
            }
        }
        if ( !empty($message) ) {
            $result.= sprintf( '<ul class="messages"><li class="error-msg"><ul><li><span>%s</span></li></ul></li></ul>', $message );
        }
        $result .= parent::_getHeaderHtml( $element );
        return $result;
    }

}
