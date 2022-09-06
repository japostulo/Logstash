<?php

namespace HAOC\Logstash;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use HAOC\Logstash\UsageFormatter;
use Monolog\Handler\SocketHandler;

class LogstashLogger
{
    /**
     * @param array $config
     * @return LoggerInterface
     */

    public function __invoke(array $config): LoggerInterface
    {
        $handler = new SocketHandler("tcp://{$config['host']}:{$config['port']}");
        $handler->setFormatter(new UsageFormatter(config('app.name'), gethostname()));
        return new Logger('logstash', [$handler]);
    }
}
