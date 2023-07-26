<?php
use Illuminate\Support\Stringable;
use function Livewire\Volt\state;

state(['episode' => fn () => $episode]);

$formatDuration = function ($seconds) {
    // [tl! collapse:start]
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
        <div
            x-data
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow"
        >
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
                    wire:navigate
                    class="text-sm font-medium text-gray-600"
                >
                    &larr; Back to episodes
                </a>
            </div>
        </div>
    @endvolt
</x-layout>
