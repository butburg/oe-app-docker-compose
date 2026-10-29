<x-mail::message>
# Hi {{ $name }},

you want to use this address for your account on {{ config('app.name') }}. Open the link below to confirm it.

<x-mail::button :url="$url">
Use this email
</x-mail::button>

The link expires in {{ $minutes }} minutes. Until you open it, your old email address stays active. If this was not you, just ignore this mail.

Thanks,<br>
{{ config('app.name') }}

<x-slot:notice>
You get this mail because this address was entered on [{{ config('app.name') }}]({{ config('app.url') }}).
</x-slot:notice>
</x-mail::message>
