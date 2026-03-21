<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    private string $contentPath;

    public function __construct()
    {
        $this->contentPath = resource_path('content/blog');
    }

    public function index(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');
        $posts = Cache::remember('blog.index', 3600, fn () => $this->loadAllPosts());

        return Inertia::render('Blog/Index', [
            'title' => 'Laravel SaaS Blog — Tutorials and Best Practices',
            'metaDescription' => 'Practical guides on building SaaS with Laravel 12, React, and TypeScript. Redis billing, feature flags, admin panels, and production deployment.',
            'canonicalUrl' => $appUrl.'/blog',
            'posts' => $posts,
        ]);
    }

    public function show(string $slug): Response
    {
        $appUrl = rtrim(config('app.url'), '/');
        $post = Cache::remember("blog.post.{$slug}", 3600, fn () => $this->loadPost($slug));

        if ($post === null) {
            abort(404);
        }

        return Inertia::render('Blog/Show', [
            'title' => $post['title'].' — Laravel React Starter Blog',
            'metaDescription' => $post['description'],
            'canonicalUrl' => $appUrl.'/blog/'.$slug,
            'ogImage' => asset('og-image.png'),
            'post' => $post,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function loadAllPosts(): array
    {
        if (! is_dir($this->contentPath)) {
            return [];
        }

        $files = glob($this->contentPath.'/*.md');
        if ($files === false) {
            return [];
        }

        $posts = [];
        foreach ($files as $file) {
            $frontmatter = $this->parseFrontmatter($file);
            if (! empty($frontmatter['title'])) {
                $posts[] = [
                    'title' => $frontmatter['title'],
                    'slug' => $frontmatter['slug'] ?? pathinfo($file, PATHINFO_FILENAME),
                    'description' => $frontmatter['description'] ?? '',
                    'date' => $frontmatter['date'] ?? '',
                    'readingTime' => $frontmatter['readingTime'] ?? '',
                    'tags' => $frontmatter['tags'] ?? [],
                    'href' => '/blog/'.($frontmatter['slug'] ?? pathinfo($file, PATHINFO_FILENAME)),
                ];
            }
        }

        usort($posts, fn ($a, $b) => strcmp($b['date'], $a['date']));

        return $posts;
    }

    /** @return array<string, mixed>|null */
    private function loadPost(string $slug): ?array
    {
        $file = $this->contentPath.'/'.$slug.'.md';

        if (! file_exists($file)) {
            return null;
        }

        $frontmatter = $this->parseFrontmatter($file);
        $rawContent = $this->getMarkdownBody($file);
        $html = $this->parseMarkdown($rawContent);

        return array_merge($frontmatter, [
            'slug' => $slug,
            'content' => $html,
        ]);
    }

    /** @return array<string, mixed> */
    private function parseFrontmatter(string $file): array
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return [];
        }

        if (! str_starts_with($content, '---')) {
            return [];
        }

        $end = strpos($content, '---', 3);
        if ($end === false) {
            return [];
        }

        $yaml = substr($content, 3, $end - 3);
        $data = [];

        foreach (explode("\n", trim($yaml)) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                // Handle array values (YAML list syntax: [item1, item2])
                if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                    $value = array_map('trim', explode(',', trim($value, '[]')));
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    private function getMarkdownBody(string $file): string
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return '';
        }

        if (! str_starts_with($content, '---')) {
            return $content;
        }

        $end = strpos($content, '---', 3);
        if ($end === false) {
            return $content;
        }

        return trim(substr($content, $end + 3));
    }

    private function parseMarkdown(string $markdown): string
    {
        // Basic Markdown-to-HTML conversion without external dependencies.
        // For richer parsing, install league/commonmark and swap this implementation.
        $html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');

        // Headings
        $html = preg_replace('/^#{6} (.+)$/m', '<h6>$1</h6>', $html) ?? $html;
        $html = preg_replace('/^#{5} (.+)$/m', '<h5>$1</h5>', $html) ?? $html;
        $html = preg_replace('/^#{4} (.+)$/m', '<h4>$1</h4>', $html) ?? $html;
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html) ?? $html;
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html) ?? $html;
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html) ?? $html;

        // Bold and italic
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html) ?? $html;
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html) ?? $html;

        // Inline code
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html) ?? $html;

        // Links
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html) ?? $html;

        // Paragraphs (double newlines)
        $html = preg_replace('/\n\n+/', '</p><p>', $html) ?? $html;
        $html = '<p>'.$html.'</p>';

        return $html;
    }
}
