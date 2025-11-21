<?php

namespace App\Blog;

use Illuminate\Support\Carbon;

class BlogPost
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $description,
        public Carbon $published_at,
        public array $tags,
        public string $status,
        public string $content_html,
    ) {
    }
}
