@extends('layouts.app')

@section('title', 'Daftar - StomaCare')

@section('content')
<main class="min-h-screen pt-32 pb-margin px-gutter flex items-center justify-center">
    <div class="max-w-container-max w-full grid grid-cols-1 lg:grid-cols-12 gap-gutter items-center">
        <div class="lg:col-span-6 hidden lg:block pr-margin">
            <div class="space-y-6">
                <h1 class="font-headline-xl text-headline-xl text-primary leading-tight">Mulai Perjalanan Kesehatan Anda Bersama Kami.</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-lg">
                    Bergabunglah dengan komunitas StomaCare untuk mendapatkan analisa kesehatan pencernaan yang mendalam, dipandu oleh teknologi klinis yang menenangkan.
                </p>
                <div class="relative rounded-xl overflow-hidden aspect-video soft-shadow">
                    <img class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDe8MjuwHRjWRyNUtNTtI-Ap1ZimoO6d9gLlP5jNo7H-7J-T_2yVmhNuYsI-aXHXkPJ1hq2tuxELVch2itMmoTg_znDHnD1EVbVaOQq-77rS3UxPCBSAdgnloZMK2nQdPvci0gq4VNfkZNoaVENkJSDyvkkg2kFDCTjQ_auQxq3WMTf4d5UqmfJdnGRr6tX5ch1QCMlW_A0rH_-Fc2GpR79FGlYyTQOmmHKuQWx-uOysM4bRlNczWIwV7B2fUOY5zi4ZJQXCIPOGZ6E"/>
                </div>
            </div>
        </div>
        <div class="lg:col-span-6 flex justify-center">
            <div class="bg-surface-container-lowest p-8 md:p-12 rounded-xl soft-shadow w-full max-w-[520px]">
                <div class="mb-8">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Buat Akun</h2>
                    <p class="font-body-md text-on-surface-variant">Lengkapi data diri Anda untuk memulai.</p>
                </div>
                <form class="space-y-6" action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="font-label-sm text-on-surface-variant ml-1">Nama Lengkap</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">person</span>
                            <input class="w-full pl-12 pr-4 py-3.5 bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-outline-variant" placeholder="Masukkan nama lengkap" type="text" name="name" required/>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-label-sm text-on-surface-variant ml-1">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">mail</span>
                            <input class="w-full pl-12 pr-4 py-3.5 bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-outline-variant" placeholder="nama@email.com" type="email" name="email" required/>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="font-label-sm text-on-surface-variant ml-1">Kata Sandi</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock</span>
                                <input class="w-full pl-12 pr-4 py-3.5 bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-outline-variant" placeholder="••••••••" type="password" name="password" required/>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="font-label-sm text-on-surface-variant ml-1">Konfirmasi</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">lock_reset</span>
                                <input class="w-full pl-12 pr-4 py-3.5 bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all placeholder:text-outline-variant" placeholder="••••••••" type="password" name="password_confirmation" required/>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 py-2">
                        <input class="mt-1 w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary focus:ring-offset-0 transition-all cursor-pointer" id="terms" type="checkbox" required/>
                        <label class="font-label-sm text-on-surface-variant leading-relaxed cursor-pointer" for="terms">
                            Saya menyetujui <a class="text-primary hover:underline" href="#">Syarat & Ketentuan</a> serta <a class="text-primary hover:underline" href="#">Kebijakan Privasi</a> StomaCare.
                        </label>
                    </div>
                    <button class="w-full py-4 bg-primary text-on-primary rounded-full font-headline-md text-body-md hover:bg-primary/90 active:scale-[0.98] transition-all shadow-lg shadow-primary/20" type="submit">
                        Daftar
                    </button>
                </form>
                <div class="mt-8 text-center">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Sudah punya akun? 
                        <a class="text-primary font-bold hover:underline" href="{{ url('/login') }}">Masuk Sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
