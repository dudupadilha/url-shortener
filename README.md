# URL Shortener — v2

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

10. Open in your browser: `http://localhost`
