@extends('errors.layout')

@section('title', 'Erreur serveur (500)')
@section('message', 'Une erreur inattendue est survenue du côté de notre serveur.')

@section('content')
    <div class="display-1 fw-bold mb-2 font-monospace" style="color: #dc3545; letter-spacing: -2px;">500</div>
    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex p-3 fs-2 mb-3">
        <i class="bi bi-cpu-fill"></i>
    </div>
    <h3 class="fw-bold text-dark mb-2">Erreur interne du serveur</h3>
    <p class="text-muted lead mb-4 fs-6">
        Notre équipe technique a été automatiquement informée. Veuillez nous excuser pour la gêne occasionnée.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="/" class="btn-tech">
            <i class="bi bi-arrow-clockwise"></i> Recharger la page d'accueil
        </a>
    </div>
@endsection
