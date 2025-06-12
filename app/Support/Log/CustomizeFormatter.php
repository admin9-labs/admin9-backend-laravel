<?php

namespace App\Support\Log;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $dateFormat = 'Y-m-d H:i:s.v';
            $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
            $formatter = new LineFormatter($output, $dateFormat, true, true);
            $handler->setFormatter($formatter);
        }
    }
}
