<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Game;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $games = Game::published()->latest('updated_at')->get();
        $categories = Category::active()->get();

        $xml = view('seo.sitemap', compact('games', 'categories'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /tai-khoan',
            'Sitemap: ' . route('sitemap'),
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}

