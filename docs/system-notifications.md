# Notificações para usuários

Guia rápido para disparar mensagens individuais pelo sistema de notificações já existente (canal `database`).

## Campos aceitos (GenericSystemNotification)

- `title`: título curto exibido na listagem.
- `message`: corpo da mensagem.
- `tag`: rótulo/assunto exibido como badge (ex.: `Onboarding`, `Admin`).
- `meta`: array opcional com informações extras (mostradas em pares label/valor).
- `level`: `success | warning | danger | info` (define cor/ícone).
- `actionUrl` e `actionLabel`: botão opcional (abre o link no target padrão `_self`).
- `icon`: opcional (ex.: `bell-alert`, `check-badge`).

## Enviar manualmente (tinker)

```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Notifications\GenericSystemNotification;

$user = User::where('email', 'usuario@dominio.com')->firstOrFail();

$user->notify(new GenericSystemNotification(
    title: 'Atualização importante',
    message: 'Seu plano foi atualizado.',
    tag: 'Admin',
    meta: ['origem' => 'Painel interno'],
    level: 'info', // success | warning | danger | info
    actionUrl: url('/app/minha-area'),
    actionLabel: 'Ver detalhes',
    icon: 'bell-alert',
));
```

- A notificação fica registrada na tabela `notifications` e aparece no centro de notificações (`livewire.notifications.index`).
- O usuário consegue marcar como lida/não lida; não há envio por e-mail neste fluxo.

## Enviar via código (job, controller, Livewire)

Use o mesmo snippet acima em qualquer ponto do código onde você tiver o `User` carregado:

```php
$user->notify(new GenericSystemNotification(...));
```

## Opcional: fila

Por padrão a notificação é síncrona. Se quiser enfileirar, implemente `ShouldQueue` na `GenericSystemNotification` (e garanta o worker rodando).
