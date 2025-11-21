## Blog baseado em Markdown

Notas rápidas sobre a feature de blog estático.

### Estrutura dos posts

- Local: `resources/content/blog`
- Formato: arquivos `.md` com front matter YAML:

```md
---
title: "Título do post"
slug: "slug-em-kebab-case"
description: "Resumo curto para meta description."
published_at: "YYYY-MM-DD"
tags: ["tag1", "tag2"]
status: "published"
---

# Título

Conteúdo em Markdown...
```

### Renderização e repositório

- Código em `app/Blog/BlogRepository.php` e `BlogPost.php`.
- Usa `league/commonmark` para converter Markdown em HTML.
- Filtra apenas `status = published` e ordena por `published_at` (desc).
- Métodos principais:
  - `paginated(int $perPage = 10)`
  - `all()`
  - `findBySlug(string $slug)`

### Cache e invalidação

- Cache principal: `blog.posts`.
- Hash de alterações dos arquivos: `blog.posts.hash`.
- O repositório recalcula o hash (nomes + mtimes) e recarrega automaticamente se algo mudar.
- Para limpar manualmente:

```bash
php artisan cache:forget blog.posts
php artisan cache:forget blog.posts.hash
```

### Rotas e páginas

- Rotas públicas em `routes/web.php`:
  - `GET /blog` → listagem (paginada).
  - `GET /blog/{slug}` → página do post.
- Controller: `app/Http/Controllers/BlogController.php`.
- Views:
  - Listagem: `resources/views/blog/index.blade.php` (card destaque + grade).
  - Post: `resources/views/blog/show.blade.php` (meta tags via `partials.head`).

### Navegação

- Link “Blog” adicionado na landing page em `resources/views/welcome.blade.php`.

### Dicas rápidas

- Após adicionar ou editar `.md`, a listagem se atualiza automaticamente; limpe o cache apenas se necessário.
- Certifique-se de manter o `slug` único e sem acentos.
- Datas são exibidas em português via `->locale('pt_BR')`.
