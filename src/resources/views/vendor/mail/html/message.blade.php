{{-- $notice: optional footer line of the mail, e.g. why it was sent or a link to the mail settings --}}
<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')" />
</x-slot:header>

{{ $slot }}

@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

<x-slot:footer>
<x-mail::footer>
@isset($notice)
{{ $notice }}
@else
You get this mail because you have an account on [{{ config('app.name') }}]({{ config('app.url') }}).
@endisset
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
