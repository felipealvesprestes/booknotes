# 📬 Mailer local com Mailpit

Use esta configuração para inspecionar todos os e-mails enviados durante o desenvolvimento sem atingir provedores externos.

---

## 1. Pré-requisitos

- Docker / Docker Desktop instalado.
- Laravel Sail já configurado (o `compose.yaml` inclui o serviço `mailpit`).

---

## 2. Subir o Mailpit

```bash
# se estiver usando Sail
./vendor/bin/sail up -d mailpit

# ou diretamente com Docker Compose
docker compose up -d mailpit
```

- SMTP ficará disponível na porta `1025` (configurada via `MAILPIT_SMTP_PORT`).
- A interface web roda na porta `8025` (`MAILPIT_PORT`).

---

## 3. Variáveis de ambiente

O `.env.example` já aponta para o Mailpit:

```
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_ENCRYPTION=null
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="contato@booknotes.com.br"
MAIL_FROM_NAME="Booknotes"
```

> Ao usar Sail, o hostname `mailpit` é automaticamente resolvido pela rede Docker. Se estiver rodando a aplicação fora dos containers, ajuste `MAIL_HOST=127.0.0.1`.

---

## 4. Visualizar e-mails

1. Gere qualquer e-mail (registro de usuário, reenviar verificação etc).
2. Acesse `http://localhost:8025`.
3. A interface do Mailpit listará todas as mensagens, com visualização HTML, texto puro e cabeçalhos.

---

## 5. Dicas

- O Mailpit armazena tudo em memória; reiniciar o container limpa os e-mails.
- Para compartilhar um e-mail específico com o time, use o botão **Share** da UI para copiar o link.
- Antes de subir para produção, lembre-se de trocar as variáveis para o provedor real (Mailgun, SES, etc.).

---

## 6. Produção com Mailgun

No servidor da DigitalOcean, ajuste o `.env` para usar o mailer `mailgun` (já configurado em `config/mail.php`) e defina o DSN:

```
MAIL_MAILER=mailgun
MAIL_HOST=null
MAIL_PORT=null
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAILGUN_DSN="mailgun+smtp://booknotes%40booknotes.com.br:Canoas%402025%24@smtp.mailgun.org:587"
MAILGUN_DOMAIN="booknotes.com.br"
MAILGUN_SECRET="SUA_API_KEY"   # substitua pela API key real
MAILGUN_ENDPOINT="api.mailgun.net"
MAIL_FROM_ADDRESS="contato@booknotes.com.br"
MAIL_FROM_NAME="Booknotes"
```

> O DSN acima usa o login SMTP `booknotes@booknotes.com.br`, senha `Canoas@2025$`, host `smtp.mailgun.org` e porta `587`. Para o campo `MAILGUN_SECRET`, utilize a API key real do Mailgun (não o password SMTP).

Depois de atualizar, execute `php artisan config:clear && php artisan config:cache` para aplicar.
