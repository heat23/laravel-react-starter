<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateIndexNowKey extends Command
{
    protected $signature = 'indexnow:generate-key';

    protected $description = 'Generate a random IndexNow API key (32-char hex). Paste it into .env as INDEXNOW_API_KEY.';

    public function handle(): int
    {
        $key = bin2hex(random_bytes(16));

        $this->line('');
        $this->info('Generated IndexNow key:');
        $this->line('');
        $this->line('  '.$key);
        $this->line('');
        $this->comment('Add to your .env file:');
        $this->line('');
        $this->line('  INDEXNOW_API_KEY='.$key);
        $this->line('  FEATURE_INDEXNOW=true');
        $this->line('');
        $this->comment('The key will be served at '.rtrim((string) config('app.url'), '/').'/'.$key.'.txt');
        $this->line('');

        return self::SUCCESS;
    }
}
