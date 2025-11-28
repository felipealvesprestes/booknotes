<?php

use App\Blog\BlogPost;
use App\Blog\BlogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator as PaginationPaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use League\CommonMark\ConverterInterface;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Cache::flush();
});

it('returns cached posts when the hash matches', function (): void {
    File::shouldReceive('exists')->andReturn(false);

    $posts = collect([
        new BlogPost('Cached', 'cached', 'Desc', Carbon::now(), [], 'published', '<p>Cached</p>'),
    ]);

    Cache::forever(BlogRepository::CACHE_KEY, $posts);
    Cache::forever('blog.posts.hash', 'missing');

    $repository = new BlogRepository(createConverter());

    $results = $repository->all();

    expect($results->first()->slug)->toBe('cached');
});

it('clears both cache entries', function (): void {
    Cache::forever(BlogRepository::CACHE_KEY, collect());
    Cache::forever('blog.posts.hash', 'abc');

    $repository = new BlogRepository(createConverter());

    $repository->clearCache();

    expect(Cache::get(BlogRepository::CACHE_KEY))->toBeNull()
        ->and(Cache::get('blog.posts.hash'))->toBeNull();
});

it('loads posts from disk when cache is stale', function (): void {
    $converter = createConverter();
    File::shouldReceive('exists')->andReturnTrue();
    $fileA = Mockery::mock(SplFileInfo::class);
    $fileA->shouldReceive('getExtension')->andReturn('md');
    $fileA->shouldReceive('getFilename')->andReturn('post-one.md');
    $fileA->shouldReceive('getMTime')->andReturn(123);
    $fileA->shouldReceive('getPathname')->andReturn('post-one.md');

    $fileB = Mockery::mock(SplFileInfo::class);
    $fileB->shouldReceive('getExtension')->andReturn('md');
    $fileB->shouldReceive('getFilename')->andReturn('post-two.md');
    $fileB->shouldReceive('getMTime')->andReturn(456);
    $fileB->shouldReceive('getPathname')->andReturn('post-two.md');

    File::shouldReceive('files')->andReturn([$fileA, $fileB]);
    File::shouldReceive('get')
        ->with('post-one.md')
        ->andReturn(<<<'MD'
---
title: "Published Post"
slug: "published-post"
description: "Desc"
published_at: "2024-01-01"
status: "published"
tags: ["news", "tech"]
---
Hello world
MD);
    File::shouldReceive('get')
        ->with('post-two.md')
        ->andReturn(<<<'MD'
---
title: "Draft Post"
slug: "draft-post"
description: "Desc"
published_at: "2024-01-02"
status: "draft"
---
Markdown content
MD);

    $repository = new BlogRepository($converter);

    $posts = $repository->all();

    expect($posts)->toHaveCount(1)
        ->and($posts->first()->slug)->toBe('published-post')
        ->and($posts->first()->tags)->toBe(['news', 'tech']);
});

it('paginates posts respecting the per page parameter', function (): void {
    $converter = createConverter();
    $repository = new BlogRepository($converter);

    File::shouldReceive('exists')->andReturnTrue();
    $content = fn ($i) => <<<MD
---
title: "Post {$i}"
slug: "slug-{$i}"
description: "Desc"
published_at: "2024-01-0{$i}"
status: "published"
---
Body {$i}
MD;

    $files = Collection::times(5, function ($i) use ($content) {
        $mock = Mockery::mock(SplFileInfo::class);
        $mock->shouldReceive('getExtension')->andReturn('md');
        $mock->shouldReceive('getFilename')->andReturn("post-{$i}.md");
        $mock->shouldReceive('getMTime')->andReturn($i);
        $mock->shouldReceive('getPathname')->andReturn("post-{$i}.md");
        File::shouldReceive('get')->with("post-{$i}.md")->andReturn($content($i));

        return $mock;
    })->all();

    File::shouldReceive('files')->andReturn($files);

    $posts = Collection::times(5, fn ($i) => new BlogPost(
        "Title {$i}",
        "slug-{$i}",
        'Description',
        Carbon::now(),
        [],
        'published',
        "<p>Post {$i}</p>"
    ));

    Cache::forever(BlogRepository::CACHE_KEY, $posts);

    PaginationPaginator::currentPageResolver(fn () => 2);

    $paginated = $repository->paginated(2);

    expect($paginated)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($paginated->items())->toHaveCount(2)
        ->and($paginated->items()[0]->slug)->toBe('slug-3');
});

