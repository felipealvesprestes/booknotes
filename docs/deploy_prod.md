# 🚀 Deploy em Produção — Booknotes

Guia rápido e completo para aplicar alterações no ambiente de produção.

---

## 🧩 1. Acesso ao servidor

```
ssh root@SEU_DROPLET
cd /var/www/booknotes
```

---

## 🔒 2. (Opcional) Ativar modo manutenção

Use quando for alterar banco de dados, dependências ou assets críticos.

```
php artisan down --secret="booknotes-deploy"
```

🔗 Acesse via `https://www.booknotes.com.br/booknotes-deploy` se precisar testar durante o modo manutenção.

---

## ⬇️ 3. Atualizar código

```
git pull origin main   # ou a branch em uso
```

---

## 🧰 4. Atualizar dependências PHP

```
composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-bcmath
```

---

## 🧱 5. Build de front-end (JS/CSS com Vite)

Execute **somente se houver alterações** em `resources/js` ou `resources/css`:

```
npm ci || npm install
npm run build
```

---

## 🗃️ 6. Rodar migrations (quando necessário)

```
php artisan migrate --force
```

---

## 💳 6.1 Variáveis de ambiente Stripe/Cashier

Antes de publicar, garanta que o `.env` de produção contém:

- `STRIPE_KEY` / `STRIPE_SECRET` (usar as chaves **live**)
- `STRIPE_PRICE_ID=price_1SRc0lA5HGNTUlGMohJIFB39`
- `STRIPE_WEBHOOK_SECRET=` (copiar do Stripe após criar o webhook)
- `SUBSCRIPTION_TRIAL_DAYS=14`
- `SUBSCRIPTION_PLAN_NAME="Acesso Plataforma Booknotes"`
- `SUBSCRIPTION_MONTHLY_AMOUNT=14.90`
- `SUBSCRIPTION_LIFETIME_EMAILS="felipealvesprestes@gmail.com,gabrielakrauzerprestes@gmail.com"`
- `CASHIER_CURRENCY=brl` e `CASHIER_CURRENCY_LOCALE=pt_BR`

> Use as chaves de teste apenas no ambiente de desenvolvimento. Em produção, substitua pelas chaves live.

---

## 🔔 6.2 Webhooks Stripe

1. Crie o endpoint `https://www.booknotes.com.br/stripe/webhook` no Stripe Dashboard → Developers → Webhooks.
2. Assine os eventos padrões do Cashier (`customer.subscription.updated`, `customer.subscription.deleted`, `invoice.payment_succeeded`, `invoice.payment_failed`, `checkout.session.completed`, `payment_intent.succeeded`, `payment_intent.payment_failed`).
3. Copie o *Signing secret* e preencha `STRIPE_WEBHOOK_SECRET` no `.env`.
4. Para testar localmente: `stripe listen --forward-to http://localhost:8000/stripe/webhook`.

---

## ⚙️ 7. Limpar e recriar caches

Essencial após qualquer alteração de código, view, rota ou config.

```
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🔐 8. Ajustar permissões

```
chown -R www-data:www-data /var/www/booknotes
```

---

## 🧵 9. Reiniciar fila (queue worker)

```
supervisorctl restart booknotes-queue
supervisorctl status booknotes-queue
```

Se necessário:

```
systemctl reload php8.3-fpm
```

---

## ✅ 10. Smoke tests

Verifique rapidamente:

```
curl -I https://www.booknotes.com.br
curl -I https://www.booknotes.com.br/login
```

No navegador:

-   Login e navegação básica
-   Upload e exportação de PDFs (fila)
-   Banner LGPD/cookies (primeiro acesso)
-   Funcionalidade geral de notas, matérias e cadernos

---

## 🔓 11. (Opcional) Retirar modo manutenção

```
php artisan up
```

---

## 💡 Dicas extras

-   Sempre faça `npm run build` após alterações JS/CSS.
-   Rodar `php artisan optimize:clear` **resolve 90% dos problemas** pós-deploy.
-   Caso algo pareça “travado”, confira:

```
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```
