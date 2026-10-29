<x-mail::message>
{!! $html !!}

<x-slot:notice>
Important message from the {{ config('app.name') }} admins. These mails cannot be switched off.
</x-slot:notice>
</x-mail::message>
