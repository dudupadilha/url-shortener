# Url Shortener

Esse é um projeto que desenvolvi para estudo, onde criei um encurtador de links funcional utilizando o **Laravel** como ferramenta principal. A ideia foi entender na prática como o framework lida com rotas, banco de dados e a arquitetura MVC.



## O que o projeto faz?

Basicamente, você cola uma URL longa e o sistema gera um código aleatório de 6 caracteres.
* **Encurta:** Gera o link curto e salva no banco de dados.
* **Redireciona:** Quando você acessa o link curto, ele te redireciona para a URL original.
* **Conta cliques:** Toda vez que o link é acessado, o contador de cliques aumenta.
* **Lista os recentes:** Na página inicial, aparecem os últimos 5 links criados com a contagem de acessos.



## Tecnologias que usei

* **Laravel 11** (PHP 8.2+)
* **MySQL** para o banco de dados.
* **Tailwind CSS** para o visual (via CDN).
* **Blade** para os templates HTML.



## O que pratiquei fazendo este projeto?

* **MVC na prática:** Como organizar o código entre Model, View e Controller.
* **Eloquent ORM:** Como salvar, buscar e incrementar dados (`increment`) no banco de forma simples.
* **Validação:** Como garantir que o usuário envie apenas URLs válidas.
* **Lógica de Unicidade:** Criei um método no Model que garante que o código encurtado nunca se repita, fazendo uma checagem no banco antes de finalizar a criação.

## Como rodar na sua máquina

1. Clone o repositório: git clone https://github.com/dudupadilha/url-shortener.git

2. Entre na pasta e rode composer install.

3. Crie o arquivo .env (copie do .env.example) e coloque os dados do seu banco.

4. Rode php artisan key:generate.

5. Rode php artisan migrate para criar as tabelas.

6. Rode php artisan serve e acesse no navegador!
