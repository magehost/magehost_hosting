#!/usr/bin/env php
<?php
$tryRoot = array( '.' );
$tryRoot[] = dirname(__DIR__);
if ( !empty($_SERVER['PWD']) ) {
    $tryRoot[] = $_SERVER['PWD'];
    $tryRoot[] = dirname($_SERVER['PWD']);
}
if ( !empty($_SERVER['HOME']) ) {
    $tryRoot[] = $_SERVER['HOME'] . '/httpdocs';
    $tryRoot[] = $_SERVER['HOME'] . '/public_html';
    $tryRoot[] = $_SERVER['HOME'] . '/htdocs';
}

$mageRoot = false;
foreach( $tryRoot as $try ) {
    $try = rtrim( $try, '/' );
    if ( file_exists($try.'/lib/Credis/Client.php') ) {
        $mageRoot = $try;
    }
}
if ( empty($mageRoot) ) {
    die( 'ERROR: Could not find Magento root dir.' );
}

/** @noinspection PhpIncludeInspection */
require $mageRoot . '/lib/Credis/Client.php';

$client = Redis_Connect( $mageRoot . '/app/etc/local.xml' );
$client->flushdb();

exit;

function Redis_Connect( $xmlFile )
{
    if ( !is_readable( $xmlFile ) )
    {
        throw new Exception( sprintf('File "%s" does not exits or is not readable.', $xmlFile ) );
    }

    $xml  = simplexml_load_file( $xmlFile, 'SimpleXMLElement', LIBXML_NOCDATA );
    /** @noinspection PhpUndefinedFieldInspection */
    $host = strval( $xml->global->cache->backend_options->server );
    /** @noinspection PhpUndefinedFieldInspection */
    $port = strval( $xml->global->cache->backend_options->port );
    /** @noinspection PhpUndefinedFieldInspection */
    $db   = strval( $xml->global->cache->backend_options->database );
    if ( empty( $host ) )
    {
        throw new Exception( sprintf('Redis server hostname is not found in "%s".', $xmlFile ) );
    }
    if ( empty( $port ) )
    {
        throw new Exception( sprintf('Redis server port is not found in "%s".', $xmlFile ) );
    }
    if ( !strlen( $db ) )
    {
        throw new Exception( sprintf('Redis database number is not found in "%s".', $xmlFile ) );
    }

    if ( '/' == substr($host,0,1) )
    {
        // Socket
        $server = $host;
    }
    else
    {
        // TCP
        $server = sprintf( 'tcp://%s:%d', $host, $port );
    }

    $client = new Credis_Client( $server );
    $client->select( $db );

    return $client;
}

