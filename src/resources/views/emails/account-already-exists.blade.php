<x-mail::message>
# You already have an account

Someone (probably you) tried to register on {{ config('app.name') }} with this email address, but an account with it already exists.

<x-mail::button :url="route('login')">
Log in
</x-mail::button>

Forgot your password? You can [reset it here]({{ route('password.request') }}). If this was not you, you can ignore this mail.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
