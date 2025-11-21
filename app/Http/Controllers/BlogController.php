<?php

namespace App\Http\Controllers;

use App\Blog\BlogRepository;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(
        private BlogRepository $blog,
    ) {
    }

    public function index(): View
    {
        $posts = $this->blog->paginated(10);

        return view('blog.index', [
            'posts' => $posts,
            'title' => 'Blog - ' . config('app.name'),
            'metaDescription' => 'Guias sobre estudo eficiente, novidades do produto e práticas de organização para o ' . config('app.name') . '.',
            'canonical' => route('blog.index'),
        ]);
    }

    public function show(string $slug): View
    {
        $post = $this->blog->findBySlug($slug);

        abort_if(! $post, 404);

        return view('blog.show', [
            'post' => $post,
            'title' => $post->title . ' - Blog | ' . config('app.name'),
            'metaDescription' => $post->description,
            'canonical' => route('blog.show', $post->slug),
        ]);
    }
}
