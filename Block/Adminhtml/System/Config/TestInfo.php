<?php

namespace Orangecat\Geoip\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\FileSystemException;

class TestInfo extends Field
{
    /**
     * MaxMindInfo constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws FileSystemException
     */
    public function render(AbstractElement $element)
    {

        $html = '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
        For some implementations, you can simulate an IP by adding the "fakeIp" parameter to your URL.<br/>
        Example: https://your.domain.com?fakeIp=162.254.206.227<br/>
        When you work in a local development environment and need to simulate an IP, we recommend using the field below.<br/>
        <strong>Warning:</strong> if you do it in production environment, that IP will be valid for all visits.
        </div>';

        return $html;
    }
}
