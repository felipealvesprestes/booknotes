# E-mail de reengajamento (Booknotes)

E-mail para convidar usuários inativos a retornarem para a plataforma, reutilizando o layout do e-mail de verificação.

## Variáveis de ambiente
- `REENGAGEMENT_EMAILS`: lista de e-mails separados por vírgula. Ex.: `felipealvesprestes@gmail.com,fulano@teste.com`
- `BOOKNOTES_APP_URL`: URL usada no botão/links do e-mail. Ex.: `https://www.booknotes.com.br`
- `BOOKNOTES_INSTAGRAM_URL`: URL exibida no bloco de “novidades”. Ex.: `https://instagram.com/booknotes.br`

## Comando Artisan
```
php artisan email:reengagement
```
- Lê os destinatários de `REENGAGEMENT_EMAILS`.
- Busca o nome no banco se existir (User) e envia o template com o nome ou fallback sem nome.

Para enviar para uma lista ad hoc (ignora o env e usa o argumento):
```
php artisan email:reengagement "email1@dominio.com,email2@dominio.com"
```

## Template e mailable
- Template: `resources/views/emails/users/reengagement.blade.php`
- Mailable: `App\Mail\ReengagementEmail`

O e-mail segue o mesmo visual do e-mail de verificação (gradiente roxo/azul, CTA redondo) e inclui:
- Texto de convite, reforçando evolução recente da plataforma.
- CTA para voltar ao Booknotes (usa `BOOKNOTES_APP_URL`).
- Link para Instagram (usa `BOOKNOTES_INSTAGRAM_URL`).
- Rodapé com contato (`mail.from.address` ou fallback `contato@booknotes.com.br`).***
