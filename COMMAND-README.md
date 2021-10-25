#Laravel コマンドメモ

###Laravelキャッシュクリア
`$ php artisan cache:clear
`
###テスト実行(全て)
```
$ php artisan test
or
$ vendor/bin/phpunit
```

###テスト実行(パラレル)
```
$ php artisan test --parallel
```

###テスト実行(ファイル指定)
```
php artisan test --filter SignUpControllerTest
```

###マイグレーションリセット、テストデータ挿入
`$ php artisan migrate:fresh --seed
`
###コントローラークラス作成
`$ php artisan make:controller BlogViewController
`
###モデルクラス作成 + マイグレーションファイル作成 + ファクトリークラス作成
`$ php artisan make:model Comment -mf
`
###ユニットテストクラス作成 + Unit/Modelsフォルダ配下
`$ php artisan make:test Models/BlogTest --unit
`
###フィーチャーテストクラス作成 + Feature/Controllersフォルダ配下
`$ php artisan make:test Controllers/BlogViewControllerTest`

###ミドルウェアクラス作成 
`$ php artisan make:middleware BlogShowLimit`
