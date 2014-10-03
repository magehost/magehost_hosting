<?php

class JeroenVermeulen_Hosting_Model_Observer
{

    /**
     * @param Varien_Event_Observer $observer
     */
    public function cleanMediaCacheAfter( $observer ) {
        $miniDir = Mage::getBaseDir('base') . DIRECTORY_SEPARATOR . 'mini';
        $success = $this->clean_dir_content( $miniDir );
        if ( $success ) {
            Mage::getSingleton('adminhtml/session')->addSuccess( sprintf("Directory '%s' has been cleaned.",$miniDir) );
        } else {
            Mage::getSingleton('adminhtml/session')->addError( sprintf("Error cleaning directory '%s'.",$miniDir) );
        }
    }

    /**
     * Does not delete the dir itself, only its contents, recursive.
     * @param string $dir
     * @return bool
     * @throws Exception
     */
    protected function clean_dir_content( $dir ) {
        if ( !is_dir($dir) ) {
            return false;
        }
        $result = true;
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if ( is_dir($path) ) {
                $this->clean_dir_content( $path );
                $result = $result && rmdir( $path );
            } else {
                $result = $result && unlink( $path );
            }
        }
        return $result;
    }

}