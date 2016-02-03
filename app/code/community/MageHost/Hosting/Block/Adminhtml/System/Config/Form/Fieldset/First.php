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
 * @copyright    Copyright (c) 2016 MageHost BVBA (http://www.magentohosting.pro)
 */
/** @noinspection PhpUndefinedClassInspection */

class MageHost_Hosting_Block_Adminhtml_System_Config_Form_Fieldset_First
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    const CONFIG_SECTION  = 'magehost_hosting';

    /**
     * Show explanation about Cache Backend that needs to be used
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element) {
        $result = '';
        $goodBackEnds = array();
        $currentBackEnd = get_class( Mage::app()->getCacheInstance()->getFrontEnd()->getBackend() );
        $currentBackEnd = preg_replace( '/^Zend_Cache_Backend_/','', $currentBackEnd );
        $message = '';
        $dependClasses = array('Cm_Cache_Backend_File', 'Cm_Cache_Backend_Redis');
        $optionClasses = array();
        $or = "' " . $this->__('or') . " '";
        foreach( $dependClasses as $dependClass ) {
            $ourClass = 'MageHost_' . $dependClass;
            if ( mageFindClassFile($dependClass) ) {
                $goodBackEnds[] = $ourClass;
            } else {
                $optionClasses[$dependClass] = $ourClass;
            }
        }
        if ( empty($goodBackEnds) ) {
            $message .= 'ERROR:';
            $message .= '<br />' . $this->__("This extension requires one of these classes to exist: '%s'", join($or,$dependClasses));
        } elseif ( ! in_array( $currentBackEnd, $goodBackEnds ) ) {
            $message .= 'ERROR:';
            $message .= '<br />' . $this->__("This extension requires cache backend: '%s'", join($or,$goodBackEnds) );
            $message .= '<br />' . $this->__("Current setting: '%s'", $currentBackEnd);
            $message .= '<br />';
            foreach( $optionClasses as $dependClass => $ourClass ) {
                $message .= '<br />' . $this->__("If you would install '%s' you could also use '%s'.", $dependClass, $ourClass );
            }
        }
        if ( !empty($message) ) {
            $result.= sprintf( '<ul class="messages"><li class="error-msg"><ul><li><span>%s</span></li></ul></li></ul>', $message );
        }
        $result .= parent::_getHeaderHtml( $element );
        return $result;
    }

}
