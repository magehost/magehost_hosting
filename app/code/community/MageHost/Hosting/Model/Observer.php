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

class MageHost_Hosting_Model_Observer
{
    const CONFIG_SECTION  = 'magehost_hosting';
    /** @var bool|string */
    protected $miniDir = false;

    public function __construct() {
        $this->miniDir = Mage::getBaseDir('base') . DIRECTORY_SEPARATOR . 'mini';
        if ( !is_dir($this->miniDir) ) {
            $this->miniDir = false;
        }
    }

    /**
     * Event listener to clean minified JS and CSS files in 'mini' directory.
     * This is only necessary for vhosts running on Nginx with automagic minify.
     *
     * @param Varien_Event_Observer $observer
     */
    public function cleanMediaCacheAfter( /** @noinspection PhpUnusedParameterInspection */ $observer ) {
        $this->cleanMiniDir();
    }

    /**
     * Event listener for cache backend cleans.
     * The event 'magehost_clean_backend_cache_after' is only triggered if cache backend used in local.xml:
     *    'MageHost_Cm_Cache_Backend_File'
     * or 'MageHost_Cm_Cache_Backend_Redis'
     *
     * @param Varien_Event_Observer $observer
     */
    public function magehostCleanBackendCacheAfter( $observer ) {
        if ($this->miniDir) {
            $prefix = Mage::app()->getCacheInstance()->getFrontend()->getOption('cache_id_prefix');
            if ( empty($observer->getTransport()->getTags()) ) {
                // no tags = clear everything
                $this->cleanMiniDir();
            } else {
                foreach ($observer->getTransport()->getTags() as $tag) {
                    if (0 === stripos($tag, $prefix . Mage_Core_Block_Abstract::CACHE_GROUP)) {
                        $this->cleanMiniDir();
                        break;
                    }
                }
            }
        }
        if ( Mage::getStoreConfigFlag(self::CONFIG_SECTION.'/cluster/enable_pass_cache_clean') &&
            ! Mage::registry('MageHost_cacheClean_via_Api') ) {
            $localHostname = Mage::helper('magehost_hosting')->getLocalHostname();
            /** @noinspection PhpUndefinedMethodInspection */
            $transport = $observer->getTransport();
            /** @noinspection PhpUndefinedMethodInspection */
            Mage::log( sprintf( "Cache Clean Event. Mode '%s', tags '%s'.",
                $transport->getMode(), implode(',',$transport->getTags()) ) );
            $nodes = Mage::getStoreConfig(self::CONFIG_SECTION.'/cluster/http_nodes');
            $url = '';
            // Protection against occasional crash while trying to get API url during n98-magerun usage
            if (  Mage::app()->getFrontController()->getRouter('admin') ) {
                $url = Mage::getUrl('api');
            }
            $url = str_replace('n98-magerun.phar/', '', $url); // Fix wrong URL generated via n98
            if ( empty($url) ) {
                $url = '/api/';
            }
            $urlData = parse_url($url);
            $nodeList = explode("\n",$nodes);
            $localIPs = Mage::helper('magehost_hosting')->getLocalIPs();
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
                    $client->call( $sessionId, 'magehost_hosting.cacheClean',
                        array( $transport->getMode(), $transport->getTags(), $localHostname) );
                } catch ( Exception $e ) {
                    Mage::log( sprintf("%s::%s: ERROR %s", __CLASS__, __FUNCTION__, $e->getMessage()) );
                }
            }
        }
    }

    /**
     * If you have a big site, Google crawler hits the site a lot of times in a short time period.
     * This causes lock problems with Cm_RedisSession, because all crawler hits are requesting the same session lock.
     * Cm_RedisSession provides the define CM_REDISSESSION_LOCKING_ENABLED to overrule if locking should be enabled.
     *
     * @param Varien_Event_Observer $observer
     */
    function controllerFrontInitBefore( /** @noinspection PhpUnusedParameterInspection */ Varien_Event_Observer $observer ) {
        if (Mage::helper('core')->isModuleEnabled('Cm_RedisSession')) {
            $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? false : $_SERVER['HTTP_USER_AGENT'];
            $isBot = ( !$userAgent || preg_match(Cm_RedisSession_Model_Session::BOT_REGEX, $userAgent) );
            if ($isBot) {
                define('CM_REDISSESSION_LOCKING_ENABLED', false);
            }
        }
    }

    protected function cleanMiniDir() {
        if ( $this->miniDir ) {
            $success = $this->clean_dir_content( $this->miniDir );
            /** @var Mage_Adminhtml_Model_Session $adminSession */
            if ( $success ) {
                Mage::helper('magehost_hosting')->successMessage( sprintf("Directory '%s' has been cleaned.",$this->miniDir) );
            } else {
                Mage::helper('magehost_hosting')->errorMessage( sprintf("Error cleaning directory '%s'.",$this->miniDir) );
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