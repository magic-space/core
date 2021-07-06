<?php

declare(strict_types=1);

namespace Stu\Component\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\ConfigInterface;

final class LoggerUtil implements LoggerUtilInterface
{

    private ConfigInterface $config;

    private $logger;

    private int $level;

    private bool $doLog;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function init(string $channel = 'stu', int $level = LoggerEnum::LEVEL_INFO): void
    {
        if ($this->checkDoLog()) {
            $this->logger = new Logger($channel);
            $this->level = $level;
            $this->logger->pushHandler(
                new StreamHandler(
                    $this->config->get('debug.logfile_path')
                ),

            );
        }
    }

    private function checkDoLog(): bool
    {
        $threshold = (int) $this->config->get('debug.loglevel');

        $this->doLog = $threshold <= $this->level;

        return $this->doLog;
    }

    public function log(string $message): void
    {
        if ($this->doLog) {
            $method = LoggerEnum::LEVEL_METHODS[$this->level];
            $this->logger->$method($message);
        }
    }
}
