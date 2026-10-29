<x-mail::message>
# Hi {{ $name }},

open the link below to create your account on {{ config('app.name') }}.

<x-mail::button :url="$url">
Create my account
</x-mail::button>

The link works once and expires in {{ $minutes }} minutes. If you did not sign up, just ignore this mail; no account will be created.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
