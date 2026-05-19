<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class DocsController extends Controller
{
    public function index(): Response
    {
        return $this->renderPage('index');
    }

    public function show(string $page): Response
    {
        return $this->renderPage($page);
    }

    private function renderPage(string $slug): Response
    {
        $slug = preg_replace('/[^a-z0-9\-]/i', '', $slug);
        $base = base_path('docs');
        $file = $base.'/'.$slug.'.md';

        if (! is_file($file)) {
            abort(404);
        }

        $contents = file_get_contents($file);

        $title = $slug;
        if (preg_match('/^#\s+(.+)$/m', $contents, $m)) {
            $title = trim($m[1]);
        }

        $env = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $env->addExtension(new CommonMarkCoreExtension);
        $env->addExtension(new GithubFlavoredMarkdownExtension);
        $env->addExtension(new TableExtension);

        $converter = new MarkdownConverter($env);
        $html = (string) $converter->convert($contents);

        return Inertia::render('docs/show', [
            'title' => $title,
            'html' => $html,
            'pages' => $this->indexPages(),
            'current' => $slug,
        ]);
    }

    private function indexPages(): array
    {
        $base = base_path('docs');
        if (! is_dir($base)) {
            return [];
        }

        $files = glob($base.'/*.md') ?: [];
        $pages = [];
        foreach ($files as $f) {
            $slug = basename($f, '.md');
            $first = '';
            $handle = fopen($f, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match('/^#\s+(.+)$/', $line, $m)) {
                        $first = trim($m[1]);
                        break;
                    }
                }
                fclose($handle);
            }
            $pages[] = [
                'slug' => $slug,
                'title' => $first ?: $slug,
                'url' => $slug === 'index' ? '/docs' : '/docs/'.$slug,
            ];
        }

        usort($pages, fn ($a, $b) => $a['slug'] === 'index' ? -1 : ($b['slug'] === 'index' ? 1 : strcmp($a['title'], $b['title'])));

        return $pages;
    }
}
