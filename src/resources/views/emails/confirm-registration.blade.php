<x-mail::message>
# Hi {{ $name }},

open the link below to create your account on {{ config('app.name') }}.

<x-mail::button :url="$url">
Create my account
</x-mail::button>

The link works once and expires in {{ $minutes }} minutes. If you did not sign up, just ignore this mail; no account will be created.

Thanks,<br>
{{ config('app.name') }}

<x-slot:notice>
You get this mail because this address was entered on [{{ config('app.name') }}]({{ config('app.url') }}).
</x-slot:notice>
</x-mail::message>
