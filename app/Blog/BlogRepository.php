<?php

namespace App\Blog;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\ConverterInterface;
use Symfony\Component\Yaml\Yaml;

class BlogRepository
{
    public const CACHE_KEY = 'blog.posts';
    private const CACHE_HASH_KEY = 'blog.posts.hash';

    private ConverterInterface $converter;

    public function __construct(?ConverterInterface $converter = null)
    {
        $this->converter = $converter ?? new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function all(): Collection
    {
        $hash = $this->computeHash();
        $cachedHash = Cache::get(self::CACHE_HASH_KEY);
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached && $cachedHash === $hash) {
            return $cached;
        }

        $posts = $this->loadPosts();

        Cache::forever(self::CACHE_KEY, $posts);
        Cache::forever(self::CACHE_HASH_KEY, $hash);

        return $posts;
    }

    public function paginated(int $perPage = 10): LengthAwarePaginator
    {
        $posts = $this->all();
        $page = Paginator::resolveCurrentPage('page');

        $items = $posts->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $posts->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }

    public function findBySlug(string $slug): ?BlogPost
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_HASH_KEY);
    }

    private function computeHash(): string
    {
        $directory = resource_path('content/blog');

        if (! File::exists($directory)) {
            return 'missing';
        }

        $fingerprint = collect(File::files($directory))
            ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'md')
            ->map(fn (\SplFileInfo $file): string => $file->getFilename() . ':' . $file->getMTime())
            ->sort()
            ->values()
            ->implode('|');

        return md5($fingerprint);
    }

    private function loadPosts(): Collection
    {
        $directory = resource_path('content/blog');

        if (! File::exists($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'md')
            ->map(fn (\SplFileInfo $file): ?BlogPost => $this->parsePost($file->getPathname()))
            ->filter()
            ->filter(fn (BlogPost $post): bool => $post->status === 'published')
            ->sortByDesc(fn (BlogPost $post): Carbon => $post->published_at)
            ->values();
    }

    private function parsePost(string $path): ?BlogPost
    {
        $contents = File::get($path);

        if (! preg_match('/^---\\s*(.*?)\\s*---\\s*(.*)$/s', $contents, $matches)) {
            return null;
        }

        $frontMatter = Yaml::parse($matches[1]) ?? [];

        if (! is_array($frontMatter)) {
            return null;
        }

        $body = trim($matches[2]);

        $title = (string) ($frontMatter['title'] ?? '');
        $slug = (string) ($frontMatter['slug'] ?? '');
        $description = (string) ($frontMatter['description'] ?? '');
        $publishedAt = $frontMatter['published_at'] ?? null;

        if ($title === '' || $slug === '' || $description === '' || ! $publishedAt) {
            return null;
        }

        try {
            $publishedAt = Carbon::parse($publishedAt);
        } catch (\Throwable) {
            return null;
        }

        $status = strtolower((string) ($frontMatter['status'] ?? 'draft'));
        $tags = $frontMatter['tags'] ?? [];
        $tags = collect(is_array($tags) ? $tags : [$tags])
            ->filter(fn (mixed $tag): bool => filled($tag))
            ->map(fn (mixed $tag): string => (string) $tag)
            ->values()
            ->all();

        $contentHtml = (string) $this->converter->convert($body);

        return new BlogPost(
            $title,
            $slug,
            $description,
            $publishedAt,
            $tags,
            $status,
            $contentHtml,
        );
    }
}
