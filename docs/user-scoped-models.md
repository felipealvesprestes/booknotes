# Models escopadas por usuário

Para garantir que registros sejam sempre associados ao usuário autenticado e exibidos apenas para o dono, utilize a trait `App\Models\Concerns\BelongsToAuthenticatedUser`.

## Como usar

1. Garanta que a tabela tenha uma coluna `user_id` com foreign key para `users`.
2. Na model, adicione:

   ```php
   use App\Models\Concerns\BelongsToAuthenticatedUser;

   class Note extends Model
   {
       use BelongsToAuthenticatedUser;

       // ...
   }
   ```

3. Agora:
   - `user_id` será preenchido automaticamente na criação (`Note::create`, `$user->notes()->create`, etc.).
   - Consultas padrão (`Note::all()`, `Note::query()`, Livewire/Controllers) só retornarão registros do usuário autenticado.
   - O relacionamento `user()` e o escopo `ownedBy()` ficam disponíveis para consultas personalizadas.

Se precisar de uma coluna diferente, sobrescreva a propriedade na model:

```php
protected string $userForeignKey = 'owner_id';
```

Em seeders, factories ou outros contextos sem usuário autenticado, desabilite o escopo quando necessário:

```php
Note::withoutGlobalScopes()->create([...]);
```
