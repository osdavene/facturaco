<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueueWorker extends Command
{
    protected $signature   = 'queue:worker';
    protected $description = 'Inicia el worker de colas para procesamiento asincrónico de jobs';

    public function handle(): int
    {
        $this->info('Iniciando worker de colas...');

        $this->call('queue:work', [
            '--tries'   => 3,
            '--timeout' => 120,
            '--sleep'   => 3,
            '--max-jobs' => 500,   // reinicia el proceso cada 500 jobs (evita memory leaks)
        ]);

        return Command::SUCCESS;
    }
}
