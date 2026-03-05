<?php

namespace App\Jobs;

use App\Models\Link;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLinkCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $code;

    public function __construct($url, $code)
    {
        $this->url = $url;
        $this->code = $code;
    }

    public function handle()
    {
        Link::create([
            'complete_url' => $this->url,
            'short_url' => $this->code,
            'expires_at' => now()->addMonths(3),
        ]);
    }
}
