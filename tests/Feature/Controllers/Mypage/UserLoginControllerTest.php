<?php

namespace Tests\Feature\Controllers\Mypage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Mypage\UserLoginController
 */
class UserLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test index */
    function ログイン画面を開ける()
    {
        $this->get('mypage/login')
            ->assertOk();
    }

    /** @test login */
    function ログイン時の入力チェック()
    {
        $url = "mypage/login";

        $this->from($url)->post($url,[])
            ->assertRedirect($url);

        $this->post($url,['email' => ''])
            ->assertSessionHasErrors(['email' => 'メールアドレスは必ず指定してください。']);
        $this->post($url,['email' => 'aa@@bb@@cc'])
            ->assertSessionHasErrors(['email' => 'メールアドレスには、有効なメールアドレスを指定してください。']);
        $this->post($url,['email' => 'aa@ああ.いい'])
            ->assertSessionHasErrors(['email' => 'メールアドレスには、有効なメールアドレスを指定してください。']);

        $this->post($url,['password' => ''])
            ->assertSessionHasErrors(['password' => 'パスワードは必ず指定してください。']);
    }

    /** @test login */
    function ログインできる()
    {
        $postData = [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234'
        ];

        $dbData = [
            'email' => 'aaa@bbb.net',
            'password' => bcrypt('abcd1234')
        ];

        $user = User::factory()->create($dbData);

        $this->post('mypage/login', $postData)
            ->assertRedirect('mypage/blogs');

        $this->assertAuthenticatedAs($user);
    }

    /** @test login */
    function メールアドレスを間違えているのでログインできない()
    {
        $postData = [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234'
        ];

        $dbData = [
            'email' => 'cccccc@bbb.net',
            'password' => bcrypt('abcd1234')
        ];

        $user = User::factory()->create($dbData);

        $this->withExceptionHandling();

        $url = 'mypage/login';
        $this->from($url)->post($url,$postData)
            ->assertRedirect($url);

        $this->get($url)
            ->assertSee('メールアドレスかパスワードが間違っています。');

        $this->from($url)->followingRedirects()->post($url,$postData)
            ->assertSee('メールアドレスかパスワードが間違っています。')
            ->assertSee('<h1>ログイン画面</h1>',false);
    }

    /** @test login */
    function パスワードを間違えているのでログインできない()
    {
        $postData = [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234'
        ];

        $dbData = [
            'email' => 'aaa@bbb.net',
            'password' => bcrypt('abcd5678')
        ];

        $user = User::factory()->create($dbData);

        $this->withExceptionHandling();

        $url = 'mypage/login';
        $this->from($url)->post($url,$postData)
            ->assertRedirect($url);

        $this->get($url)
            ->assertSee('メールアドレスかパスワードが間違っています。');

        $this->from($url)->followingRedirects()->post($url,$postData)
            ->assertSee('メールアドレスかパスワードが間違っています。')
            ->assertSee('<h1>ログイン画面</h1>',false);
    }

    /** @test login */
    function 認証エラーなのでvalidationExceptionの例外が発生する()
    {
        // Exceptionを検証する場合はこの記述がないと例外が発生しない事になる
        $this->withoutExceptionHandling();

        $postData = [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234'
        ];

        try {
            $this->post('mypage/login', $postData);
            $this->fail('validationExceptionの例外が発生しませんでした。');
        } catch (ValidationException $e) {
            $this->assertEquals('メールアドレスかパスワードが間違っています。',
                $e->errors()['email'][0] ?? '');
        }
    }

    /** @test login */
    function 認証OKなのでvalidationExceptionの例外が出ない()
    {
        // Exceptionを検証する場合はこの記述がないと例外が発生しない事になる
        $this->withoutExceptionHandling();

        $postData = [
            'email' => 'aaa@bbb.net',
            'password' => 'abcd1234'
        ];

        $dbData = [
            'email' => 'aaa@bbb.net',
            'password' => bcrypt('abcd1234')
        ];

        $user = User::factory()->create($dbData);

        try {
            $this->post('mypage/login', $postData);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail('validationExceptionの例外が発生してしまいました。');
        }
    }

    /** @test logout */
    function ログアウトできる()
    {
        $this->login();
        $this->post('mypage/logout')
            ->assertRedirect($url = 'mypage/login');

        $this->get($url)
            ->assertSee('ログアウトしました。');

        $this->assertGuest();
    }
}
