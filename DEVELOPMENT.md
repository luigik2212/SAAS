# Rodar em DEV (Windows PowerShell + Linux/macOS)

Este guia é para **este repositório atual** (Blade + Tailwind + Alpine, sem Inertia/React/Vue).

## 1) Diagnóstico do FRONT neste repositório

Situação encontrada:

- **Não há** `package.json` nem `vite.config.js` na raiz.
- As views **não usam** `@vite(...)`.
- O layout principal usa:
  - Tailwind via CDN (`https://cdn.tailwindcss.com`)
  - Alpine via CDN (`https://cdn.jsdelivr.net/.../alpinejs...`)
- Há CSS local em `public/css/print-contas.css`, carregado na view de impressão de contas.

✅ Conclusão: **o front roda sem npm/Vite neste estado atual**.

---

## 2) Pré-requisitos

- PHP 8.2+
- Composer 2+
- MySQL 8+
- Extensões PHP comuns para Laravel (pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json)

> Observação importante: este snapshot não inclui `composer.json`. Em um clone completo do app Laravel, ele precisa existir na raiz.

---

## 3) Setup DEV (MySQL + timezone America/Sao_Paulo)

### 3.1 Entrar na raiz do projeto

#### Windows (PowerShell)
```powershell
cd C:\caminho\para\SAAS
```

#### Linux/macOS
```bash
cd /caminho/para/SAAS
```

### 3.2 Instalar dependências PHP (Composer)

#### Windows (PowerShell)
```powershell
composer install
```

#### Linux/macOS
```bash
composer install
```

### 3.3 Criar `.env`

#### Windows (PowerShell)
```powershell
Copy-Item .env.example .env
```

#### Linux/macOS
```bash
cp .env.example .env
```

### 3.4 Gerar APP_KEY

#### Windows (PowerShell)
```powershell
php artisan key:generate
```

#### Linux/macOS
```bash
php artisan key:generate
```

### 3.5 Configurar banco MySQL no `.env`

Use estes valores (ajuste apenas o que for necessário):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_saas
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
APP_TIMEZONE=America/Sao_Paulo
```

### 3.6 Rodar migrations e seed

Este repositório possui migrations e seeders. Rode:

#### Windows (PowerShell)
```powershell
php artisan migrate
php artisan db:seed
```

#### Linux/macOS
```bash
php artisan migrate
php artisan db:seed
```

> Opcional em uma linha: `php artisan migrate --seed`

### 3.7 Subir o Laravel

#### Windows (PowerShell)
```powershell
php artisan serve
```

#### Linux/macOS
```bash
php artisan serve
```

Acesse: `http://127.0.0.1:8000`

---

## 4) Como subir os assets do front neste projeto

Como o projeto atual **não usa Vite** e usa CDN para Tailwind/Alpine:

- **Não execute `npm install` nem `npm run dev`** para este snapshot.
- O CSS local usado na impressão está em `public/css/print-contas.css`.
- O restante do estilo/script vem de CDN nas próprias Blade views.

---

## 5) Troubleshooting

### Erro: `npm ERR! ENOENT package.json`

Causa comum:
- você está fora da raiz do projeto **ou** este snapshot realmente não possui front build com npm.

Como resolver:
1. Verifique a pasta atual.
   - PowerShell: `Get-Location`
   - Linux/macOS: `pwd`
2. Liste arquivos e confirme se existe `package.json`.
   - PowerShell: `Get-ChildItem`
   - Linux/macOS: `ls -la`
3. Neste repositório atual, a ausência de `package.json` é esperada; rode apenas o Laravel/PHP.

### Erro: `Vite manifest not found`

Causa comum:
- layout/view usa `@vite(...)`, mas o build do Vite não foi gerado.

Como resolver (projetos que usam Vite):
1. `npm install`
2. Ambiente DEV: `npm run dev`
3. Ambiente sem watcher: `npm run build`
4. Garanta que as views tenham `@vite(['resources/css/app.css', 'resources/js/app.js'])`.

> Neste snapshot, esse erro **não deveria ocorrer** porque não há `@vite(...)`.

### CSS não carrega

Checklist:
1. Confirme se a view/layout está carregando assets corretos.
   - Neste projeto: CDN (`tailwindcss`, `alpinejs`) e `asset('css/print-contas.css')` para impressão.
2. Teste abrir diretamente o CSS local:
   - `http://127.0.0.1:8000/css/print-contas.css`
3. Se estiver usando outro branch/projeto com Vite, confirme:
   - `@vite(...)` no Blade
   - `npm run dev` em execução

---

## 6) Comandos mínimos (copiar e colar)

### Windows (PowerShell)
```powershell
cd C:\caminho\para\SAAS
composer install
Copy-Item .env.example .env
php artisan key:generate
# edite .env (DB_* + APP_TIMEZONE=America/Sao_Paulo)
php artisan migrate
php artisan db:seed
php artisan serve
```

### Linux/macOS
```bash
cd /caminho/para/SAAS
composer install
cp .env.example .env
php artisan key:generate
# edite .env (DB_* + APP_TIMEZONE=America/Sao_Paulo)
php artisan migrate
php artisan db:seed
php artisan serve
```

> FRONT neste snapshot: sem npm; assets via CDN + `public/css/print-contas.css`.
