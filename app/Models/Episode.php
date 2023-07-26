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
            'notes' => '<p>Jake and Michael discuss all the latest Laravel releases, tutorials, and happenings in the community.</p><p>This episode is sponsored by <a href=\"https://honeybadger.io/?ref=laravelnewspodcast\">Honeybadger</a> - combining error monitoring, uptime monitoring and check-in monitoring into a single, easy to use platform and making you a DevOps hero.</p><p></p><ul><li>(03:37) - Laravel 10.14 released</li><li>(11:41) - Upcoming Livewire v3 features and changes</li><li>(17:01) - Pines: An Alpine and Tailwind UI library</li><li>(20:36) - JetBrains announced a bundle for Laravel Developers: PhpStorm + Laravel Idea plugin</li><li>(24:03) - Download the response of an HTTP request in Laravel</li><li>(28:11) - Raw query output with bindings is coming to Laravel 10</li><li>(30:00) - Generate code in Laravel with Synth</li><li>(31:59) - Writing and debugging Eloquent queries with Tinkerwell</li><li>(35:18) - Sponsor: Honeybadger</li><li>(36:13) - ChatGPT mock API generator for Laravel</li><li>(38:48) - Diving into Cross-Origin Resource Sharing</li><li>(39:42) - API authentication in Laravel</li></ul>',
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
