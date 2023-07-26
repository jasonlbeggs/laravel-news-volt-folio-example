# Building a podcast player with Livewire v3, Volt, and Folio

Yesterday, the Laravel team released [Laravel Folio](https://twitter.com/taylorotwell/status/1683856862352125952) - a powerful page-based router designed to simplify routing in Laravel applications. Today, they [released Volt](https://twitter.com/taylorotwell/status/1684243932820176896) -an elegantly crafted functional API for Livewire, allowing a component's PHP logic and Blade templates to coexist in the same file with reduced boilerplate.

Although they may be used separately, I think using them together is a new, incredibly productive way to build Laravel apps.

In this article, I'm going to teach you how to build a simple app that lists out episodes of the Laravel News podcast and allows you to play them, with a player that can seamlessly continue playing across page loads.

## Setup

To get started, we need to create a new Laravel app and install Livewire, Volt, Folio, and Sushi (to create some dummy data).

```
laravel new

composer require livewire/livewire:^3.0@beta livewire/volt:^1.0@beta laravel/folio:^1.0@beta calebporzio/sushi
```

> Livewire v3, Volt, and Folio are all still currently in beta. They should be pretty stable, but use at your own risk.

After requiring the packages, we need to run `php artisan volt:install` and `php artisan folio:install`. This will scaffold out some folder and service providers Volt and Folio need.

## The `Episode` model

For dummy data, I'm going to create a Sushi model. [Sushi](https://github.com/calebporzio/sushi) is a package written by Caleb Pozio that allows you to create Eloquent models that query their data from an array written directly in the model file. This works great when you're building example apps or when you have data that doesn't need to change very often.

Create a model, then remove the `HasFactory` trait and replace it with the `Sushi` trait. I added the details of the 4 latest Laravel News Podcast episodes as the data for this example.

I won't go into detail on how all this works since this isn't the point of the article and you'll likely use a real Eloquent model if you were to build your own podcast player.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Episode extends Model
{
    use Sushi;

    protected $casts = [
        'released_at' => 'datetime',
    ];

    protected $rows = [
        [
            'number' => 195,
            'title' => 'Queries, GPT, and sinking downloads',
            'notes' => '...',
            'audio' => 'https://media.transistor.fm/c28ad926/93e5fe7d.mp3',
            'image' => 'https://images.transistor.fm/file/transistor/images/show/6405/full_1646972621-artwork.jpg',
            'duration_in_seconds' => 2579,
            'released_at' => '2023-07-06 10:00:00',
        ],
        [
            'number' => 194,
            'title' => 'Squeezing lemons, punching cards, and bellowing forges',
            'notes' => '...',
            'audio' => 'https://media.transistor.fm/6d2d53fe/f70d9278.mp3',
            'image' => 'https://images.transistor.fm/file/transistor/images/show/6405/full_1646972621-artwork.jpg',
            'duration_in_seconds' => 2219,
            'released_at' => '2023-06-21 10:00:00',
        ],
        [
            'number' => 193,
            'title' => 'Precognition, faking Stripe, and debugging Blade',
            'notes' => '...',
            'audio' => 'https://media.transistor.fm/d434305e/975fbb28.mp3',
            'image' => 'https://images.transistor.fm/file/transistor/images/show/6405/full_1646972621-artwork.jpg',
            'duration_in_seconds' => 2146,
            'released_at' => '2023-06-06 10:00:00',
        ],
        [
            'number' => 192,
            'title' => 'High octane, sleepy code, and Aaron Francis',
            'notes' => '...',
            'audio' => 'https://media.transistor.fm/b5f81577/c58c90c8.mp3',
            'image' => 'https://images.transistor.fm/file/transistor/images/show/6405/full_1646972621-artwork.jpg',
            'duration_in_seconds' => 1865,
            'released_at' => '2023-05-24 10:00:00',
        ],
        // ...
    ];
}
```

## The layout view

We'll need a layout file to load Tailwind, add a logo, and add some basic styling. Since Livewire and Alpine automatically inject their scripts and styles now, we don't even need to load those in the layout! We'll create the layout as an anonymous Blade component at `resources/views/components/layout.blade.php`.

```html
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
            <a
                href="/episodes"
                class="mx-auto flex max-w-max items-center gap-3 font-bold text-[#FF2D20] transition hover:opacity-80"
            >
                <img
                    src="/images/logo.svg"
                    alt="Laravel News"
                    class="mx-auto w-12"
                />
                <span>Laravel News Podcast</span>
            </a>

            <div class="py-10">{{ $slot }}</div>
        </div>
    </body>
</html>
```

## The episode list page

Using Folio, we can easily create a new page in the `resources/views/pages` directory, and Laravel will automatically create a route for that page. We want our route to be `/episodes`, so we can run `php artisan make:folio episodes/index`. That will create a blank view at `resources/views/pages/episodes/index.blade.php`.

On this page, we'll insert the layout component, then loop over all the podcast episodes. Volt provides namespaced functions for most of the Livewire features. Here, we'll open regular `<?php ?>` open and close tags. I<a href="/episodes/{{ $episode->number }}">nside those, we'll use the [`computed`](https://livewire.laravel.com/docs/volt#computed-properties) function to create an `$episodes` variable that runs a query to get all the Episode models (`$episodes = computed(fn () => Episode::get());`). We can access the computed property in the template using `$this->episodes`.

I also created a `$formatDuration` variable that's a function to format each episode's `duration_in_seconds` property to a readable format. We can call that function in the template using `$this->formatDuration($episode->duration_in_seconds)`.

We also need to wrap the dynamic functionality on the page in the `@volt` directive to register it as an "[anonymous Livewire component](https://livewire.laravel.com/docs/volt#anonymous-components)" within the Folio page.

```blade
<?php

use App\Models\Episode;
use Illuminate\Support\Stringable;
use function Livewire\Volt\computed;
use function Livewire\Volt\state;

$episodes = computed(fn () => Episode::get());

$formatDuration = function ($seconds) { // [tl! collapse:start]
    return str(date('G\h i\m s\s', $seconds))
        ->trim('0h ')
        ->explode(' ')
        ->mapInto(Stringable::class)
        ->each->ltrim('0')
        ->join(' ');
}; // [tl! collapse:end]

?>

<x-layout>
    @volt
        <div class="rounded-xl border border-gray-200 bg-white shadow">
            <ul class="divide-y divide-gray-100">
                @foreach ($this->episodes as $episode)
                    <li
                        wire:key="{{ $episode->number }}"
                        class="flex flex-col items-start gap-x-6 gap-y-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2>
                                No. {{ $episode->number }} - {{ $episode->title }}
                            </h2>
                            <div
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500"
                            >
                                <p>
                                    Released:
                                    {{ $episode->released_at->format('M j, Y') }}
                                </p>
                                &middot;
                                <p>
                                    Duration:
                                    {{ $this->formatDuration($episode->duration_in_seconds) }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="flex shrink-0 items-center gap-1 text-sm font-medium text-[#FF2D20] transition hover:opacity-60"
                        >
                            <img
                                src="/images/play.svg"
                                alt="Play"
                                class="h-8 w-8 transition hover:opacity-60"
                            />
                            <span>Play</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endvolt
</x-layout>
```

## The episode player

From there, we need to add some interactivity. I want to add an episode player so we can listen to the episodes from the episode list. This can be a regular Blade component we render in the layout file.

```
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
            <a
                href="/episodes"
                class="mx-auto flex max-w-max items-center gap-3 font-bold text-[#FF2D20] transition hover:opacity-80"
            >
                <img
                    src="/images/logo.svg"
                    alt="Laravel News"
                    class="mx-auto w-12"
                />
                <span>Laravel News Podcast</span>
            </a>

            <div class="py-10">{{ $slot }}</div>

            <x-episode-player />
        </div>
    </body>
</html>
```

We can create that component by adding a `resources/views/components/episode-player.blade.php` file. Inside the component, we'll add an `<audio>` element with some Alpine code to store the active episode and a function that updates the active episode and starts the audio. We'll also only show the player if an active episode is set and we'll add a nice fade transition to the wrapper.

```alpine
<div
    x-data="{
        activeEpisode: null,
        play(episode) {
            this.activeEpisode = episode

            this.$nextTick(() => {
                this.$refs.audio.play()
            })
        },
    }"
    x-show="activeEpisode"
    x-transition.opacity.duration.500ms
    class="fixed inset-x-0 bottom-0 w-full border-t border-gray-200 bg-white"
    style="display: none"
>
    <div class="mx-auto max-w-xl p-6">
        <h3
            x-text="`Playing: No. ${activeEpisode?.number} - ${activeEpisode?.title}`"
            class="text-center text-sm font-medium text-gray-600"
        ></h3>
        <audio
            x-ref="audio"
            class="mx-auto mt-3"
            :src="activeEpisode?.audio"
            controls
        ></audio>
    </div>
</div>
```

If we reload the page, we don't see any changes. That's because we haven't added a way to play episodes. We'll use events to communicate from our Livewire components to the player. First, in the player, we'll add `x-on:play-episode.window="play($event.detail)"` to listen for the `play-episode` event on the window then call the `play` function.

```alpine
<div
    x-data="{
        activeEpisode: null,
        play(episode) {
            this.activeEpisode = episode

            this.$nextTick(() => {
                this.$refs.audio.play()
            })
        },
    }"
    x-on:play-episode.window="play($event.detail)"
    ...
