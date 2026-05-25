# Deploy no Railway

## Estrutura recomendada para este projeto

- App: Laravel 12 detectado pelo Railway/Railpack.
- Banco: MySQL, usando o serviço MySQL do Railway.
- Assets: `npm run build`.
- Pre-deploy: migrations, `storage:link`, cache de config e views.
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
4. No serviço do app, cole as variáveis acima.
5. Gere a chave localmente com:

```bash
php artisan key:generate --show
```

6. Cole a chave em `APP_KEY`.
7. Gere um domínio em `Settings > Networking > Generate Domain`.
8. Ajuste `APP_URL` para esse domínio.
9. Faça deploy.

## Observações importantes

- Uploads locais em `storage/app/public` podem sumir em redeploy se o app não usar volume ou storage externo. Para demonstração rápida, isso é aceitável. Para uso real, use volume Railway ou S3.
- O Railway executa o pre-deploy do `railway.json`, então as migrations rodam antes da nova versão entrar no ar.
- Se precisar popular dados de teste, rode manualmente no Railway:

```bash
php artisan db:seed --force
```
