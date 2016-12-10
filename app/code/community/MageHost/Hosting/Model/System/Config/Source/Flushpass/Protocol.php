<?php

class MageHost_Hosting_Model_System_Config_Source_Flushpass_Protocol
{

    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'auto',
                'label' => 'Automatic',
            ),
            array(
                'value' => 'http',
                'label' => 'HTTP',
            ),
            array(
                'value' => 'https',
                'label' => 'HTTPS',
            ),
            array(
                'value' => 'http_ssloffloaded',
                'label' => 'HTTP with SSL Offloaded',
            ),
        );
    }

}