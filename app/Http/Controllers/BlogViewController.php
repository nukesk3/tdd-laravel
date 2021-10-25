<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Facades\Illuminate\Support\Str;

class BlogViewController extends Controller
{
    public function index()
    {
        $blogs = Blog::withCount('comments')
            ->onlyOpen()
            ->orderByDesc('comments_count')
            ->get();
        return view('index',compact('blogs'));
    }

    public function show(Blog $blog)
    {
        if ($blog->isClosed()) {
            abort(403);
        }

        $random = Str::random(10);

        return view('blog.show',compact('blog', 'random'));
    }

}
