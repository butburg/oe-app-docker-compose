@props(['url'])
@php
    // same split as x-app-wordmark: first word in the accent colour
    [$first, $rest] = array_pad(explode(' ', config('app.name'), 2), 2, '');
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" class="brand">
<img src="{{ App\Mail\MailAssets::LOGO_CID }}" class="logo" width="48" height="48" alt="{{ config('app.name') }}">
<br>
<span class="accent">{{ $first }}</span>@if ($rest !== '') {{ $rest }}@endif
</a>
</td>
</tr>
