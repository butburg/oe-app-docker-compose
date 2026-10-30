<x-mail::message>
@foreach ($announcementHtml as $html)
{!! $html !!}

@if (!$loop->last || $commentPosts || $uploaders)
<hr class="divider">

@endif
@endforeach
@if ($commentPosts)
# New comments on your images

@foreach ($commentPosts as $post)
**{!! $post['names'] !!}** commented on **{!! $post['title'] !!}**:

@foreach ($post['comments'] as $comment)
> @if (count($post['comments']) > 1)**{!! $comment['name'] !!}:** @endif{!! $comment['text'] !!}

@endforeach
[View in the gallery]({{ $post['url'] }})

@endforeach
@if ($uploaders)
<hr class="divider">

@endif
@endif
@if ($uploaders)
# Fresh images in the gallery

@foreach ($uploaders as $uploader)
{{ $loop->first ? 'Hey! ' : '' }}**{!! $uploader['name'] !!}** just uploaded {{ $uploader['count'] === 1 ? 'a new image' : "{$uploader['count']} new images" }}:

@foreach ($uploader['titles'] as $title)
- {!! $title !!}
@endforeach
@if ($uploader['more'] > 0)
- and {{ $uploader['more'] }} more
@endif

@endforeach
Go check {{ $imageCount === 1 ? 'it' : 'them' }} out:
@endif
@if ($commentPosts || $uploaders)

<x-mail::button :url="route('dashboard')">
Open the gallery
</x-mail::button>
@endif

@if ($switchedOn !== '')
<x-slot:notice>
You get this mail because mails about {{ $switchedOn }} are switched on. Don't want them? [Change your mail settings]({{ $settingsUrl }})
</x-slot:notice>
@endif
</x-mail::message>
