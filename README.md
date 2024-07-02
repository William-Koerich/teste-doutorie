## Como rodar o projeto


1. Clone o projeto
2. Abra o terminal e navegue até a pasta do projeto
3. Execute o comando `npm install` para instalar todas as dependências
4. Renomeie o arquivo `.env.example` para `.env`
5. Gere a chave da aplicação com o comando `php artisan key generate`
6. Rode as migrations com o comando `php artisan migrate`
7. Rode as seeders com o comando `php artisan db::seed`

## Observações.

Não utilizei o Docker com o banco de dados pois escolhi usar o Supabase, não precisa ficar com receio, o Supabase é uma plataforma Open Source para criar aplicações.
