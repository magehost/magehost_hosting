<?php
 
class JeroenVermeulen_Hosting_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @return string
     */
    public function getLocalHostname() {
        return trim(shell_exec('hostname -f'));
    }

    public function getLocalIPs() {
        $result = $this->readIPs('ip addr');
        if ( empty($result) ) {
            $result = $this->readIPs('ifconfig -a');
        }
        $result = array_unique( $result );
        return $result;
    }

    protected function readIPs( $cmd ) {
        $result = array();
        $lines = explode( "\n", trim(shell_exec($cmd)) );
        foreach( $lines as $line ) {
            $matches = array();
            if ( preg_match('|inet6?\s+(?:addr\:\s*)?([\:\.\w]+)|',$line,$matches) ) {
                $result[] = $matches[1];
            }
        }
        var_dump($cmd);
        var_dump($result);
        return $result;
    }
}