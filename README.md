# Url Shortener — v2

Esse é um projeto que desenvolvi para estudo, onde criei um encurtador de links funcional utilizando o **Laravel** como ferramenta principal. A ideia foi entender na prática como o framework lida com rotas, banco de dados, filas assíncronas, cache com Redis e a arquitetura MVC.

> **Evolução:** Esta é a **versão 2** do projeto. Na [v1](https://github.com/dudupadilha/url-shortener/releases/tag/v1.0), tudo era feito de forma síncrona — o código era gerado aleatoriamente e o INSERT no banco acontecia dentro da própria request, o que funcionava bem para uso simples, mas travava sob alta carga. Nesta versão, introduzi **filas assíncronas**, **pré-geração de códigos**, **cache de redirects** e **contagem de cliques via Redis**, tornand# URL Shortener — v2

A functional URL shortener built with **Laravel** as the main framework. The goal was to understand in practice how Laravel handles routing, database operations, async queues, Redis caching, and MVC architecture.

> **Evolution:** This is **version 2** of the project. In [v1](https://github.com/dudupadilha/url-shortener/releases/tag/v1.0), everything was synchronous — the short code was generated randomly and the INSERT happened directly inside the request, which worked fine under light load but struggled under high concurrency. In this version, I introduced **async queues**, **pre-generated codes**, **redirect caching**, and **click counting via Redis**, making the application capable of handling hundreds of thousands of requests.



## What it does

Paste a long URL and the system generates a random 10-character short code.

- **Shortens:** Generates the short link using pre-generated codes from Redis and saves to the database asynchronously via queue.
- **Redirects:** Resolves the short link to the original URL using Redis cache for maximum performance.
- **Counts clicks:** Every access increments a click counter in Redis, synced to MySQL periodically.
- **Lists popular links:** The homepage shows the 5 most-clicked links.
- **Auto-expires:** Links expire after 3 months and are cleaned up automatically.



## What changed from v1 to v2?

| Aspect | v1 (sync-insert) | v2 (async-queue) |
|---|---|---|
| **Code generation** | Random per request with duplicate check in DB | Pre-generated in bulk (1M) and stored in Redis; expanded to 10 characters to minimize collision probability |
| **DB insert** | Synchronous inside the request | Async via Laravel Job on Redis queue |
| **Redirect** | MySQL lookup on every access | Redis cache, MySQL as fallback |
| **Click counting** | `UPDATE` in MySQL on every click | `hincrby` in Redis, synced to MySQL every 5 min |
| **Link expiration** | Not implemented | Expires after 3 months, daily cleanup |
| **Performance** | ~100 req/s, struggled under load | Tested with 500K+ concurrent requests |
| **Server** | Standard PHP-FPM | Laravel Octane with Swoole |



## Tech stack

- **Laravel 12** (PHP 8.4)
- **Laravel Octane** with Swoole for high performance
- **MySQL** for data persistence
- **Redis** for caching, queues, code inventory, and click counters
- **Docker** with Laravel Sail for the development environment
- **Tailwind CSS** for styling (via CDN)
- **Blade** for HTML templates



## Architecture

```
  Request ──▶ Controller ──▶ Redis (lpop pre-generated code)
                  │
                  ├──▶ Redis (redirect cache)
                  │
                  └──▶ Queue (ProcessLinkCreation) ──▶ MySQL

  Redirect ──▶ Redis (cache) ──▶ hincrby clicks
                  │
                  └──▶ MySQL (fallback if no cache)

  Schedule ──▶ Sync Redis clicks → MySQL (every 5 min)
           ──▶ Pre-generate codes in Redis (every 30 min)
           ──▶ Clean up expired links (daily)
```



## What I practiced building this

- **MVC in practice:** Organizing code across Model, View, and Controller layers
- **Eloquent ORM:** Saving, querying, and incrementing data in the database
- **Async queues:** Processing DB inserts via Laravel Jobs with Redis as the queue driver
- **Redis caching:** Storing redirects, click counters, and unique code inventory
- **Code pre-generation:** 1 million unique codes stored in Redis to avoid collisions and gain performance
- **Laravel Octane + Swoole:** High-performance server keeping the application in memory
- **Docker:** Full containerization with Laravel Sail (PHP, MySQL, Redis)
- **Scheduled tasks:** Artisan commands for data sync, cleanup, and code replenishment
- **Stress testing:** Tested with 500K+ concurrent requests using `xargs` + `curl`



## Running locally

1. Clone the repository:
   ```bash
   git clone https://github.com/dudupadilha/url-shortener.git
   ```

2. Navigate to the project folder:
   ```bash
   cd url-shortener
   ```

3. Create the `.env` file:
   ```bash
   cp .env.example .env
   ```

4. Start the containers:
   ```bash
   ./vendor/bin/sail up -d
   ```

5. Generate the application key:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. Run migrations:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. Pre-generate the code inventory:
   ```bash
   ./vendor/bin/sail artisan app:pre-generate-codes
   ```

8. Start the queue worker (separate terminal):
   ```bash
   ./vendor/bin/sail artisan queue:work --tries=3
   ```

9. Start the scheduler (separate terminal):
   ```bash
   ./vendor/bin/sail artisan schedule:work
   ```

10. Open in your browser: `http://localhost`o a aplicação capaz de lidar com centenas de milhares de requisições.



## O que o projeto faz?

Basicamente, você cola uma URL longa e o sistema gera um código aleatório de 10 caracteres.
* **Encurta:** Gera o link curto usando códigos pré-gerados no Redis e salva no banco de dados via fila assíncrona.
* **Redireciona:** Quando você acessa o link curto, ele te redireciona para a URL original usando cache Redis para máxima performance.
* **Conta cliques:** Toda vez que o link é acessado, o contador de cliques é incrementado no Redis e sincronizado com o MySQL periodicamente.
* **Lista os populares:** Na página inicial, aparecem os 5 links mais clicados.
* **Expira automaticamente:** Links expiram após 3 meses e são limpos automaticamente.



## O que mudou da v1 para a v2?

| Aspecto | v1 (sync-insert) | v2 (async-queue) |
|---|---|---|
| **Geração de códigos** | Gerado aleatoriamente a cada request com verificação de duplicata no banco | Pré-gerados em lote (1M) e armazenados no Redis; o tamanho foi expandido para 10 caracteres para garantir unicidade e minimizar drasticamente a probabilidade de colisões. |
| **Inserção no banco** | Síncrona dentro da request (INSERT direto) | Assíncrona via Job na fila do Redis |
| **Redirect** | Busca no MySQL a cada acesso | Cache no Redis, MySQL só como fallback |
| **Contagem de cliques** | `UPDATE` no MySQL a cada clique | `hincrby` no Redis, sync com MySQL a cada 5 min |
| **Expiração de links** | Não tinha | Expira após 3 meses, limpeza automática diária |
| **Performance** | ~100 req/s, travava sob carga | Testado com 500K+ requisições simultâneas |
| **Server** | PHP-FPM padrão | Laravel Octane com Swoole |



## Tecnologias que usei

* **Laravel 12** (PHP 8.4)
* **Laravel Octane** com Swoole para alta performance.
* **MySQL** para persistência dos dados.
* **Redis** para cache, filas, estoque de códigos e contadores de cliques.
* **Docker** com Laravel Sail para o ambiente de desenvolvimento.
* **Tailwind CSS** para o visual (via CDN).
* **Blade** para os templates HTML.



## Arquitetura

```
  Request ──▶ Controller ──▶ Redis (lpop código pré-gerado)
                  │                    
                  ├──▶ Redis (cache do redirect)
                  │
                  └──▶ Fila (ProcessLinkCreation) ──▶ MySQL
                  
  Redirect ──▶ Redis (cache) ──▶ hincrby cliques
                  │
                  └──▶ MySQL (fallback se não tem cache)

  Schedule ──▶ Sync cliques Redis → MySQL (5 min)
           ──▶ Pré-gerar códigos no Redis (30 min)
           ──▶ Limpar links expirados (diário)
```



## O que pratiquei fazendo este projeto?

* **MVC na prática:** Como organizar o código entre Model, View e Controller.
* **Eloquent ORM:** Como salvar, buscar e incrementar dados no banco.
* **Filas assíncronas:** Processamento de inserções no banco via jobs do Laravel com Redis como driver.
* **Cache com Redis:** Armazenar redirects, contadores de cliques e estoque de códigos únicos.
* **Pré-geração de códigos:** Estoque de 1 milhão de códigos únicos no Redis para evitar colisões e ganhar performance.
* **Laravel Octane + Swoole:** Servidor de alta performance mantendo a aplicação em memória.
* **Docker:** Containerização completa com Laravel Sail (PHP, MySQL, Redis).
* **Scheduled Tasks:** Comandos agendados para sincronização de dados, limpeza e reabastecimento.
* **Teste de stress:** Testado com 500K+ requisições simultâneas usando `xargs` + `curl`.



## Como rodar na sua máquina

1. Clone o repositório:
   ```bash
   git clone https://github.com/dudupadilha/url-shortener.git
   ```

2. Entre na pasta:
   ```bash
   cd url-shortener
   ```

3. Crie o arquivo `.env` (copie do `.env.example`):
   ```bash
   cp .env.example .env
   ```

4. Suba os containers com Sail:
   ```bash
   ./vendor/bin/sail up -d
   ```

5. Gere a chave da aplicação:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. Rode as migrations:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. Pré-gere o estoque de códigos:
   ```bash
   ./vendor/bin/sail artisan app:pre-generate-codes
   ```

8. Inicie os workers da fila (em um terminal separado):
   ```bash
   ./vendor/bin/sail artisan queue:work --tries=3
   ```

9. Inicie o scheduler (em um terminal separado):
   ```bash
   ./vendor/bin/sail artisan schedule:work
   ```

10. Acesse no navegador: `http://localhost`
