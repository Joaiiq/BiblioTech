# Deploy no Railway

## Estrutura recomendada para este projeto

- App: Laravel 12 detectado pelo Railway/Railpack.
- Banco: MySQL, usando o serviço MySQL do Railway.
- Assets: `npm run build`.
- Pre-deploy: migrations, seeders, `storage:link`, cache de config e views.
- Start: `php artisan serve --host=0.0.0.0 --port=${PORT}`.
- Healthcheck: `/up`.

## Variáveis do serviço App

Configure no Railway em `App service > Variables`.

```env
APP_NAME=BiblioTech
APP_ENV=production
APP_KEY=base64:COLE_A_CHAVE_AQUI
APP_DEBUG=false
APP_URL=https://SEU-DOMINIO.up.railway.app
ASSET_URL=https://SEU-DOMINIO.up.railway.app

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR
APP_TIMEZONE=America/Fortaleza

LOG_CHANNEL=stderr
LOG_STDERR_FORMATTER=Monolog\Formatter\JsonFormatter
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public

MAIL_MAILER=log
MAIL_FROM_ADDRESS=nao-responder@bibliotech.local
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

## Passo a passo rápido

1. Faça push do repositório para o GitHub.
2. No Railway, crie um projeto novo e selecione `Deploy from GitHub repo`.
3. Adicione um serviço `MySQL` no mesmo projeto.
4. Crie um Volume no serviço do app com mount path `/app/storage/app/public`.
5. No serviço do app, cole as variáveis acima.
6. Gere a chave localmente com:

```bash
php artisan key:generate --show
```

7. Cole a chave em `APP_KEY`.
8. Gere um domínio em `Settings > Networking > Generate Domain`.
9. Ajuste `APP_URL` e `ASSET_URL` para esse domínio, sempre com `https://`.
10. Faça deploy.

## Observações importantes

- Uploads de capas e fotos ficam em `storage/app/public`. No Railway, use um Volume montado em `/app/storage/app/public`; sem isso, imagens podem sumir em restart/redeploy ou não aparecer entre instâncias.
- O Railway executa o pre-deploy do `railway.json`, então migrations e seeders rodam antes da nova versão entrar no ar.
- Se CSS/JS aparecerem como `http://` no navegador, revise `APP_URL` e `ASSET_URL` no Railway e redeploye para refazer o cache de configuração.
