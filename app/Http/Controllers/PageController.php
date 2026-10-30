<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\HtmlSanitizer;
use App\Support\Markdown;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response
    {
        $page = Page::forLocale($slug, app()->getLocale()) ?? abort(404);

        // Seeded bodies are Markdown; editor-authored bodies are already HTML.
        $body = preg_match('/<\/?[a-z][\s\S]*>/i', (string) $page->body) === 1
            ? (string) $page->body
            : Markdown::toHtml((string) $page->body);

        return Inertia::render('Page', [
            'page' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'group' => $page->group,
                // Re-sanitised on read so legacy rows are safe too.
                'body' => HtmlSanitizer::clean($body),
                'meta_description' => $page->meta_description,
                'updated_at' => $page->updated_at
                    ? $page->updated_at->copy()->locale(app()->getLocale())->translatedFormat('j M Y')
                    : null,
            ],
        ]);
    }
}
