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
composer install --no-dev --optimize-autoloader
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

## ⚙️ 7. Limpar e recriar caches

Essencial após qualquer alteração de código, view, rota ou config.

```
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
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
