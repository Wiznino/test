<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('database:backup')]
#[Description('Create a retained SQLite snapshot in private local storage')]
class BackupSqliteDatabase extends Command
{
    public function handle(): int
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'sqlite') {
            $this->error('This backup command supports SQLite. Configure database backups with your hosting provider for other database engines.');

            return self::FAILURE;
        }

        $databasePath = $connection->getDatabaseName();

        if ($databasePath === ':memory:' || ! is_file($databasePath)) {
            $this->error('The configured SQLite database file could not be found.');

            return self::FAILURE;
        }

        $disk = Storage::disk('local');
        $directory = 'database-backups';
        $disk->makeDirectory($directory);

        $backupRelativePath = $directory.'/atu-eats-'.now()->format('Ymd-His-u').'.sqlite';
        $quotedBackupPath = $connection->getPdo()->quote($disk->path($backupRelativePath));

        if (! is_string($quotedBackupPath)) {
            $this->error('The backup destination path could not be prepared.');

            return self::FAILURE;
        }

        $connection->statement('VACUUM INTO '.$quotedBackupPath);

        $retentionCutoff = now()->subDays(14)->timestamp;

        foreach ($disk->files($directory) as $path) {
            if (Str::endsWith($path, '.sqlite') && $disk->lastModified($path) < $retentionCutoff) {
                $disk->delete($path);
            }
        }

        $this->info('SQLite backup created: '.$backupRelativePath);

        return self::SUCCESS;
    }
}
