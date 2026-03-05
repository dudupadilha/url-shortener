<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Link;

class SyncLinkClicks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-link-clicks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza os contadores de cliques do Redis para o MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cliquesNoRedis = Redis::hgetall('link_clicks');

        if (empty($cliquesNoRedis)) {
            $this->info("Nenhum clique para sincronizar.");
            return;
        }

        $totalSincronizado = 0;
        $this->info("Iniciando sincronização de " . count($cliquesNoRedis) . " links...");

        foreach ($cliquesNoRedis as $code => $count) {
            $updated = Link::where('short_url', $code)->increment('click_count', $count);
            if ($updated) {
                Redis::hdel('link_clicks', $code);
                $totalSincronizado++;
            }
        }

        $this->info("Sucesso: $totalSincronizado links atualizados no MySQL.");
    }
}
