{{-- app name with the first word in the accent color, e.g. "Weedy" Gallery --}}
@php
    [$first, $rest] = array_pad(explode(' ', config('app.name'), 2), 2, '');
@endphp

<span {{ $attributes }}><span class="text-c-accent">{{ $first }}</span>@if ($rest !== '') {{ $rest }}@endif</span>
