<!-- Top Navigation Bar -->
<nav class="fixed top-0 w-full z-50 bg-surface dark:bg-on-background shadow-[0px_4px_16px_rgba(0,123,255,0.05)] h-20 transition-all duration-300">
    <div class="max-w-container-max mx-auto px-gutter flex justify-between items-center h-full">
        <div class="text-headline-md font-headline-lg font-bold text-primary dark:text-primary-fixed-dim">
            <a href="{{ url('/') }}">StomaCare</a>
        </div>
        <div class="hidden md:flex items-center gap-8">
            <a class="{{ request()->is('/') ? 'text-primary dark:text-primary-fixed-dim border-b-2 border-primary dark:border-primary-fixed-dim' : 'text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim' }} pb-1 font-semibold font-body-md transition-colors duration-200" href="{{ url('/') }}">Home</a>
            <a class="{{ request()->is('analysis') ? 'text-primary dark:text-primary-fixed-dim border-b-2 border-primary' : 'text-on-surface-variant dark:text-outline-variant hover:text-primary' }} font-medium font-body-md hover:text-primary dark:hover:text-primary-fixed-dim transition-colors duration-200" href="{{ url('/analysis') }}">Analisa</a>
            <a class="{{ request()->is('history') ? 'text-primary dark:text-primary-fixed-dim border-b-2 border-primary' : 'text-on-surface-variant dark:text-outline-variant hover:text-primary' }} font-medium font-body-md hover:text-primary dark:hover:text-primary-fixed-dim transition-colors duration-200" href="{{ url('/history') }}">Riwayat</a>
        </div>
        <div class="flex items-center gap-4">
            @auth
                <form action="{{ route('logout') }}" method="POST" class="inline-flex items-center">
                    @csrf
                    <button type="submit" class="border border-error/30 text-error hover:bg-error hover:text-white px-6 py-2 rounded-full font-semibold text-label-sm hover:shadow-[0px_4px_12px_rgba(186,26,26,0.1)] transform translate-y-[4px] active:scale-95 duration-200 ease-in-out transition-all">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ url('/login') }}" class="text-primary font-medium hover:underline">Masuk</a>
                <a href="{{ url('/register') }}" class="bg-primary text-on-primary px-6 py-2 rounded-full font-label-sm text-label-sm hover:opacity-90 active:scale-95 duration-200 ease-in-out transition-all">
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</nav>
