<div x-data="{ expanded: false }">
    <p x-ref="comment" class="text-gray-700 truncate-2-lines" x-bind:class="{ 'truncate': !expanded }">
        {{ $slot }}
    </p>
    <button @click="expanded = !expanded" x-show="!expanded"
            class="text-blue-500 hover:underline mt-1">more</button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('truncatedComment', () => ({
            expanded: false
        }));
    });
</script>
