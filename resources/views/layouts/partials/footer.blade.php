<!-- Footer -->
<footer class="bg-surface-container dark:bg-surface-container-lowest w-full py-margin">
    <div class="max-w-container-max mx-auto px-gutter flex flex-col md:flex-row justify-between items-center gap-unit">
        <div class="flex flex-col items-center md:items-start gap-4">
            <div class="text-headline-md font-headline-md text-on-surface dark:text-on-background">
                StomaCare
            </div>
            <p class="font-label-sm text-on-surface-variant dark:text-outline-variant text-center md:text-left">
                © {{ date('Y') }} StomaCare. Clinical Serenity for your Digestive Health.
            </p>
        </div>
        <div class="flex gap-8">
            <a class="font-label-sm text-on-surface-variant dark:text-outline-variant hover:text-secondary dark:hover:text-secondary-fixed-dim transition-colors opacity-80 hover:opacity-100" href="{{ route('legal') }}#privacy">Kebijakan Privasi</a>
            <a class="font-label-sm text-on-surface-variant dark:text-outline-variant hover:text-secondary dark:hover:text-secondary-fixed-dim transition-colors opacity-80 hover:opacity-100" href="{{ route('legal') }}#terms">Syarat & Ketentuan</a>
            <a class="font-label-sm text-on-surface-variant dark:text-outline-variant hover:text-secondary dark:hover:text-secondary-fixed-dim transition-colors opacity-80 hover:opacity-100" href="{{ route('legal') }}#contact">Hubungi Kami</a>
        </div>

        <div class="flex gap-4">
            <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-all cursor-pointer">
                <span class="material-symbols-outlined">share</span>
            </div>
            <div class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-white transition-all cursor-pointer">
                <span class="material-symbols-outlined">mail</span>
            </div>
        </div>
    </div>
</footer>
