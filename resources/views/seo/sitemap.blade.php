{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('games.explore') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach($categories as $category)
        <url>
            <loc>{{ route('games.category', $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at?->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach($games as $game)
        <url>
            <loc>{{ route('games.show', $game->slug) }}</loc>
            <lastmod>{{ $game->updated_at?->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach
</urlset>

