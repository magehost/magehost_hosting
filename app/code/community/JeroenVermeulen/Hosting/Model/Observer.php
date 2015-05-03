<?php
/**
 * JeroenVermeulen_Hosting
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category     JeroenVermeulen
 * @package      JeroenVermeulen_Hosting
 * @copyright    Copyright (c) 2015 Jeroen Vermeulen (http://www.jeroenvermeulen.eu)
 */

class JeroenVermeulen_Hosting_Model_Observer
{
    const CONFIG_SECTION  = 'jeroenvermeulen_hosting';

    /**
     * Event listener to clean minified JS and CSS files in 'mini' directory.
     * This is only necessary for vhosts running on Nginx with automagic minify.
     *
     * @param Varien_Event_Observer $observer
     */
    public function cleanMediaCacheAfter( /** @noinspection PhpUnusedParameterInspection */ $observer ) {
        $miniDir = Mage::getBaseDir('base') . DIRECTORY_SEPARATOR . 'mini';
        if ( is_dir($miniDir) ) {
            $success = $this->clean_dir_content( $miniDir );
            /** @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');
            if ( $success ) {
                $adminSession->addSuccess( sprintf("Directory '%s' has been cleaned.",$miniDir) );
            } else {
                $adminSession->addError( sprintf("Error cleaning directory '%s'.",$miniDir) );
            }
        }
    }

    /**
     * Event listener for cache backend cleans.
     * The event 'jv_clean_backend_cache' can only be triggered if cache backend used in local.xml:
     *    'JeroenVermeulen_Cm_Cache_Backend_File'
     * or 'JeroenVermeulen_Cm_Cache_Backend_Redis'
     *
     * @param Varien_Event_Observer $observer
     */
    public function jvCleanBackendCache( $observer ) {
        if ( Mage::getStoreConfigFlag(self::CONFIG_SECTION.'/cluster/enable_pass_cache_clean') &&
             ! Mage::registry('JeroenVermeulen_cacheClean_via_Api') ) {
            $localHostname = Mage::helper('jeroenvermeulen_hosting')->getLocalHostname();
            /** @noinspection PhpUndefinedMethodInspection */
            $transport = $observer->getTransport();
            /** @noinspection PhpUndefinedMethodInspection */
            Mage::log( sprintf( "Cache Clean Event. Mode '%s', tags '%s'.",
                                $transport->getMode(), implode(',',$transport->getTags()) ) );
            $nodes = Mage::getStoreConfig(self::CONFIG_SECTION.'/cluster/http_nodes');
            $url = Mage::getUrl('api');
            $url = str_replace('n98-magerun.phar/', '', $url); // Fix wrong URL generated via n98
            $urlData = parse_url($url);
            $nodeList = explode("\n",$nodes);
            $localIPs = Mage::helper('jeroenvermeulen_hosting')->getLocalIPs();
            foreach ( $nodeList as $node ) {
                $node = trim($node);
                $nodeSplit = explode(':',$node);
                $nodeHost = $nodeSplit[0];
                $nodePort = (empty($nodeSplit[1])) ? 80 : intval($nodeSplit[1]);
                $nodeIP =  gethostbyname( $nodeHost );
                if ( $nodeHost == $localHostname || in_array($nodeIP,$localIPs) ) {
                    continue;
                }
                $headers = array();
                $headers[] = 'Host: '.$urlData['host'];
                $nodeScheme = $urlData['scheme'];
                if ( 443 == $nodePort && 'http' == $nodeScheme ) {
                    $nodeScheme = 'https';
                    $headers[] = 'X-Forwarded-Proto: http';
                }
                elseif ( 80 == $nodePort && 'https' == $nodeScheme ) {
                    $nodeScheme = 'http';
                    $headers[] = 'X-Forwarded-Proto: https';
                    $headers[] = 'Ssl-Offloaded: 1';
                }
                $nodeLocation = $nodeScheme.'://'.$node.$urlData['path'];
                Mage::log( sprintf("%s::%s: Passing flush to %s", __CLASS__, __FUNCTION__, $nodeLocation) );
                try {
                    $client = new Zend_Soap_Client(null);
                    $client->setLocation($nodeLocation);
                    $client->setUri($nodeLocation);
                    $client->setStreamContext( stream_context_create( array(
                            'ssl'  => array( 'verify_peer'          => false,
                                             'allow_self_signed'    => true ),
                            'http' => array( 'header'               => implode("\n",$headers),
                                             'follow_location'      => 0 )
                    ) ) );
                    $apiUser = Mage::getStoreConfig(self::CONFIG_SECTION.'/cluster/api_user');
                    $apiKey  = Mage::getStoreConfig(self::CONFIG_SECTION.'/cluster/api_key');
                    /** @noinspection PhpUndefinedMethodInspection */
                    $sessionId =  $client->login( $apiUser, $apiKey );
                    /** @noinspection PhpUndefinedMethodInspection */
                    $client->call( $sessionId, 'jvhosting.cacheClean',
                                   array( $transport->getMode(), $transport->getTags(), $localHostname) );
                } catch ( Exception $e ) {
                    Mage::log( sprintf("%s::%s: ERROR %s", __CLASS__, __FUNCTION__, $e->getMessage()) );
                }
            }
        }
    }

    /**
     * Does not delete the dir itself, only its contents, recursive.
     *
     * @param string $dir
     * @return bool
     * @throws Exception
     */
    protected function clean_dir_content( $dir )
    {
        if (!is_dir( $dir )) {
            return false;
        }
        $result = true;
        foreach (scandir( $dir ) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir( $path )) {
                $this->clean_dir_content( $path );
                $result = $result && rmdir( $path );
            } else {
                $result = $result && unlink( $path );
            }
        }
        return $result;
    }

}