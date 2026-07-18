@extends('layouts.app')

@section('title', 'Masuk - StomaCare')

@section('content')
<!-- Main Content Canvas -->
<main class="pt-32 pb-margin min-h-screen flex items-center justify-center">
    <div class="max-w-container-max mx-auto px-gutter w-full grid md:grid-cols-2 gap-16 items-center">
        <!-- Left Side: Clinical Serenity Messaging -->
        <div class="hidden md:flex flex-col space-y-8">
            <div class="space-y-4">
                <h1 class="font-headline-xl text-headline-xl text-primary">Selamat Datang Kembali di StomaCare</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-md">
                    Masuk ke akun Anda untuk melanjutkan pemantauan kesehatan pencernaan dengan analisis klinis yang tenang dan akurat.
                </p>
            </div>
            <div class="relative rounded-xl overflow-hidden shadow-xl aspect-video">
                <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAIAjbC2tapdCefLUN44gKR_nbU36ZYiMMC-MZ9Gp_3S04bknLgCrQF5uEwv6xqDiT2G1Bym000o-X06ABRTKnTUWwvGpk7Jpp5rzDMOwex40OLrviB5Iyl2ij0usjIuFMEhoLm752tiBnqy8VICi5bi-IGDrhyoHa88aJRyY99EUyYxDGFd8uFBh9Aio8e23zh05FweAwswiVfTBOooGfke_RMcbMozLG8vJziJMD81Ei_x4yBOaOeE5qJKUbRXHyuVU2ZZOIMnVwI"/>
                <div class="absolute inset-0 bg-gradient-to-t from-primary/20 to-transparent"></div>
            </div>
        </div>
        <!-- Right Side: Login Form -->
        <div class="flex justify-center">
            <div class="w-full max-w-[440px] bg-surface-container-lowest p-margin rounded-xl shadow-[0px_16px_48px_rgba(0,123,255,0.05)] border border-surface-container">
                <div class="mb-8">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Masuk</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">Lanjutkan perjalanan kesehatan Anda.</p>
                </div>
                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="space-y-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant">Alamat Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">mail</span>
                            <input class="w-full pl-12 pr-4 py-3 bg-surface border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 font-body-md" placeholder="nama@email.com" type="email" name="email" required/>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant">Kata Sandi</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
                            <input class="w-full pl-12 pr-4 py-3 bg-surface border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 font-body-md" placeholder="••••••••" type="password" name="password" required/>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <a class="font-label-sm text-label-sm text-primary hover:underline transition-all" href="#">Lupa Password?</a>
                    </div>
                    <button class="w-full bg-primary text-on-primary py-4 rounded-xl font-headline-md shadow-md hover:shadow-lg transition-all active:scale-[0.98]" type="submit">
                        Masuk
                    </button>
                </form>
                <div class="mt-8 text-center">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Belum punya akun? 
                        <a class="text-primary font-bold hover:underline" href="{{ url('/register') }}">Daftar Sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
