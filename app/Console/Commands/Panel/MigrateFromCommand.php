<?php

namespace App\Console\Commands\Panel;

use App\Enums\WingsType;
use App\Models\Node;
use App\Traits\EnvironmentWriterTrait;
use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves an existing Pterodactyl or Pelican panel onto Shoebill: same database, upgraded in place.
 * Pelican's migrations already convert a Pterodactyl schema; this wraps them with the steps people forget.
 */
class MigrateFromCommand extends Command
{
    use EnvironmentWriterTrait;

    /** Settings copied from the old panel's .env. APP_KEY decrypts stored secrets, so it must move with the database. */
    public const COPIED_KEYS = ['APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];

    protected $signature = 'shoebill:migrate-from
        {--env= : Path to the old panel\'s .env file (e.g. /var/www/pterodactyl/.env); its APP_KEY and DB_* settings are copied}
        {--force : Do not ask for confirmation}';

    protected $description = 'Move an existing Pterodactyl or Pelican panel to Shoebill, keeping users, servers, nodes and Wings.';

    public function handle(): int
    {
        if ($path = $this->option('env')) {
            if (!is_readable($path)) {
                $this->error("Cannot read $path.");

                return self::FAILURE;
            }

            $old = array_intersect_key(Dotenv::parse(file_get_contents($path)), array_flip(self::COPIED_KEYS));
            $this->writeToEnvironment($old);
            config(['app.key' => $old['APP_KEY'] ?? config('app.key')]);
            DB::purge();
            $this->info('Copied ' . implode(', ', array_keys($old)) . ' from ' . $path . '.');
        }

        $source = $this->detectSource();
        $this->info("Detected: $source");

        if ($source === 'empty') {
            $this->error('The database has no panel tables. Point DB_* at the old panel\'s database (or pass --env) and run this again.');

            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Back up the database first. Upgrade it to Shoebill now?')) {
            return self::FAILURE;
        }

        $this->call('migrate', ['--force' => true]);

        if ($source === 'Pterodactyl') {
            // The nodes still run Pterodactyl Wings, which keeps working; switch a node to Pelican Wings later if you upgrade it.
            $count = Node::query()->update(['daemon_type' => WingsType::Pterodactyl->value]);
            $this->info("Marked $count node(s) as running Pterodactyl Wings.");
        }

        Artisan::call('optimize:clear');

        $this->newLine();
        $this->line('Next steps:');
        $this->line(' 1. Point your web server at this Shoebill install (or swap the folders) and keep the same APP_URL.');
        $this->line(' 2. Restart the queue worker and make sure the scheduler (php artisan schedule:run) runs every minute.');
        $this->line($source === 'Pelican'
            ? ' 3. Copy storage/app/public and plugins/ from the old Pelican install if you used avatars, icons or plugins.'
            : ' 3. Wings needs no changes. Users log in with their existing accounts and passwords.');

        return self::SUCCESS;
    }

    private function detectSource(): string
    {
        return match (true) {
            // Pelican's migrations remove nests, so only an unconverted Pterodactyl database still has it.
            Schema::hasTable('nests') => 'Pterodactyl',
            Schema::hasTable('server_stat_samples') => 'Shoebill',
            Schema::hasTable('eggs') => 'Pelican',
            default => 'empty',
        };
    }
}
