# Setup local — Sprint 1 (NOME_DO_SAAS)

> Observação: este repositório está com a estrutura inicial e rotas base. Para iniciar do zero com Laravel + Breeze Blade, rode os comandos abaixo localmente.

## 1) Criar projeto Laravel

```bash
composer create-project laravel/laravel NOME_DO_SAAS
cd NOME_DO_SAAS
```

## 2) Instalar Breeze (Blade + Tailwind + Alpine)

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
```

## 3) Configurar MySQL no `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_saas
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

## 4) Timezone

No `config/app.php`:

```php
'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),
```

No `.env`:

```env
APP_TIMEZONE=America/Sao_Paulo
```

## 5) Rodar migrations

```bash
php artisan migrate
```

## 6) Subir aplicação local

```bash
php artisan serve
npm run dev
```

## 7) Rotas da Sprint 1

- `/login`
- `/dashboard` (auth)
- `/contas` (auth)
- `/contas/print` (auth)

A estrutura completa está em `routes/web.php`.
