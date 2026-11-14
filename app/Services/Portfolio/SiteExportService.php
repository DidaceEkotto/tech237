<?php

namespace App\Services\Portfolio;

use App\Models\PortfolioSite;
use Illuminate\Support\Str;
use ZipArchive;

class SiteExportService
{
    public function export(PortfolioSite $site, string $format = 'html'): string
    {
        $site->loadMissing(['pages', 'themeParts', 'user']);
        $tmpDir = storage_path('app/tmp-exports');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . 'site-' . $site->slug . '-' . $format . '-' . time() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de créer l’archive ZIP.');
        }

        $css = $this->buildCss($site);
        $js = $this->buildJs();
        $images = $this->collectLocalImages($site);

        if ($format === 'html') {
            $zip->addFromString('css/style.css', $css);
            $zip->addFromString('js/app.js', $js);
            foreach ($images as $name => $absPath) {
                $zip->addFile($absPath, 'images/' . $name);
            }
            foreach ($site->pages as $page) {
                $filename = $page->is_homepage ? 'html/index.html' : 'html/' . Str::slug($page->slug) . '.html';
                $zip->addFromString($filename, $this->buildHtmlPage($site, $page, '../'));
            }
        } elseif ($format === 'php') {
            $zip->addFromString('public/css/style.css', $css);
            $zip->addFromString('public/js/app.js', $js);
            foreach ($images as $name => $absPath) {
                $zip->addFile($absPath, 'public/images/' . $name);
            }
            $sqlitePath = $this->buildSqliteDb($site, $tmpDir);
            if ($sqlitePath && file_exists($sqlitePath)) {
                $zip->addFile($sqlitePath, 'database/site.sqlite');
            }
            $zip->addFromString('public/index.php', $this->buildPhpEntry($site));
            $zip->addFromString('admin/index.php', $this->buildPhpAdmin($site));
            $zip->addFromString('README.md', "# {$site->name} (Export PHP + SQLite)\n\nLancez `php -S localhost:8000 -t public` pour démarrer.");
        } else {
            // Laravel + SQLite starter
            $zip->addFromString('public/css/style.css', $css);
            $zip->addFromString('public/js/app.js', $js);
            foreach ($images as $name => $absPath) {
                $zip->addFile($absPath, 'public/images/' . $name);
            }
            $sqlitePath = $this->buildSqliteDb($site, $tmpDir);
            if ($sqlitePath && file_exists($sqlitePath)) {
                $zip->addFile($sqlitePath, 'database/database.sqlite');
            }
            $zip->addFromString('routes/web.php', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\nuse App\\Http\\Controllers\\SiteController;\n\nRoute::get('/', [SiteController::class, 'home']);\nRoute::get('/admin', [SiteController::class, 'admin']);\nRoute::get('/{slug}', [SiteController::class, 'page']);\n");
            $zip->addFromString('app/Http/Controllers/SiteController.php', $this->buildLaravelController());
            $zip->addFromString('resources/views/site.blade.php', $this->buildHtmlPage($site, $site->pages->first(), '/'));
            $zip->addFromString('README.md', "# {$site->name} (Export Laravel + SQLite)\n\nCopiez ces fichiers dans un projet Laravel 11/12/13 avec `DB_CONNECTION=sqlite`.");
        }

        if (!$zip->close() || !file_exists($zipPath)) {
            throw new \RuntimeException('Impossible de finaliser l’archive ZIP.');
        }
        return $zipPath;
    }

    private function collectLocalImages(PortfolioSite $site): array
    {
        $found = [];
        $json = json_encode($site->pages->pluck('draft_tree')->all());
        if (preg_match_all('/\/storage\/([^\s"\'\\\\]+)/', (string) $json, $matches)) {
            foreach ($matches[1] as $rel) {
                $abs = storage_path('app/public/' . $rel);
                if (file_exists($abs)) {
                    $found[basename($rel)] = $abs;
                }
            }
        }
        return $found;
    }

