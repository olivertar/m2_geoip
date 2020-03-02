<?php

namespace Orangecat\Geoip\Helper;

use Magento\Directory\Model\Region;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const CONFIG_MODULE_PATH = 'geoip';

    protected $_directoryList;

    protected $_localeResolver;

    protected $_regionModel;

    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        Resolver $localeResolver,
        Region $regionModel
    ) {
        $this->_directoryList  = $directoryList;
        $this->_localeResolver = $localeResolver;
        $this->_regionModel    = $regionModel;

        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConfigGeneral($field, $storeId = null)
    {
        $field = self::CONFIG_MODULE_PATH . '/' . 'general' . '/' . $field;
        return $this->getConfigValue($field, $storeId);
    }

    public function getDownloadPath($store = null)
    {
        return $this->getConfigGeneral('downloadurl', $store);
    }

    public function isEnabled($store = null)
    {
        return $this->getConfigGeneral('enabled', $store);
    }

    /***************************************** Maxmind Db GeoIp ******************************************************/

    public function checkHasLibrary()
    {
        $path = $this->_directoryList->getPath('var') . '/geoipdb';
        if (!file_exists($path)) {
            return false;
        }

        //$folder   = scandir($path, true);
        //$pathFile = $path . '/' . $folder[0] . '/GeoLite2-City.mmdb';
        $pathFile = $path . '/GeoLite2-City.mmdb';
        if (!file_exists($pathFile)) {
            return false;
        }

        return $pathFile;
    }

    public function getGeoIpData($ip = null, $storeId = null)
    {
        if (empty($ip)) {
            $ip = $this->getIpAddress();
        }
        try {
            $libPath = $this->checkHasLibrary();
            if ($this->isEnabled($storeId) && $libPath && class_exists('GeoIp2\Database\Reader')) {
                $geoIp  = new \GeoIp2\Database\Reader($libPath, $this->getLocales());
                $record = $geoIp->city($ip);

                $geoIpData = [
                    'city'       => $record->city->name,
                    'country_id' => $record->country->isoCode,
                    'country_name' => $record->country->name,
                    'postcode'   => $record->postal->code
                ];

                if ($record->mostSpecificSubdivision) {
                    $code = $record->mostSpecificSubdivision->isoCode;
                    if ($regionId = $this->_regionModel->loadByCode($code, $record->country->isoCode)->getId()) {
                        $geoIpData['region_id'] = $regionId;
                    } else {
                        $geoIpData['region'] = $record->mostSpecificSubdivision->name;
                    }
                }
            } else {
                $geoIpData = [];
            }
        } catch (\Exception $e) {
            // No Ip found in database
            $geoIpData = [];
        }

        return $geoIpData;
    }

    public function getIpAddress()
    {
        $fakeIP = $this->_request->getParam('fakeIp', false);
        if ($fakeIP) {
            return $fakeIP;
        }

        $fackeip = trim($this->helper->getConfigValue(self::CONFIG_MODULE_PATH . '/test/fakeip'));
        if (!empty($fackeip)) {
            return $fackeip;
        }

        $server = $this->_getRequest()->getServer();

        $ip = $server['REMOTE_ADDR'];
        if (!empty($server['HTTP_CLIENT_IP'])) {
            $ip = $server['HTTP_CLIENT_IP'];
        } elseif (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $server['HTTP_X_FORWARDED_FOR'];
        }

        $ipArr = explode(',', $ip);
        $ip    = $ipArr[count($ipArr) - 1];

        return trim($ip);
    }

    protected function getLocales()
    {
        $language = substr($this->_localeResolver->getLocale(), 0, 2) ?: 'en';

        $locales = [$language];
        if ($language !== 'en') {
            $locales[] = 'en';
        }

        return $locales;
    }
}
