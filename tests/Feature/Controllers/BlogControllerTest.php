<?php

use App\Blog\BlogPost;
use App\Blog\BlogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

beforeEach(function (): void {
    config(['app.name' => 'Booknotes']);
});

afterEach(function (): void {
    \Mockery::close();
});

it('renders the blog index with paginated posts and metadata', function (): void {
    $post = makeBlogPost([
        'title' => 'Study Habits',
        'slug' => 'study-habits',
        'description' => 'Improve your focus.',
    ]);

    $paginator = new LengthAwarePaginator(
        new Collection([$post]),
        1,
        10,
        1,
        ['path' => route('blog.index')]
    );

    $repository = \Mockery::mock(BlogRepository::class);
    $repository->shouldReceive('paginated')->once()->with(10)->andReturn($paginator);
    app()->instance(BlogRepository::class, $repository);

    $response = $this->get(route('blog.index'));

    $response->assertOk()
        ->assertViewIs('blog.index')
        ->assertViewHas('posts', fn ($value) => $value === $paginator)
        ->assertViewHas('title', 'Blog - Booknotes')
        ->assertViewHas('metaDescription', fn ($value) => str_contains($value, 'Guias sobre estudo eficiente'))
        ->assertViewHas('canonical', route('blog.index'));
});

it('shows an individual post when the slug exists', function (): void {
    $post = makeBlogPost([
        'title' => 'Deep Work Tips',
        'slug' => 'deep-work-tips',
        'description' => 'Stay focused.',
    ]);

    $repository = \Mockery::mock(BlogRepository::class);
    $repository->shouldReceive('findBySlug')->once()->with('deep-work-tips')->andReturn($post);
    app()->instance(BlogRepository::class, $repository);

    $response = $this->get(route('blog.show', $post->slug));

    $response->assertOk()
        ->assertViewIs('blog.show')
        ->assertViewHas('post', fn ($value) => $value === $post)
        ->assertViewHas('title', 'Deep Work Tips - Blog | Booknotes')
        ->assertViewHas('metaDescription', 'Stay focused.')
        ->assertViewHas('canonical', route('blog.show', $post->slug));
});

it('returns a not found response when the slug is missing', function (): void {
    $repository = \Mockery::mock(BlogRepository::class);
    $repository->shouldReceive('findBySlug')->once()->with('missing-post')->andReturn(null);
    app()->instance(BlogRepository::class, $repository);

    $response = $this->get(route('blog.show', 'missing-post'));

    $response->assertNotFound();
});

function makeBlogPost(array $overrides = []): BlogPost
{
    $attributes = array_merge([
        'title' => 'Sample Title',
        'slug' => 'sample-slug',
        'description' => 'Sample description.',
        'published_at' => Carbon::parse('2024-01-01 12:00:00'),
        'tags' => ['productivity'],
        'status' => 'published',
        'content_html' => '<p>Content</p>',
    ], $overrides);

    return new BlogPost(
        $attributes['title'],
        $attributes['slug'],
        $attributes['description'],
        $attributes['published_at'],
        $attributes['tags'],
        $attributes['status'],
        $attributes['content_html'],
    );
}
