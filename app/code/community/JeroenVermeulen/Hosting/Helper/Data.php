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
        $hostname = shell_exec('hostname -f 2>/dev/null');
        if ( empty($hostname) ) {
            $hostname = shell_exec('hostname -s 2>/dev/null');
        }
        if ( empty($hostname) ) {
            $hostname = reset( $this->getLocalIPs() );
        }
        return trim($hostname);
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
                $result[$matches[1]] = 1;
            }
        }
        unset( $result['127.0.0.1'] );
        unset( $result['::1'] );
        return array_keys($result);
    }
}