<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'StomaCare - Serenity for Your Digestive Health')</title>

    <!-- Tailwind & Plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <!-- Tailwind Custom Config -->
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "error-container": "#ffdad6",
                    "on-secondary-container": "#00743a",
                    "on-background": "#191c1e",
                    "inverse-surface": "#2d3133",
                    "on-secondary": "#ffffff",
                    "primary-container": "#0070ea",
                    "on-error-container": "#93000a",
                    "secondary": "#006d37",
                    "on-surface": "#191c1e",
                    "on-primary-fixed-variant": "#004493",
                    "surface-container-lowest": "#ffffff",
                    "surface-container": "#eceef0",
                    "primary": "#0059bb",
                    "on-surface-variant": "#414754",
                    "on-primary-container": "#fefcff",
                    "on-tertiary-fixed-variant": "#384a48",
                    "inverse-on-surface": "#eff1f3",
                    "primary-fixed-dim": "#adc7ff",
                    "outline": "#717786",
                    "inverse-primary": "#adc7ff",
                    "surface-tint": "#005bc0",
                    "on-tertiary-fixed": "#0d1e1d",
                    "error": "#ba1a1a",
                    "on-tertiary": "#ffffff",
                    "on-tertiary-container": "#f3fffd",
                    "surface-variant": "#e0e3e5",
                    "surface-container-high": "#e6e8ea",
                    "secondary-fixed": "#6bfe9c",
                    "on-secondary-fixed": "#00210c",
                    "on-secondary-fixed-variant": "#005228",
                    "secondary-container": "#6bfe9c",
                    "surface-bright": "#f7f9fb",
                    "secondary-fixed-dim": "#4ae183",
                    "tertiary": "#4d5f5d",
                    "surface-container-highest": "#e0e3e5",
                    "outline-variant": "#c1c6d7",
                    "tertiary-fixed-dim": "#b7cac8",
                    "on-primary": "#ffffff",
                    "primary-fixed": "#d8e2ff",
                    "tertiary-fixed": "#d3e7e4",
                    "background": "#f7f9fb",
                    "surface-container-low": "#f2f4f6",
                    "on-error": "#ffffff",
                    "tertiary-container": "#667876",
                    "surface-dim": "#d8dadc",
                    "surface": "#f7f9fb",
                    "on-primary-fixed": "#001a41"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "spacing": {
                    "unit": "8px",
                    "gutter": "24px",
                    "section-gap": "120px",
                    "margin": "64px",
                    "container-max": "1280px"
            },
            "fontFamily": {
                    "headline-lg": ["Manrope"],
                    "label-sm": ["Inter"],
                    "body-lg": ["Inter"],
                    "headline-xl": ["Manrope"],
                    "headline-md": ["Manrope"],
                    "body-md": ["Inter"]
            },
            "fontSize": {
                    "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                    "label-sm": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.02em", "fontWeight": "500"}],
                    "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                    "headline-xl": ["48px", {"lineHeight": "1.2", "fontWeight": "700"}],
                    "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                    "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>

    <style>
        html {
            scroll-behavior: smooth;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .soft-shadow {
            box-shadow: 0px 4px 16px rgba(0, 123, 255, 0.05);
        }
        [v-cloak] { display: none; }
    </style>

    @stack('styles')
</head>
<body class="bg-background text-on-background font-body-md selection:bg-primary-fixed selection:text-on-primary-fixed">
    
    @include('layouts.partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
