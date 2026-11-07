@extends('errors.layout')

@section('title', 'Page introuvable (404)')
@section('message', 'La ressource que vous recherchez n\'existe pas ou a été déplacée.')

@section('content')
    <div class="display-1 fw-bold mb-2 font-monospace" style="color: var(--tech-navy, #0B2545); letter-spacing: -2px;">404</div>
    <div class="rounded-circle d-inline-flex p-3 fs-2 mb-3" style="background-color: rgba(0, 209, 143, 0.15); color: var(--tech-green, #00D18F);">
        <i class="bi bi-compass"></i>
    </div>
    <h3 class="fw-bold text-dark mb-2">Oups ! Page introuvable</h3>
    <p class="text-muted lead mb-4 fs-6">
        L'article, le dossier ou la page que vous recherchez a peut-être été déplacé, renommé ou n'existe plus.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="/" class="btn-tech">
            <i class="bi bi-house-door-fill"></i> Retour à l'accueil
        </a>
        <a href="/startups" class="btn btn-outline-dark btn-outline-custom">
            <i class="bi bi-building"></i> Startups
        </a>
        <a href="/emplois" class="btn btn-outline-secondary btn-outline-custom">
            <i class="bi bi-briefcase"></i> Emplois IT
        </a>
    </div>
@endsection
