<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Url Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen font-sans antialiased text-slate-900">

    <div class="max-w-4xl mx-auto px-4 py-12">
        
        <header class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-blue-600 tracking-tight mb-2">
                <i class="fa-solid fa-link mr-2"></i>Url Shortener
            </h1>
            <p class="text-slate-500 text-lg">Simples, rápido e eficiente.</p>
        </header>

        <main class="space-y-8">
            
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                <form action="/shorten" method="POST" class="flex flex-col md:flex-row gap-4">
                    @csrf
                    <div class="flex-grow">
                        <input type="url" name="url_campo" 
                            placeholder="Cole seu link longo aqui (ex: https://google.com)" 
                            class="w-full px-4 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                            required>
                    </div>
                    <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-4 rounded-xl transition-all shadow-lg shadow-blue-200 active:scale-95">
                        Encurtar
                    </button>
                </form>

                @if ($errors->any())
                    <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg">
                        <p class="font-bold">Ops! Algo deu errado:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('sucesso'))
                    <div class="mt-8 p-6 bg-blue-50 border border-blue-100 rounded-2xl text-center">
                        <p class="text-blue-600 font-medium mb-2">Seu link está pronto!</p>
                        <div class="flex items-center justify-center gap-3">
                            <input type="text" readonly value="{{ request()->getHttpHost() }}/{{ session('codigo') }}" 
                                class="bg-white border border-blue-200 text-blue-800 font-bold px-4 py-2 rounded-lg text-lg text-center w-full max-w-sm">
                            <a href="/{{ session('codigo') }}" target="_blank" 
                                class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition-colors" title="Abrir link">
                                <i class="fa-solid fa-external-link"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </section>

            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-800">Últimos Links</h2>
                    <span class="text-xs font-semibold bg-slate-100 text-slate-500 px-3 py-1 rounded-full uppercase tracking-wider">Top 5 Recentes</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50">
                            <tr class="text-slate-400 text-xs uppercase">
                                <th class="px-6 py-4 font-semibold">URL Original</th>
                                <th class="px-6 py-4 font-semibold">Encurtado</th>
                                <th class="px-6 py-4 font-semibold text-center">Cliques</th>
                                <th class="px-6 py-4 font-semibold text-right">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($links as $link)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-600 truncate max-w-[250px]" title="{{ $link->complete_url }}">
                                            {{ $link->complete_url }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="/{{ $link->short_url }}" target="_blank" class="text-blue-600 font-mono font-bold hover:underline">
                                            {{ $link->short_url }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fa-solid fa-chart-line mr-1"></i> {{ $link->click_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-400 text-sm italic">
                                        {{ $link->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">
                                        Nenhum link encurtado ainda. Comece agora!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </main>

        <footer class="mt-12 text-center text-slate-400 text-sm">
            &copy; 2026 Url Shortener - Desenvolvido com Laravel
        </footer>

    </div>

</body>
</html>