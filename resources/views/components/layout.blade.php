<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Laravel News Podcast Player</title>
        <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    </head>
    <body class="min-h-screen bg-gray-50 font-sans text-black antialiased">
        <div class="mx-auto max-w-2xl px-6 py-24">
            @persist('logo')
                <a
                    href="/episodes"
                    wire:navigate
                    class="mx-auto flex max-w-max items-center gap-3 font-bold text-[#FF2D20] transition hover:opacity-80"
                >
                    <img
                        src="/images/logo.svg"
                        alt="Laravel News"
                        class="mx-auto w-12"
                    />
                    <span>Laravel News Podcast</span>
                </a>
            @endpersist

            <div class="py-10">
                {{ $slot }}
            </div>
        </div>

        @persist('player')
            <x-episode-player />
        @endpersist
    </body>
</html>
