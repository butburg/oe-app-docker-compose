<x-mail::message>
# Hi {!! $ownerName !!},

**{!! $commenterName !!}** commented on your image **{!! $postTitle !!}**:

> {!! $excerpt !!}

<x-mail::button :url="$url">
View the comment
</x-mail::button>

<x-slot:notice>
You get this mail because someone commented on your image. [Change mail settings]({{ $settingsUrl }})
</x-slot:notice>
</x-mail::message>
