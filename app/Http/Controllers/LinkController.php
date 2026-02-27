<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index(){
        $links = Link::latest()->take(5)->get();
        return view('home', compact('links'));
    }

    public function store(Request $request){
        $validData = $request->validate([
            'url_campo' => 'required|url',
        ]);

        try{
            $code = Link::generateUniqueCode();
            Link::create([
                'complete_url' => $validData['url_campo'],
                'short_url' => $code
            ]);
            return redirect('/')->with([
                'sucesso' => true,
                'codigo' => $code
            ]);
            
        } catch(\Exception $e){
            return "Erro ao salvar no banco: " . $e->getMessage();
        }
    }

    public function redirect($url){
        $link = Link::where('short_url',$url)->firstOrFail();
        $link->increment('click_count');

        return \redirect()->to($link->complete_url);
    }
}
