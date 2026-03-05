# Url Shortener — v2

Esse é um projeto que desenvolvi para estudo, onde criei um encurtador de links funcional utilizando o **Laravel** como ferramenta principal. A ideia foi entender na prática como o framework lida com rotas, banco de dados, filas assíncronas, cache com Redis e a arquitetura MVC.

> **Evolução:** Esta é a **versão 2** do projeto. Na [v1](https://github.com/dudupadilha/url-shortener/tree/v1/sync-insert), tudo era feito de forma síncrona — o código era gerado aleatoriamente e o INSERT no banco acontecia dentro da própria request, o que funcionava bem para uso simples, mas travava sob alta carga. Nesta versão, introduzi **filas assíncronas**, **pré-geração de códigos**, **cache de redirects** e **contagem de cliques via Redis**, tornando a aplicação capaz de lidar com centenas de milhares de requisições.



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
| **Geração de códigos** | Gerado aleatoriamente a cada request com verificação de duplicata no banco | Pré-gerados em lote (1M) e armazenados no Redis |
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