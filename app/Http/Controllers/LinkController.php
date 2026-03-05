<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use App\Jobs\ProcessLinkCreation;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class LinkController extends Controller
{
    public function index(){
        $links = Cache::remember('popular_links', 300, function() {
            return Link::orderBy('click_count', 'desc')->take(5)->get();
        });
        return view('home', compact('links'));
    }

    public function store(Request $request){
        $validData = $request->validate([
            'url_campo' => 'required|url',
        ]);

        try{
            $code = Redis::lpop(Link::REDIS_STOCK_KEY);

            if (!$code) {
                $code = Link::generateUniqueCode();
            }

            Redis::set("link:{$code}", $request->url_campo, 'EX', 86400);
            ProcessLinkCreation::dispatch($validData['url_campo'], $code);
            return redirect('/')->with([
                'sucesso' => true,
                'codigo' => $code
            ]);
            
        } catch(\Exception $e){
            return redirect('/')->with('erro', 'Erro no processamento. Tente novamente.');
        }
    }

    public function redirect($code){
        $url_cache = Redis::get("link:{$code}");
        if($url_cache){
            Redis::hincrby('link_clicks', $code, 1);
            return redirect()->away($url_cache);
        }
        $link = Link::where('short_url',$code)->firstOrFail();
        Redis::set("link:{$code}", $link->complete_url, 'EX', 86400);
        Redis::hincrby('link_clicks', $code, 1);
        return \redirect()->to($link->complete_url);
        
    }
}


