<?php
namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Illuminate\Support\Str;

class CustomLoggerFactory
{
    public function __invoke(array $config)
    {
        $clientName = $this->sanitizeFileName($config['clientName']);
        $moduleName = $this->sanitizeFileName($config['moduleName']);

        $log = new Logger('custom');

        $log->pushHandler(
            new RotatingFileHandler(
                storage_path("logs/custom/{$clientName}/{$moduleName}.log"),
                $config['maxFiles'] ?? 30,
                $this->level($config),
                $config['bubble'] ?? true,
                $config['permission'] ?? null,
                $config['locking'] ?? false
            )
        );

        return $log;
    }

    protected function sanitizeFileName($fileName)
    {
        return Str::slug($fileName);
    }

    protected function level(array $config)
    {
        return $config['level'] ?? 'debug';
    }
}