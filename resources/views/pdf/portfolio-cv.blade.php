<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CV — {{ $cv['fullName'] ?? 'Curriculum Vitae' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.5; margin: 0; padding: 24px; }
        .header { border-bottom: 3px solid {{ $cv['accentColor'] ?? '#4f46e5' }}; padding-bottom: 14px; margin-bottom: 18px; }
        .name { font-size: 24px; font-weight: bold; margin: 0; color: #0f172a; }
        .role { font-size: 14px; color: {{ $cv['accentColor'] ?? '#4f46e5' }}; font-weight: bold; margin: 4px 0; }
        .meta { font-size: 11px; color: #475569; }
        .section-title { font-size: 13px; font-weight: bold; text-transform: uppercase; color: {{ $cv['accentColor'] ?? '#4f46e5' }}; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin: 16px 0 10px; }
        .item { margin-bottom: 10px; }
        .item-title { font-weight: bold; font-size: 12.5px; }
        .item-sub { font-size: 11px; color: #64748b; }
        .skill-row { margin-bottom: 6px; }
        .bar-bg { background: #e2e8f0; height: 7px; border-radius: 4px; width: 100%; margin-top: 2px; }
        .bar-fill { background: {{ $cv['accentColor'] ?? '#4f46e5' }}; height: 7px; border-radius: 4px; }
        .badge { display: inline-block; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 12px; font-size: 10.5px; margin: 2px 4px 2px 0; }
        .footer { margin-top: 28px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="name">{{ $cv['fullName'] ?? 'Nom Prénom' }}</p>
        <p class="role">{{ $cv['jobTitle'] ?? 'Développeur Fullstack' }} @if(!empty($cv['experienceYears'])) • {{ $cv['experienceYears'] }} ans d'expérience @endif</p>
        <p class="meta">{{ $cv['email'] ?? '' }} @if(!empty($cv['phone'])) | {{ $cv['phone'] }} @endif @if(!empty($cv['location'])) | {{ $cv['location'] }} @endif</p>
    </div>

    @if(!empty($cv['summary']))
        <p>{{ $cv['summary'] }}</p>
    @endif

    @if(!empty($cv['formations']))
        <div class="section-title">Formations &amp; Diplômes</div>
        @foreach($cv['formations'] as $f)
            <div class="item">
                <div class="item-title">{{ $f['title'] ?? '' }}</div>
                <div class="item-sub">{{ $f['school'] ?? '' }} @if(!empty($f['period'])) • {{ $f['period'] }} @endif</div>
            </div>
        @endforeach
    @endif

    @if(!empty($cv['projects']))
        <div class="section-title">Travaux &amp; Projets Réalisés</div>
        @foreach($cv['projects'] as $p)
            <div class="item">
                <div class="item-title">{{ $p['name'] ?? '' }} @if(!empty($p['url'])) <span class="item-sub">({{ $p['url'] }})</span> @endif</div>
                @if(!empty($p['description']))
                    <div>{{ $p['description'] }}</div>
                @endif
            </div>
        @endforeach
    @endif

    @if(!empty($cv['skills']))
        <div class="section-title">Compétences Techniques</div>
        @foreach($cv['skills'] as $s)
            <div class="skill-row">
                <strong>{{ $s['name'] ?? '' }}</strong>
                <span class="item-sub">({{ $s['category'] ?? 'Tech' }}) — {{ (int) ($s['level'] ?? 80) }}%</span>
                <div class="bar-bg"><div class="bar-fill" style="width: {{ min(100, max(5, (int) ($s['level'] ?? 80))) }}%;"></div></div>
            </div>
        @endforeach
    @endif

    @if(!empty($cv['hobbies']))
        <div class="section-title">Loisirs &amp; Centres d'intérêt</div>
        @foreach($cv['hobbies'] as $h)
            <span class="badge">{{ $h['label'] ?? '' }}</span>
        @endforeach
    @endif

    <div class="footer">Généré avec Tech237 Portfolio Studio — Propulsé par tech237.com</div>
</body>
</html>
