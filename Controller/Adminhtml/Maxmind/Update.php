<?php

namespace Orangecat\Geoip\Controller\Adminhtml\Maxmind;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Orangecat\Geoip\Model\GeoIpDatabase\MaxMind;

/**
 * Class Update
 * @package Orangecat\Geoip\Controller\Adminhtml\Maxmind\Update
 */
class Update extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Orangecat_Geoip::config';

    /**
     * @var \Magefan\GeoIp\Model\GeoIpDatabase\MaxMind
     */
    protected $maxMind;

    /**
     * Update constructor.
     * @param Context $context
     * @param MaxMind $maxMind
     */
    public function __construct(
        Context $context,
        MaxMind $maxMind
    ) {
        parent::__construct($context);
        $this->maxMind = $maxMind;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->maxMind->update()) {
            $this->messageManager->addSuccessMessage('MaxMind GeoIP Database has been updated successfully.');
        } else {
            $this->messageManager->addErrorMessage('Something went wrong while updating the GeoIP database.');
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
