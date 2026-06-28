<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'finance:backup';

    protected $description = 'Crea un backup local de PostgreSQL.';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $file = $dir.'/finanzas-'.now()->format('Ymd-His').'.sql';
        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -f %s',
            escapeshellarg((string) config('database.connections.pgsql.password')),
            escapeshellarg((string) config('database.connections.pgsql.host')),
            escapeshellarg((string) config('database.connections.pgsql.port')),
            escapeshellarg((string) config('database.connections.pgsql.username')),
            escapeshellarg((string) config('database.connections.pgsql.database')),
            escapeshellarg($file),
        );

        passthru($command, $code);
        $this->info($code === 0 ? "Backup creado: {$file}" : 'No se pudo crear el backup.');

        return $code;
    }
}
