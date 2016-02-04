# MagentoHosting.pro Magento Extension

### MageHost_Cm_Cache_Backend_Redis
* Extended version of Cm_Cache_Backend_Redis
* Requires Cm_Cache_Backend_Redis to be installed
* Prevents the site from crashing when Redis is not responding for example because it has to remove lots of objects.
* Dispatches extra events on cache flush

### MageHost_Cm_Cache_Backend_File
* Extended version of Cm_Cache_Backend_File
* Dispatches extra events on cache flush

### Can pass cache flushes to other servers
* This is required in a cluster setup
* Works via SOAP API

### Flushes minify cache
* Flushes minify cache when 'Flush JS/CSS Cache' button is pressed
* Flushes minify cache when 'block_html' cache is flushed
* Empties 'httpdocs/mini' directory if it exists
* Minify can be used on Nginx hosting accounts from [MagentoHosting.pro](https://magentohosting.pro)