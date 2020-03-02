# M2 Geo IP Module

This module has been developed for Magento 2.3.x

The purpose of this module is to obtain the geographical location (country, city, postal code, etc) of the user using the IP.
For its operation, it uses the MaxMind database (https://dev.maxmind.com/geoip/geoip2/geolite2/).

The module updates the IP's database through the Magento cron and also allows you to update it manually from the Magento backend.

In order to download the database, you must first obtain a license key: https://www.maxmind.com/en/geolite2/signup

## Installation

The extension must be installed via `composer`. To proceed, run these commands in your terminal:

```
composer require orangecat/geoip
php bin/magento module:enable Orangecat_Geoip
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Note
This module depends on GeoIP2 PHP API (https://github.com/maxmind/GeoIP2-php), if you install this module manually, install GeoIP2-php first.
composer require geoip2/geoip2:~2.0

## Screenshot
![ScreenShot](https://github.com/olivertar/m2_geoip/blob/master/screen-shot/geoip_system.png)