    private function buildCss(PortfolioSite $site): string
    {
        $primary = $site->global_settings['primary_color'] ?? '#4f46e5';
        return <<<CSS
:root {
    --pb-primary: {$primary};
}
body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    margin: 0;
    color: #0f172a;
    background: #ffffff;
}
.pb-section { padding: 48px 16px; }
.pb-container { max-width: 1180px; margin: 0 auto; }
.pb-tech237-footer {
    border-top: 1px solid #e2e8f0;
    padding: 16px;
    text-align: center;
    font-size: 13px;
    color: #475569;
    background: #f8fafc;
}
CSS;
    }

    private function buildJs(): string
    {
        return <<<JS
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href^="#"]').forEach((a) => {
        a.addEventListener('click', (e) => {
            const id = a.getAttribute('href').slice(1);
            const el = document.getElementById(id);
            if (el) {
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
JS;
    }

    private function buildHtmlPage(PortfolioSite $site, $page, string $assetPrefix = '../'): string
    {
        $title = e(($page->title ?? 'Accueil') . ' — ' . $site->name);
        $tree = $page->published_tree ?? $page->draft_tree ?? ['sections' => []];
        $sectionsHtml = '';

        foreach ($tree['sections'] ?? [] as $sec) {
            $secId = e($sec['advanced']['customId'] ?? $sec['id'] ?? '');
            $sectionsHtml .= "<section id=\"{$secId}\" class=\"pb-section\"><div class=\"pb-container\"><div class=\"row g-4\">";
            foreach ($sec['rows'] ?? [] as $row) {
                foreach ($row['columns'] ?? [] as $col) {
                    $w = (int) ($col['width'] ?? 12);
                    $sectionsHtml .= "<div class=\"col-md-{$w}\">";
                    foreach ($col['modules'] ?? [] as $mod) {
                        $sectionsHtml .= $this->renderModuleHtml($mod);
                    }
                    $sectionsHtml .= "</div>";
                }
            }
            $sectionsHtml .= "</div></div></section>\n";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{$title}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{$assetPrefix}css/style.css" rel="stylesheet" />
</head>
<body>
    {$sectionsHtml}
    <footer class="pb-tech237-footer">
        Propulsé par <a href="https://tech237.com" target="_blank" rel="noopener" class="fw-bold text-decoration-none">tech237.com</a>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{$assetPrefix}js/app.js"></script>
</body>
</html>
HTML;
    }

    private function renderModuleHtml(array $mod): string
    {
        $type = $mod['type'] ?? 'text';
        $c = $mod['content'] ?? [];
        if ($type === 'heading') {
            $tag = in_array($c['tag'] ?? 'h2', ['h1', 'h2', 'h3', 'h4'], true) ? $c['tag'] : 'h2';
            return "<{$tag} class=\"fw-bold mb-3\">" . e($c['text'] ?? '') . "</{$tag}>";
        }
        if ($type === 'hero') {
            return '<div class="p-4 p-md-5 rounded-4 bg-light"><h1 class="display-5 fw-bold mb-3">' . e($c['title'] ?? '') . '</h1><p class="lead text-secondary">' . e($c['subtitle'] ?? '') . '</p></div>';
        }
        if ($type === 'about') {
            return '<div class="p-4 rounded-4 border bg-white"><h2 class="fw-bold mb-2">' . e($c['title'] ?? '') . '</h2><p class="text-secondary mb-0">' . e($c['bio'] ?? '') . '</p></div>';
        }
        if ($type === 'image' && !empty($c['url'])) {
            return '<img src="' . e($c['url']) . '" alt="' . e($c['alt'] ?? '') . '" class="img-fluid rounded-4" />';
        }
        if ($type === 'button') {
            return '<a href="' . e($c['href'] ?? '#') . '" class="btn btn-primary rounded-pill px-4">' . e($c['label'] ?? 'Bouton') . '</a>';
        }
        return '<p class="text-secondary">' . e($c['text'] ?? ($c['title'] ?? '')) . '</p>';
    }

    private function buildSqliteDb(PortfolioSite $site, string $tmpDir): ?string
    {
        if (!class_exists(\SQLite3::class)) {
            return null;
        }
        $dbPath = $tmpDir . '/site-' . $site->id . '.sqlite';
        if (file_exists($dbPath)) {
            @unlink($dbPath);
        }
        $db = new \SQLite3($dbPath);
        $db->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT UNIQUE, title TEXT, html TEXT)');
        $db->exec('CREATE TABLE messages (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, message TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');

        $stmt = $db->prepare('INSERT INTO pages (slug, title, html) VALUES (:slug, :title, :html)');
        foreach ($site->pages as $page) {
            $stmt->bindValue(':slug', $page->slug, SQLITE3_TEXT);
            $stmt->bindValue(':title', $page->title, SQLITE3_TEXT);
            $stmt->bindValue(':html', $this->buildHtmlPage($site, $page, ''), SQLITE3_TEXT);
            $stmt->execute();
        }
        $db->close();
        return $dbPath;
    }

    private function buildPhpEntry(PortfolioSite $site): string
    {
        return <<<'PHP'
<?php
$db = new PDO('sqlite:' . __DIR__ . '/../database/site.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$slug = $_GET['page'] ?? 'accueil';
$stmt = $db->prepare('SELECT html FROM pages WHERE slug = :slug LIMIT 1');
$stmt->execute([':slug' => $slug]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    $row = $db->query('SELECT html FROM pages LIMIT 1')->fetch(PDO::FETCH_ASSOC);
}
echo $row ? $row['html'] : '<h1>Page introuvable</h1>';
PHP;
    }

    private function buildPhpAdmin(PortfolioSite $site): string
    {
        $siteName = e($site->name);
        return <<<PHP
<?php
\$db = new PDO('sqlite:' . __DIR__ . '/../database/site.sqlite');
\$pages = \$db->query('SELECT id, slug, title FROM pages')->fetchAll(PDO::FETCH_ASSOC);
\$messages = \$db->query('SELECT * FROM messages ORDER BY id DESC LIMIT 50')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Admin — {$siteName}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4 bg-light">
<div class="container">
<h2 class="fw-bold mb-4">Mini Administration — {$siteName}</h2>
<h5>Pages (<?= count(\$pages) ?>)</h5>
<ul class="list-group mb-4"><?php foreach (\$pages as \$p): ?><li class="list-group-item"><?= htmlspecialchars(\$p['title']) ?> (<?= htmlspecialchars(\$p['slug']) ?>)</li><?php endforeach; ?></ul>
<h5>Messages reçus (<?= count(\$messages) ?>)</h5>
<ul class="list-group"><?php foreach (\$messages as \$m): ?><li class="list-group-item"><strong><?= htmlspecialchars(\$m['name']) ?></strong>: <?= htmlspecialchars(\$m['message']) ?></li><?php endforeach; ?></ul>
</div></body></html>
PHP;
    }

    private function buildLaravelController(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function home()
    {
        $page = DB::table('pages')->first();
        return response($page?->html ?? '<h1>Accueil</h1>');
    }

    public function page(string $slug)
    {
        $page = DB::table('pages')->where('slug', $slug)->firstOrFail();
        return response($page->html);
    }

    public function admin()
    {
        $pages = DB::table('pages')->select('id', 'slug', 'title')->get();
        return response()->json(['pages' => $pages]);
    }
}
PHP;
    }
}
