<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Link;

class CleanExpiredLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;

        Link::where('expires_at', '<', now())->chunkById(1000, function ($links) use (&$count) {
        $shortUrls = $links->pluck('short_url')->toArray();
        $ids = $links->pluck('id')->toArray();

        $cacheKeys = array_map(fn($code) => "link:{$code}", $shortUrls);

        Redis::del(...$cacheKeys);
        Redis::srem(Link::REDIS_USED_KEY, ...$shortUrls);
        Redis::hdel('link_clicks', ...$shortUrls);
        Link::whereIn('id', $ids)->delete();

        $count += count($ids);
        $this->info("Lote de " . count($ids) . " links processado...");
    });

    $this->info("Total de links expirados removidos: $count");
    }
}
