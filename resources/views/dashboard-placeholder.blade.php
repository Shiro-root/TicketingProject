@extends('layouts.app')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xs">Halo, {{ auth()->user()->name }} 👋</h1>
        <p class="text-body-md text-mute mb-xl">
            Anda masuk sebagai <strong class="text-ink dark:text-on-dark">{{ auth()->user()->role?->name }}</strong>.
            Modul Dashboard (statistik, grafik, widget) akan hadir di Modul 3.
        </p>

        <div class="bg-canvas dark:bg-black/20 rounded-md p-xl border border-hairline dark:border-white/10">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-md">Modul 2 — Authentication ✅</h2>
            <ul class="text-body-md text-body dark:text-on-dark-mute list-disc list-inside space-y-xxs">
                <li>Login, Logout, Remember Me</li>
                <li>Forgot Password / Reset Password</li>
                <li>Change Password</li>
                <li>Profile + Avatar Upload</li>
                <li>RBAC middleware (role &amp; permission)</li>
                <li>Audit log untuk login/logout/update profil</li>
            </ul>
        </div>
    </div>
@endsection
