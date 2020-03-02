<?php

namespace Orangecat\Geoip\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Orangecat\Geoip\Model\GeoIpDatabase\MaxMind;
use Orangecat\Geoip\Helper\Data;

class MaxMindInfo extends Field
{
    /**
     * @var _dir
     */
    protected $_dir;

    /**
     * @var maxMind
     */
    protected $maxMind;

    /**
     * @var helper
     */
    protected $helper;

    /**
     * MaxMindInfo constructor.
     * @param Context $context
     * @param DirectoryList $dir
     * @param MaxMind $maxMind
     * @param array $data
     */
    public function __construct(
        Context $context,
        DirectoryList $dir,
        MaxMind $maxMind,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dir = $dir;
        $this->maxMind = $maxMind;
        $this->helper = $helper;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws FileSystemException
     */
    public function render(AbstractElement $element)
    {
        //$dirList = $this->_dir->getPath('var'). '/geoipdb/GeoLite2-Country.mmdb';
        $dirList = $this->_dir->getPath('var') . '/geoipdb/GeoLite2-City.mmdb';
        $licensekey = $this->helper->getConfigGeneral('licensekey');

        if (file_exists($dirList)) {
            $modified = date("F d, Y.", filemtime($dirList));
        } elseif (!empty($licensekey)) {
            $modified = __('Verify license key.');
        } elseif ($this->maxMind->update()) {
            $modified = date("F d, Y.", filemtime($dirList));
        } else {
            $modified = __('Can not download DB. Verify the license.');
        }

        $html = '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
        This GeoIP extension includes GeoLite2 data created by MaxMind, available from
        <a target="_blank" rel="nofollow  noopener" href="https://www.maxmind.com">https://www.maxmind.com</a>.<br/>
        Last GeoIP Data Base Update: <strong>' . $modified . '</strong>
        </div>';

        return $html;
    }
}