>
    <!-- ... -->
</div>
```

Next, back in the `episodes/index` page, we'll add a click listener on the play buttons for each episode. The buttons will dispatch the `play-episode` let's wrap the `<h2>`event which will be received by the episode player and han each episode's there. in an anchor tag.

```blade
<button<a href="/episodes/{{ $episode->number }}">
    x-data
    x-on:click="$dispatch('play-episode', @js($episode))"
    ...
>
    <img
        src="/images/play.svg"
        alt="Play"
        class="h-8 w-8 transition hover:opacity-60"
    />
    <span>Play</span>
</button>
```

## The episode details page

Next, I'd like to add an episode details page so we can display each episode's show notes and other details.

Folio has some pretty cool conventions for route model binding in your filenames. To make an equivalent route for `/episodes/{episode:id}`, simply create a page at `resources/views/pages/episodes/[Episode].blade.php`. To use a route parameter other than the primary key, you can use the `[Model:some_other_key].blade.php` syntax in your filename. I want to use the episode number in the URL, so we'll create a file at `resources/views/pages/episodes/[Episode:number].blade.php`.

Folio will automatically query the Episode models for an episode with the number we pass in the URL and make that available as an `$episode` variable in our `<?php ?>` code. We can then convert that to a Livewire property using Volt's `state` function.

We'll also include a play button on this page so we can play an episode while viewing its details.

```
<?php
use Illuminate\Support\Stringable;
use function Livewire\Volt\state;

