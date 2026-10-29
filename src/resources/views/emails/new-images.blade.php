<x-mail::message>
# New in the gallery

**{!! $uploaderText !!}** published {{ $count }} new {{ $images }}:

@foreach ($titleTexts as $title)
- {!! $title !!}
@endforeach
@if ($more > 0)
- and {{ $more }} more
@endif

<x-mail::button :url="route('dashboard')">
Open the gallery
</x-mail::button>

<x-slot:notice>
You get this mail because new images were published on {{ config('app.name') }}. [Change mail settings]({{ $settingsUrl }})
</x-slot:notice>
</x-mail::message>
