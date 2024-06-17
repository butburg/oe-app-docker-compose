<textarea
@keydown.enter.prevent="$event.target.closest('form').submit()"    
class="border rounded p-2 text-sm overflow-hidden resize-none w-full @error('comment', 'form_{{ $image->id }}') is-invalid @enderror"
    name="comment" placeholder="{{ $placeholder }}" rows="1"
    onfocus="this.style.height = ''; this.style.height = this.scrollHeight + 2 + 'px'"
    oninput="this.style.height = ''; this.style.height = this.scrollHeight + 2 + 'px'">{{ $slot }}</textarea>
