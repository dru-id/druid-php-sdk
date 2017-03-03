# DruID PHP SDK

This repository contains the open source PHP SDK that allows you to integrate with DruID platform.

**:warning: This version is still in development.**

## Installation
The easiest way to install this library is to use Composer.
```bash
$ composer require genetsis/dru-id
```

## Usage
```php
<?php
use Genetsis\DruID\DruID;
use Genetsis\DruID\Core\Config\Beans\Config;
use Genetsis\DruID\Core\Logger\Services\VoidLogger;
use Doctrine\Common\Cache\VoidCache;

// You can use any logging options that Monolog library allows. If you don't want to 
// use any log system by default you must provide a VoidLogger instance.
$logger = new VoidLogger();

// You can use any cache options that Doctrine Cache library allows. If you don't want
// to use any cache system by default you must provide a VoidCache instance.
$cache = new VoidCache();

// Setting DruID library up. This action just configure the library but none calls will be made to DruID servers.
$druid = new DruID(new Config($server_name), file_get_contents('full/path/to/oauthconf.xml'), $logger, $cache);

// The final step is to initialize the library to synchronize with the DruID services. This should be done before 
// any interaction with the library but not necessarily now, you can delay the process until you need to use the 
// library and wherever you want to use it.
$druid->identity()->synchronizeSessionWithServer();
```

## DruID Container
If you need the library instance to be available for your entire application DruID provides you its own tool for
this purpose: DruIDContainer. Of course you are free to use any method at your fingertips for this purpose (eg: Service containers), 
but if your application doesn't allow you to perform this task we offer you the tool for it. Simply configure the library as 
described in the previous point and register it in DruIDContainer. DruID will be available wherever you need it 
with "DruIDContainer::get()"

```php
<?php 
use Genetsis\DruID\DruID;
use Genetsis\DruID\Core\Config\Beans\Config;
use Genetsis\DruID\Core\Logger\Services\VoidLogger;
use Doctrine\Common\Cache\VoidCache;

$logger = new VoidLogger();
$cache = new VoidCache();
$druid = new DruID(new Config($server_name), file_get_contents('full/path/to/oauthconf.xml'), $logger, $cache);

// We register the library in DruIDContainer.
DruIDContainer::setup($druid);

DruIDContainer::get()->identity()->synchronizeSessionWithServer();
    
```
