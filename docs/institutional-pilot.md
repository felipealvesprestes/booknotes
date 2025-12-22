# Piloto de acessos institucionais (1 ano, 100 alunos)

Processo para liberar acesso anual a alunos de uma instituição sem painel ou checkout Stripe: importar os e-mails enviados pela instituição, habilitar um trial de 12 meses e disparar e-mails de definição de senha.

## O que habilita acesso hoje
- O middleware `EnsureSubscribed` libera o app se o usuário tiver assinatura ativa **ou** estiver em período de teste (`trial_ends_at` no futuro) **ou** tiver acesso vitalício (`is_lifetime`).
- Para o piloto, basta garantir `trial_ends_at` daqui a 1 ano; não é necessário criar assinatura no Stripe.

## Dados que a instituição deve enviar
- Lista de e-mails dos alunos (CSV/linha a linha, sem formatação).
- Nome da instituição e data de início desejada (para registrar no log interno).
- Opcional: nome dos alunos para personalizar o cadastro; se não vier, usamos o prefixo do e-mail.

## Passo a passo (modo manual atual)
1) Salve os e-mails em um arquivo texto, um por linha, ex.: `/tmp/alunos.csv`.
2) Rode o script abaixo no `php artisan tinker` (ajuste caminho do arquivo e data de expiração).

```php
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

$emails = array_filter(array_map('trim', file('/tmp/alunos.csv'))); // um por linha
$expiresAt = now()->addYear(); // 1 ano de acesso

collect($emails)->each(function (string $email) use ($expiresAt) {
    $user = User::firstOrCreate(
        ['email' => $email],
        [
            'name' => Str::before($email, '@'),
            'password' => Str::random(32), // senha descartável
            'email_verified_at' => now(),
        ],
    );

    $user->forceFill([
        'trial_starts_at' => now(),
        'trial_ends_at' => $expiresAt,
    ])->save();

    Password::sendResetLink(['email' => $email]); // envia e-mail para definir senha
});
```

3) Valide rapidamente:
- Contagem: `User::whereIn('email', $emails)->count();`
- Amostra: `User::whereIn('email', $emails)->take(5)->get(['email','trial_ends_at']);`

4) Comunique a instituição/alunos:
- Informe que receberão um e-mail de redefinição de senha do Booknotes.
- Data de validade do acesso: `{{ $expiresAt->format('d/m/Y') }}`.
- Canal de suporte para dúvidas ou reenvio do e-mail.

## Recomendações operacionais
- Execute em produção apenas após validar o arquivo de e-mails (UTF-8, sem espaços extras).
- Se o aluno já existir com trial menor, o script prorroga para a nova data.
- Se quiser reexecutar com a mesma lista, não cria duplicados (usa `firstOrCreate`).
- Para reenvio manual do e-mail de senha: `Password::sendResetLink(['email' => 'aluno@exemplo.com']);`

## Envio do e-mail de proposta
- Template: `resources/views/emails/institutions/partnership.blade.php` (usa o assunto “Proposta de parceria educacional (piloto) – Plataforma Booknotes”).
- Comando: `php artisan email:institution-partnership {emails?} {--institution=}`, validando a lista e avisando se nenhum destinatário for encontrado.
- Sem argumento, busca os contatos em `INSTITUTIONAL_PARTNERSHIP_EMAILS` no `.env` (CSV). Exemplo direto: `php artisan email:institution-partnership "contato@faculdade.br,parcerias@faculdade.br" --institution="Faculdade Exemplo"`.
- Dados dinâmicos no e-mail: `--institution` (nome que aparece no “Olá, equipe da ...”), `BOOKNOTES_APP_URL`, `BOOKNOTES_CONTACT_NAME`, `BOOKNOTES_CONTACT_EMAIL`, `BOOKNOTES_CONTACT_PHONE`.

## Próximos passos (futuro)
- Criar um comando dedicado (`php artisan institution:grant-access --file=/tmp/alunos.csv --expires=2026-03-01 --institution="Nome"`) com log estruturado (tabela `institution_access_logs`).
- Adicionar template de e-mail específico explicando o benefício institucional e a data de expiração.
- Guardar metadados da instituição no usuário (`institution_name`) para relatórios e renovação.
