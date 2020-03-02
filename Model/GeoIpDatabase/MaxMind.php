<?php

namespace Orangecat\Geoip\Model\GeoIpDatabase;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Orangecat\Geoip\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class MaxMind
 * @package Orangecat\Geoip\Model\GeoIpDatabase
 */
class MaxMind
{
    /**
     * @var DirectoryList
     */
    protected $_dir;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_file;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var helper
     */
    protected $helper;

    /**
     * MaxMind constructor.
     * @param DirectoryList $dir
     * @param File $file
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $dir,
        File $file,
        Filesystem $filesystem,
        LoggerInterface $logger,
        Data $helper
    ) {
        $this->_dir = $dir;
        $this->_file = $file;
        $this->filesystem = $filesystem;
        $this->_logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @return bool
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function createDir($dirPath)
    {
        $ioAdapter = $this->_file;
        if (!is_dir($dirPath)) {
            if (!$ioAdapter->mkdir($dirPath, 0775)) {
                throw new LocalizedException(__('Can not create folder' . $dirPath));
            }
        }
        return true;
    }

    /**
     * @return bool
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function update()
    {
        $dbPath = $this->_dir->getPath('var') . '/geoipdb';
        $this->createDir($dbPath);

        $tpl_url = $this->helper->getDownloadPath();
        $licensekey = trim($this->helper->getConfigGeneral('licensekey'));

        if (!empty($licensekey)) {
            $url = str_replace("YOUR_LICENSE_KEY", $licensekey, $tpl_url);
        } else {
            return false;
        }

        // get db tar gz from maxmind
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if (!$result) {
            throw new LocalizedException(__('Can not download file GeoLite2-City.mmdb'));
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            throw new LocalizedException(_('Fail download file. Http code: %1', $http_code));
        }
        curl_close($ch);

        // write maxmind tar gz file to filesystem
        $tar_filename = $dbPath . '/' . 'temp.tar.gz';
        $fp = fopen($tar_filename, 'w');
        if (!fwrite($fp, $result)) {
            //throw new LocalizedException(__('Can not save or overwrite file GeoLite2-Country.mmdb'));
            throw new LocalizedException(__('Can not save or overwrite file GeoLite2-City.mmdb'));
        }
        fclose($fp);

        stream_wrapper_restore('phar');
        $phar = new \PharData($tar_filename);

        // uncompress maxmind tar gz database, move maxmind database and clean directory
        if ($phar->current()->isDir()) {
            $dir = $phar->current()->getPathname();
            $dirname = substr($dir, strrpos($dir, '/') + 1);
            if (!$phar->extractTo($dbPath, $dirname . '/' . 'GeoLite2-City.mmdb', true)) {
                throw new LocalizedException(__('Can not save or overwrite file GeoLite2-City.mmdb'));
            }
            if (!$this->_file->cp($dbPath . '/' . $dirname . '/' . 'GeoLite2-City.mmdb', $dbPath . '/' . 'GeoLite2-City.mmdb')) {
                $this->_file->rm($tar_filename);
                throw new LocalizedException(__('Can not save or overwrite file GeoLite2-City.mmdb'));
            }
            $this->_file->rm($tar_filename);
            $this->_file->rmdir($dbPath . '/' . $dirname, true);
        }

        return true;
    }
}
