<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Hashtag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Lắng nghe khi nào cái file Blade của Sidebar được gọi ra thì chạy hàm
        View::composer('partials.social_trending', function ($view) {
            $trendingTags = Hashtag::withCount('posts')
                ->orderByDesc('posts_count')
                ->take(5)
                ->get();

            // Tự chế mớ logic rút gọn số liệu (Chế biến thêm cột formatted_count)
            foreach ($trendingTags as $tag) {
                $count = $tag->usage_count ?? $tag->posts_count ?? 0;

                // Logic rút gọn số liệu: 1500 => 1.5k, 2.3 triệu => 2.3M
                if ($count >= 1000000) {
                    $tag->formatted_count = round($count / 1000000, 1) . 'M';
                } elseif ($count >= 1000) {
                    $tag->formatted_count = round($count / 1000, 1) . 'k';
                } else {
                    $tag->formatted_count = $count;
                }
            }

            $view->with('trendingTags', $trendingTags);
        });
    }
}
