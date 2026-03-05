<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use App\Models\Link;

class PreGenerateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pre-generate-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pré-gera códigos únicos para short URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estoqueDesejado = 1000000;
        $batchSize = 5000;
        $atual = Redis::llen(Link::REDIS_STOCK_KEY);
        $faltam = $estoqueDesejado - $atual;
        $adicionados = 0;
        $this->info("Estoque atual: $atual. Abastecendo...");

        if ($faltam <= 0) {
            $this->info("Estoque já está cheio!");
            return;
        }
        
        while ($adicionados < $faltam) {
            $codigos = [];
            for ($i = 0; $i < $batchSize && ($adicionados + count($codigos)) < $faltam; $i++) {
                $codigos[] = Str::random(10);
            }

            $resultados = Redis::pipeline(function ($pipe) use ($codigos) {
                foreach ($codigos as $code) {
                    $pipe->sadd(Link::REDIS_USED_KEY, $code);
                }
            });

            $codigosUnicos = [];
            foreach ($resultados as $index => $foiAdicionado) {
                if ($foiAdicionado) {
                    $codigosUnicos[] = $codigos[$index];
                }
            }

            if (!empty($codigosUnicos)) {
                Redis::rpush(Link::REDIS_STOCK_KEY, ...$codigosUnicos);
                $adicionados += count($codigosUnicos);
            }
        }
        $this->newLine();
        $this->info("Estoque abastecido com sucesso! Total: " . Redis::llen(Link::REDIS_STOCK_KEY));
    }
}
