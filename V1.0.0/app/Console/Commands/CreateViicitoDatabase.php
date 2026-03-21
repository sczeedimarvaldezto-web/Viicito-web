<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateViicitoDatabase extends Command
{
    protected $signature = 'viicito:setup
                            {--host=localhost : Database host}
                            {--port=3306 : Database port}
                            {--username=root : Database username}
                            {--password= : Database password}';

    protected $description = 'Create and setup the Viicito database';

    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');
        $username = $this->option('username');
        $password = $this->option('password');

        $this->info('🔧 Setting up Viicito Database...');

        try {
            // Create database
            $this->createDatabase($host, $port, $username, $password);

            // Run migrations
            $this->info('📊 Running migrations...');
            $this->call('migrate', ['--force' => true]);

            // Run seeders if needed
            $this->info('🌱 Database setup completed successfully!');
            $this->showConnectionInfo();
        } catch (\Exception $e) {
            $this->error('❌ Setup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function createDatabase($host, $port, $username, $password): void
    {
        $database = 'viicito_db';

        $connection = mysqli_connect($host, $username, $password ?: null, null, $port);

        if (!$connection) {
            throw new \Exception('Failed to connect to MySQL: ' . mysqli_connect_error());
        }

        $sql = "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        if (!mysqli_query($connection, $sql)) {
            throw new \Exception('Failed to create database: ' . mysqli_error($connection));
        }

        $this->info("✓ Database '{$database}' created/verified");
        mysqli_close($connection);
    }

    private function showConnectionInfo(): void
    {
        $this->line('');
        $this->line('📋 Connection Info:');
        $this->line('   Database: viicito_db');
        $this->line('   Host: ' . config('database.connections.mysql.host'));
        $this->line('   Port: ' . config('database.connections.mysql.port'));
        $this->line('');
        $this->line('🚀 Start the server: php artisan serve');
        $this->line('');
    }
}
