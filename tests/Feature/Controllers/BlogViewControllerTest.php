<?php

namespace Tests\Feature\Controllers;

use App\Http\Middleware\BlogShowLimit;
use App\Models\Blog;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Facades\Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\BlogViewController
 */
class BlogViewControllerTest extends TestCase
{
    use RefreshDatabase;
    // use WithoutMiddleware;

    /** @test index */
    function ブログのTOPページを開ける()
    {
        $blog1 = Blog::factory()->hasComments(1)->create();
        $blog2 = Blog::factory()->hasComments(3)->create();
        $blog3 = Blog::factory()->hasComments(2)->create();

        $response = $this->get('/')
            ->assertViewIs('index')
            ->assertOk()
            ->assertSee($blog1->title)
            ->assertSee($blog2->title)
            ->assertSee($blog3->title)
            ->assertSee($blog1->user->name)
            ->assertSee($blog2->user->name)
            ->assertSee($blog3->user->name)
            ->assertSee("(1件のコメント)")
            ->assertSee("(2件のコメント)")
            ->assertSee("(3件のコメント)")
            ->assertSeeInOrder([$blog2->title,$blog3->title,$blog1->title]);
    }

    /** @test index */
    function ブログの一覧、非公開のブログは表示されない()
    {
        Blog::factory()->closed()->create(
            [
                'title' => 'ブログA'
            ]
        );
        Blog::factory()->create(
            [
                'title' => 'ブログB'
            ]
        );
        Blog::factory()->create(
            [
                'title' => 'ブログC'
            ]
        );

        $this->get('/')
            ->assertOk()
            ->assertDontSee('ブログA')
            ->assertSee('ブログB')
            ->assertSee('ブログC');
    }

    /** @test show */
    function ブログの詳細画面が表示でき、コメントが古い順に表示される()
    {
        //$this->withoutMiddleware(BlogShowLimit::class);

        $blog = Blog::factory()->withCommentsData([
            ['created_at' => now()->sub('2 days'), 'name' => '太郎'],
            ['created_at' => now()->sub('3 days'), 'name' => '二郎'],
            ['created_at' => now()->sub('1 days'), 'name' => '三郎']
        ])->create();


        $this->get('blogs/'.$blog->id)
            ->assertOk()
            ->assertSee($blog->title)
            ->assertSee($blog->user->name)
            ->assertSeeInOrder(['二郎','太郎','三郎']);
    }

    /** @test show */
    function ブログの詳細画面でランダムな文字列が10文字表示される()
    {
        //$this->withoutMiddleware(BlogShowLimit::class);

        $blog = Blog::factory()->create();

        Str::shouldReceive('random')
            ->once()->with(10)->andReturn('HELLO_RAND');

        $this->get('blogs/'.$blog->id)
            ->assertOk()
            ->assertSee('HELLO_RAND');
    }

    /** @test show */
    function ブログで非公開のものは、詳細画面は表示できない()
    {
        //$this->withoutMiddleware(BlogShowLimit::class);

        $blog = Blog::factory()->closed()->create();
        $this->get('blogs/'.$blog->id)->assertForbidden();
    }

    /** @test show */
    function クリスマスの日は、メリークリスマス！と表示される()
    {
        //$this->withoutMiddleware(BlogShowLimit::class);

        $blog = Blog::factory()->create();
        Carbon::setTestNow('2020-12-24');

        $this->get('blogs/'.$blog->id)
            ->assertOk()
            ->assertDontSee('メリークリスマス！');

        $blog = Blog::factory()->create();
        Carbon::setTestNow('2020-12-25');

        $this->get('blogs/'.$blog->id)
            ->assertOk()
            ->assertSee('メリークリスマス！');
    }
}
