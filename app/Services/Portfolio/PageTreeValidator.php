<?php

namespace App\Services\Portfolio;

use Illuminate\Support\Str;

class PageTreeValidator
{
    public const SCHEMA_VERSION = 1;

    /**
     * Validate and sanitize a page builder JSON tree (Sections -> Rows -> Columns -> Modules).
     */
    public function sanitizeTree(?array $tree): array
    {
        if (!is_array($tree)) {
            return $this->emptyTree();
        }

        $sections = [];
        foreach (($tree['sections'] ?? []) as $section) {
            if (!is_array($section)) {
                continue;
            }
            $sections[] = $this->sanitizeSection($section);
        }

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'sections' => $sections,
        ];
    }

    public function emptyTree(): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'sections' => [],
        ];
    }

    private function sanitizeSection(array $section): array
    {
        $rows = [];
        foreach (($section['rows'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rows[] = $this->sanitizeRow($row);
        }

        return [
            'id' => $this->safeId($section['id'] ?? null, 'sec'),
            'label' => $this->safeText($section['label'] ?? 'Section', 80),
            'design' => is_array($section['design'] ?? null) ? $this->sanitizeScalarMap($section['design']) : [],
            'advanced' => is_array($section['advanced'] ?? null) ? $this->sanitizeAdvanced($section['advanced']) : [],
            'rows' => $rows,
        ];
    }

    private function sanitizeRow(array $row): array
    {
        $columns = [];
        foreach (($row['columns'] ?? []) as $col) {
            if (!is_array($col)) {
                continue;
            }
            $columns[] = $this->sanitizeColumn($col);
        }

        return [
            'id' => $this->safeId($row['id'] ?? null, 'row'),
            'layout' => $this->safeText($row['layout'] ?? '1', 32),
            'design' => is_array($row['design'] ?? null) ? $this->sanitizeScalarMap($row['design']) : [],
            'advanced' => is_array($row['advanced'] ?? null) ? $this->sanitizeAdvanced($row['advanced']) : [],
            'columns' => $columns,
        ];
    }

    private function sanitizeColumn(array $col): array
    {
        $modules = [];
        foreach (($col['modules'] ?? []) as $mod) {
            if (!is_array($mod)) {
                continue;
            }
            $modules[] = $this->sanitizeModule($mod);
        }

        return [
            'id' => $this->safeId($col['id'] ?? null, 'col'),
            'width' => $this->safeText($col['width'] ?? '12', 16),
            'design' => is_array($col['design'] ?? null) ? $this->sanitizeScalarMap($col['design']) : [],
            'advanced' => is_array($col['advanced'] ?? null) ? $this->sanitizeAdvanced($col['advanced']) : [],
            'modules' => $modules,
        ];
    }

    private function sanitizeModule(array $mod): array
    {
        return [
            'id' => $this->safeId($mod['id'] ?? null, 'mod'),
            'type' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($mod['type'] ?? 'text')),
            'template_variant' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($mod['template_variant'] ?? 'classic')),
            'content' => is_array($mod['content'] ?? null) ? $this->sanitizeContentMap($mod['content']) : [],
            'design' => is_array($mod['design'] ?? null) ? $this->sanitizeScalarMap($mod['design']) : [],
            'advanced' => is_array($mod['advanced'] ?? null) ? $this->sanitizeAdvanced($mod['advanced']) : [],
        ];
    }

    private function sanitizeAdvanced(array $adv): array
    {
        return [
            'html_id' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($adv['html_id'] ?? '')),
            'css_class' => preg_replace('/[^a-zA-Z0-9_\-\s]/', '', (string) ($adv['css_class'] ?? '')),
            'animation' => preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($adv['animation'] ?? 'none')),
            'hide_desktop' => (bool) ($adv['hide_desktop'] ?? false),
            'hide_tablet' => (bool) ($adv['hide_tablet'] ?? false),
            'hide_mobile' => (bool) ($adv['hide_mobile'] ?? false),
        ];
    }

    private function sanitizeContentMap(array $data): array
    {
        $clean = [];
        foreach ($data as $k => $v) {
            $key = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $k);
            if ($key === '') {
                continue;
            }
            if (is_array($v)) {
                $clean[$key] = $this->sanitizeContentMap($v);
            } elseif (is_bool($v) || is_int($v) || is_float($v) || $v === null) {
                $clean[$key] = $v;
            } else {
                // Strip dangerous script/event handlers while keeping safe text
                $str = (string) $v;
                $str = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $str);
                $str = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $str);
                $clean[$key] = $str;
            }
        }
        return $clean;
    }

    private function sanitizeScalarMap(array $data): array
    {
        $clean = [];
        foreach ($data as $k => $v) {
            $key = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $k);
            if ($key === '') {
                continue;
            }
            if (is_array($v)) {
                $clean[$key] = $this->sanitizeScalarMap($v);
            } elseif (is_bool($v) || is_int($v) || is_float($v) || $v === null) {
                $clean[$key] = $v;
            } else {
                $clean[$key] = strip_tags((string) $v);
            }
        }
        return $clean;
    }

    private function safeId(?string $id, string $prefix): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($id ?? ''));
        return $clean !== '' ? $clean : ($prefix . '_' . Str::random(10));
    }

    private function safeText(?string $text, int $max): string
    {
        return Str::limit(strip_tags((string) ($text ?? '')), $max, '');
    }
}
