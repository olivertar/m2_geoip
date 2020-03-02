<?php

namespace Orangecat\Geoip\Cron;

use Exception;
use Orangecat\Geoip\Model\GeoIpDatabase\MaxMind;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateMaxMind
 * @package Orangecat\Geoip\Cron
 */
class UpdateMaxMind
{
    /**
     * @var MaxMind
     */
    protected $maxMind;
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * UpdateMaxMind constructor.
     * @param MaxMind $maxMind
     * @param LoggerInterface $logger
     */
    public function __construct(
        MaxMind $maxMind,
        LoggerInterface $logger
    ) {
        $this->maxMind = $maxMind;
        $this->_logger = $logger;
    }

    /**
     * Execute Cron UpdateMaxMind
     */
    public function execute()
    {
        try {
            $this->maxMind->update();
        } catch (Exception $e) {
            $this->_logger->debug($e->getMessage());
            return false;
        }
        return true;
    }
}
