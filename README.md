# MagentoHosting.pro Magento Extension

### MageHost_Cm_Cache_Backend_Redis
* Extended version of Cm_Cache_Backend_Redis
* Requires Cm_Cache_Backend_Redis to be installed
* Prevents the site from crashing when Redis is not responding for example because it has to remove lots of objects.
* Fires extra events

### MageHost_Cm_Cache_Backend_File
* Extended version of Cm_Cache_Backend_File
* Fires extra events

### Can pass cache flushes to other servers
* This is required in a cluster setup
* Works via SOAP API

### Flushes minify cache when 'Flush JS/CSS' button is pressed
* Empties 'httpdocs/minify' directory if it exists
* Minify can be used on Nginx hosting accounts from [MagentoHosting.pro](https://magentohosting.pro)