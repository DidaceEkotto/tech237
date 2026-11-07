<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Tech237</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --tech-navy: #0B2545;
            --tech-green: #00D18F;
        }
        body {
            font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }
        .error-card {
            max-width: 680px;
            width: 100%;
            background: #ffffff;
            border-radius: 1.5rem;
            padding: 3rem 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            text-align: center;
        }
        .brand-logo {
            max-height: 50px;
            object-fit: contain;
            margin-bottom: 2rem;
        }
        .btn-tech {
            background-color: var(--tech-green);
            color: #ffffff;
            font-weight: 700;
            border-radius: 50rem;
            padding: 0.6rem 1.8rem;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-tech:hover {
            opacity: 0.92;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-outline-custom {
            border-radius: 50rem;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <a href="/">
            <img src="/assets/images/logo.png" alt="Tech237" class="brand-logo" onerror="this.style.display='none'">
        </a>
        @yield('content')

        @if(config('app.debug'))
            <div class="text-start mt-4 p-3 rounded-3 bg-light border">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-warning text-dark fw-bold">
                        <i class="bi bi-bug-fill me-1"></i> Mode Débogage (APP_DEBUG=true)
                    </span>
                </div>
                <div class="small text-muted font-monospace mb-1">
                    <strong>Message :</strong> @yield('message')
                </div>
                @hasSection('debug_details')
                    <div class="small text-muted font-monospace">
                        @yield('debug_details')
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
