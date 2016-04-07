# MagentoHosting.pro Magento Extension

[Functionality](#functionality)
[Installation](#installation)
[Configuration](#installation)

## Functionality

#### MageHost_Cm_Cache_Backend_Redis
* Extended version of Cm_Cache_Backend_Redis
* Requires Cm_Cache_Backend_Redis to be installed
* Prevents the site from crashing when Redis is not responding for example because it has to remove lots of objects.
* Dispatches extra events on cache flush

#### MageHost_Cm_Cache_Backend_File
* Extended version of Cm_Cache_Backend_File
* Dispatches extra events on cache flush

#### Can pass cache flushes to other servers
* This is required in a cluster setup
* Works via SOAP API

#### Flushes minify cache
* Flushes minify cache when 'Flush JS/CSS Cache' button is pressed
* Flushes minify cache when 'block_html' cache is flushed
* Flushes minify cache when 'layout' cache is flushed
* Empties 'httpdocs/mini' directory if it exists
* Minify can be used on Nginx hosting accounts from [MagentoHosting.pro](https://magentohosting.pro)

#### Disable locking in Cm_RedisSession for Bots
* Detects robots using User-Agent header
* Speeds up page load time for GoogleBot
* Google will index more pages

## Installation
* Install [Modman](https://github.com/colinmollenhour/modman)
* `cd` to your Magento root dir
* `test -d .modman || modman init`
* `modman clone --copy --force https://github.com/magehost/MageHost-Hosting`
* If you keep your Magento code in Git: 
  * `rm -rf lib/Credis/.git`
  * Add `.modman` to your `.gitignore`

## Configuration
There is only configuration needed for the passing of flushes to other servers in a cluster setup. The configuration screen also shows some information about the extension and an error message if something is wrong.

It can be found in the Admin here:
* `System => Configuration  => ADVANCED => MagentoHosting.pro`
