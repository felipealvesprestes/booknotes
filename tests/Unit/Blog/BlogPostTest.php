<?php

use App\Blog\BlogPost;
use Illuminate\Support\Carbon;

test('blog post stores provided attributes', function (): void {
    $publishedAt = Carbon::parse('2024-11-30 12:00:00');

    $post = new BlogPost(
        title: 'New Release',
        slug: 'new-release',
        description: 'Highlights from the new release',
        published_at: $publishedAt,
        tags: ['release', 'news'],
        status: 'published',
        content_html: '<p>Hello</p>',
    );

    expect($post->title)->toBe('New Release')
        ->and($post->slug)->toBe('new-release')
        ->and($post->description)->toBe('Highlights from the new release')
        ->and($post->published_at)->toEqual($publishedAt)
        ->and($post->tags)->toBe(['release', 'news'])
        ->and($post->status)->toBe('published')
        ->and($post->content_html)->toBe('<p>Hello</p>');
});
