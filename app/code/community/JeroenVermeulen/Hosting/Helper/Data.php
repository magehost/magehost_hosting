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

class JeroenVermeulen_Hosting_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Get local hostname of this server
     *
     * @return string - Local hostname
     */
    public function getLocalHostname() {
        return trim(shell_exec('hostname -f'));
    }

    /**
     * Get the local IPs of a Linux, FreeBSD or Mac server
     *
     * @return array - Local IPs
     */
    public function getLocalIPs() {
        $result = $this->readIPs('ip addr');
        if ( empty($result) ) {
            $result = $this->readIPs('ifconfig -a');
        }
        return $result;
    }

    /**
     * Execute shell command to receive IPs and parse the output
     *
     * @param $cmd   - can be 'ip addr' or 'ifconfig -a'
     * @return array - IP numbers
     */
    protected function readIPs( $cmd ) {
        $result = array();
        $lines = explode( "\n", trim(shell_exec($cmd.' 2>/dev/null')) );
        foreach( $lines as $line ) {
            $matches = array();
            if ( preg_match('|inet6?\s+(?:addr\:\s*)?([\:\.\w]+)|',$line,$matches) ) {
                $result[] = $matches[1];
            }
        }
        $result = array_unique( $result );
        return $result;
    }
}