it('finds a post by slug', function (): void {
    $converter = createConverter();
    $repository = new BlogRepository($converter);

    File::shouldReceive('exists')->andReturnTrue();
    $file = Mockery::mock(SplFileInfo::class);
    $file->shouldReceive('getExtension')->andReturn('md');
    $file->shouldReceive('getFilename')->andReturn('post.md');
    $file->shouldReceive('getMTime')->andReturn(1);
    $file->shouldReceive('getPathname')->andReturn('post.md');
    File::shouldReceive('files')->andReturn([$file]);
    File::shouldReceive('get')->andReturn(<<<'MD'
---
title: "B"
slug: "slug-b"
description: "Desc"
published_at: "2024-01-02"
status: "published"
---
Content
MD);

    $post = $repository->findBySlug('slug-b');

    expect($post)->toBeInstanceOf(BlogPost::class)
        ->and($post->title)->toBe('B');
});

it('returns empty collection when blog directory is missing', function (): void {
    $converter = createConverter();
    File::shouldReceive('exists')->andReturnFalse();

    $repository = new BlogRepository($converter);

    $posts = $repository->all();

    expect($posts)->toBeInstanceOf(Collection::class)
        ->and($posts)->toHaveCount(0);
});

it('ignores files without valid front matter', function (): void {
    File::shouldReceive('get')->with('invalid.md')->andReturn('no front matter');

    $repository = new BlogRepository(createConverter());

    expect(callParse($repository, 'invalid.md'))->toBeNull();
});

it('ignores posts when front matter is not an array', function (): void {
    File::shouldReceive('get')->with('invalid.md')->andReturn(<<<'MD'
---
true
---
Body
MD);

    $repository = new BlogRepository(createConverter());

    expect(callParse($repository, 'invalid.md'))->toBeNull();
});

it('requires mandatory fields in front matter', function (): void {
    File::shouldReceive('get')->with('invalid.md')->andReturn(<<<'MD'
---
title: ""
slug: ""
description: ""
published_at: ""
---
Body
MD);

    $repository = new BlogRepository(createConverter());

    expect(callParse($repository, 'invalid.md'))->toBeNull();
});

it('ignores posts with invalid publication date', function (): void {
    File::shouldReceive('get')->with('invalid.md')->andReturn(<<<'MD'
---
title: "Post"
slug: "post"
description: "Desc"
published_at: "invalid-date"
status: "published"
---
Body
MD);

    $repository = new BlogRepository(createConverter());

    expect(callParse($repository, 'invalid.md'))->toBeNull();
});

function createConverter(): ConverterInterface
{
    return new class implements ConverterInterface {
        public function convert(string $input): \League\CommonMark\Output\RenderedContentInterface
        {
            return new class($input) implements \League\CommonMark\Output\RenderedContentInterface {
                public function __construct(private string $content)
                {
                }

                public function getContent(): string
                {
                    return "<p>{$this->content}</p>";
                }

                public function getDocument(): \League\CommonMark\Node\Block\Document
                {
                    return new \League\CommonMark\Node\Block\Document();
                }

                public function __toString(): string
                {
                    return $this->getContent();
                }
            };
        }
    };
}

function callParse(BlogRepository $repository, string $path): ?BlogPost
{
    $method = new ReflectionMethod($repository, 'parsePost');
    $method->setAccessible(true);

    return $method->invoke($repository, $path);
}
