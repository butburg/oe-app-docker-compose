<div x-data="{ expanded: false }">
    <!-- Slot for the comment text -->
    <p x-ref="comment" class="text-gray-700 truncate" x-bind:class="{ 'truncate': !expanded }">
        {{ $slot }}
    </p>
    <!-- Button to expand/collapse text -->
    <button @click="expanded = !expanded" 
            class="text-blue-500 hover:underline mt-1">
            <span x-show="!expanded">more</span>
            <span x-show="expanded">less</span>
    </button>
</div>