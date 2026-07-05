<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sedang Pemeliharaan — Helpdesk Enterprise</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
    <style>
        /* Fallback murni CSS kalau build Vite belum tersedia saat down — halaman ini
           harus tetap tampil rapi tanpa bergantung asset apapun. */
        body.fallback {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #fbfbf9; font-family: -apple-system, 'Segoe UI', sans-serif; color: #211922;
        }
        .fallback .card { max-width: 420px; text-align: center; padding: 32px; }
        .fallback h1 { font-size: 28px; margin: 0 0 8px; color: #000; }
        .fallback p { color: #62625b; font-size: 16px; line-height: 1.4; }
        .fallback .brand { color: #e60023; font-weight: 700; margin-bottom: 24px; display: block; font-size: 18px; }
    </style>
</head>
<body class="fallback bg-surface-soft">
    <div class="card flex flex-col items-center gap-lg">
        <span class="brand text-heading-md text-primary">Helpdesk Enterprise</span>
        <div style="font-size:48px;">🛠️</div>
        <h1 class="text-heading-lg text-ink">Sedang Dalam Pemeliharaan</h1>
        <p class="text-body-md">
            {{ cache('maintenance_message', 'Aplikasi sedang dalam pemeliharaan terjadwal. Silakan coba beberapa saat lagi.') }}
        </p>
    </div>
</body>
</html>
