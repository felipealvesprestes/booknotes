# E-mail de proposta institucional (piloto Booknotes)

Comando Artisan para disparar o e-mail de parceria educacional para instituições, usando o template dedicado com o assunto “Proposta de parceria educacional (piloto) – Plataforma Booknotes”.

## Variáveis de ambiente
- `INSTITUTIONAL_PARTNERSHIP_EMAILS`: lista CSV de destinatários padrão. Ex.: `"contato@faculdade.br,parcerias@faculdade.br"`
- `BOOKNOTES_APP_URL`: URL usada no CTA “Conhecer o Booknotes”. Ex.: `https://www.booknotes.com.br`
- `BOOKNOTES_CONTACT_NAME`: nome exibido na assinatura. Ex.: `Felipe Prestes`
- `BOOKNOTES_CONTACT_EMAIL`: e-mail de resposta/contato. Ex.: `contato@booknotes.com.br`
- `BOOKNOTES_CONTACT_PHONE`: telefone/WhatsApp exibido no rodapé. Ex.: `51 9 99985956`

## Comando Artisan
```
php artisan email:institution-partnership {emails?} {--institution=}
```
- Sem argumento, lê os destinatários de `INSTITUTIONAL_PARTNERSHIP_EMAILS`.
- `emails` (opcional): lista CSV ad hoc que ignora o env. Ex.: `"contato@faculdade.br,parcerias@faculdade.br"`.
- `--institution` (opcional): nome da instituição mostrado em “Olá, equipe da ...”. Padrão: `Instituição`.

Exemplos:
```
# Usando .env
php artisan email:institution-partnership

# Lista ad hoc e nome customizado
php artisan email:institution-partnership "contato@faculdade.br,parcerias@faculdade.br" --institution="Faculdade Exemplo"
```

## Template e mailable
- Template: `resources/views/emails/institutions/partnership.blade.php`
- Mailable: `App\Mail/InstitutionalPartnershipEmail`

## Observações rápidas
- Validação: e-mails são normalizados (trim, lowercase) e inválidos são descartados; se nenhum válido for encontrado, o comando apenas exibe aviso e encerra.
- Se `BOOKNOTES_CONTACT_EMAIL` não estiver definido, o mailable usa `mail.from` ou `mail.reply_to` como fallback, ou `contato@booknotes.com.br` por último.
