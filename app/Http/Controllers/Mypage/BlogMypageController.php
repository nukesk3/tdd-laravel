<?php

namespace App\Http\Controllers\Mypage;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BlogMypageController extends Controller
{
    public function index()
    {
        $blogs = auth()->user()->blogs;

        return view('mypage.blog.index', compact('blogs'));
    }

    public function create()
    {
        return view('mypage.blog.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateInput();

        $data['status'] = $request->boolean('status');

        $blog = auth()->user()->blogs()->create($data);
        return redirect('mypage/blogs/edit/'.$blog->id);
    }

    public function edit(Blog $blog, Request $request)
    {
        if ($request->user()->isNot($blog->user)) {
            abort(403);
        }

        $data = old() ? : $blog;

        return view('mypage.blog.edit', compact('blog','data'));
    }

    public function update(Blog $blog, Request $request)
    {
        // 所有チェック
        if ($request->user()->isNot($blog->user)) {
            abort(403);
        }

        $data = $this->validateInput();

        $data['status'] = $request->boolean('status');

        $blog->update($data);

        return redirect(route('mypage.blog.edit',$blog))->with('status','ブログを更新しました。');
    }

    public function destroy(Blog $blog, Request $request)
    {
        // 所有チェック
        if ($request->user()->isNot($blog->user)) {
            abort(403);
        }

        $blog->delete();
        return redirect('mypage/blogs');
    }

    private function validateInput()
    {
        return request()->validate([
            'title' => ['required','max:255'],
            'body' => ['required'],
        ]);
    }
}
