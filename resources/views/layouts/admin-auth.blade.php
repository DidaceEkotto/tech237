<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administration - Connexion' }} | Tech237</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/logo.svg') }}">

    <!-- Local Poppins Font, Bootstrap 5 and Bootstrap Icons (100% Offline) -->
    <link rel="stylesheet" href="{{ asset('assets/css/poppins.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    @livewireStyles
</head>
<body class="bg-dark d-flex align-items-center justify-content-center min-vh-100" style="background: linear-gradient(135deg, #0d0d12 0%, #171822 100%) !important;">

    <div class="container py-4">
        {{ $slot }}
    </div>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    @livewireScripts
</body>
</html>
