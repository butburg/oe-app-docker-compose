<textarea
    class="@error('comment', 'form_{{ $image->id }}') is-invalid @enderror w-full resize-none overflow-hidden rounded border p-2 text-sm"
    name="comment" @keydown.enter.prevent="$event.target.closest('form').submit()"
    onfocus="this.style.height = ''; this.style.height = this.scrollHeight + 2 + 'px'"
    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 2 + 'px'" placeholder="{{ $placeholder }}"
    rows="1">
    {{ $slot }}
</textarea>