state(['episode' => fn () => $episode]);

$formatDuration = function ($seconds) { // [tl! collapse:start]
    return str(date('G\h i\m s\s', $seconds))
        ->trim('0h ')
        ->explode(' ')
        ->mapInto(Stringable::class)
        ->each->ltrim('0')
        ->join(' ');
}; // [tl! collapse:end]
?>

<x-layout>
    @volt
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow">
            <div class="p-6">
                <div class="flex items-center justify-between gap-8">
                    <div>
                        <h2 class="text-xl font-medium">
                            No. {{ $episode->number }} -
                            {{ $episode->title }}
                        </h2>
                        <div
                            class="mt-1 flex items-center gap-3 text-sm text-gray-500"
                        >
                            <p>
                                Released:
                                {{ $episode->released_at->format('M j, Y') }}
                            </p>
                            &middot;
                            <p>
                                Duration:
                                {{ $this->formatDuration($episode->duration_in_seconds) }}
                            </p>
                        </div>
                    </div>

                    <button
                        x-on:click="$dispatch('play-episode', @js($episode))"
                        type="button"
                        class="flex items-center gap-1 text-sm font-medium text-[#FF2D20] transition hover:opacity-60"
                    >
                        <img
                            src="/images/play.svg"
                            alt="Play"
                            class="h-8 w-8 transition hover:opacity-60"
                        />
                        <span>Play</span>
                    </button>
                </div>
                <div class="prose prose-sm mt-4">
                    {!! $episode->notes !!}
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4">
                <a
                    href="/episodes"
                    class="text-sm font-medium text-gray-600"
                >
                    &larr; Back to episodes
                </a>
            </div>
        </div>
    @endvolt
</x-layout>
```

Now, we need to link to the details page from the index page. Back in the `episodes/index` page, let's wrap each episode's `<h2>` in an anchor tag.

```blade
@foreach ($this->episodes as $episode)
    <li
        wire:key="{{ $episode->number }}"
        class="flex flex-col items-start gap-x-6 gap-y-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <a
                href="/episodes/{{ $episode->number }}"
                class="transition hover:text-[#FF2D20]"
            >
                <h2>
                    No. {{ $episode->number }} -
                    {{ $episode->title }}
                </h2>
            </a>
        </div>
        {{-- ... --}}
    </li>
@endforeach
```

## SPA-mode

We're almost there. The app looks pretty good and functions pretty well, but there's one issue. If you're listening to an episode, and navigate to a different page, the episode player loses its active episode state and disappears.

Thankfully, Livewire has the `wire:navigate` and the `@persist` directive to help with these problems now!

In our layout file, let's wrap the logo and episode player in `@persist` blocks. Livewire will detect this and will will skip re-rendering those blocks when we change pages.

```blade
<!DOCTYPE html>
<html lang="en">
    ...
    <body class="min-h-screen bg-gray-50 font-sans text-black antialiased">
        <div class="mx-auto max-w-2xl px-6 py-24">
            @persist('logo')
                <a
                    href="/episodes"
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

            <div class="py-10">{{ $slot }}</div>

            @persist('player')
                <x-episode-player />
            @endpersist
        </div>
    </body>
</html>
```

Finally, we need to add the `wire:navigate` attribute to all the links through the app. For example:

```blade
<a
    href="/episodes/{{ $episode->number }}"
    class="transition hover:text-[#FF2D20]"
    wire:navigate
>
    <h2>
        No. {{ $episode->number }} -
        {{ $episode->title }}
    </h2>
</a>
```

When you use the `wire:navigate` attribute, behind the scenes, Livewire will fetch the new pages contents using AJAX, the magically swap out the contents in your browser without doing a full page reload. This makes page loads feel incredibly fast and enables features like persist to work! It enables features that previously you could only accomplish by building an SPA.

## Conclusion

This was a really fun demo app to build while learning Volt and Folio. I've uploaded the demo app [here](https://github.com/jasonlbeggs/laravel-news-volt-folio-example) if you want to see the full source code or want to try it out yourself!

What do you think? Is Livewire v3 + Volt + Folio the simplest stack for building Laravel apps now? Let me know on [Twitter](https://twitter.com/jasonlbeggs)!
