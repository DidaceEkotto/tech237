@extends('errors.layout')

@section('title', $code ?? 'Erreur')

@section('content')
    <div class="display-1 fw-bold mb-2 font-monospace" style="color: var(--tech-navy, #0B2545); letter-spacing: -2px;">
        {{ $code ?? '404' }}
    </div>
    <div class="rounded-circle d-inline-flex p-3 fs-2 mb-3" style="background-color: rgba(0, 209, 143, 0.15); color: var(--tech-green, #00D18F);">
        <i class="bi bi-exclamation-triangle"></i>
    </div>
    <h3 class="fw-bold text-dark mb-2">@yield('title')</h3>
    <p class="text-muted lead mb-4 fs-6">
        @yield('message', 'Une erreur est survenue lors du traitement de votre requête.')
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="/" class="btn-tech">
            <i class="bi bi-house-door-fill"></i> Retour à l'accueil
        </a>
    </div>
@endsection
