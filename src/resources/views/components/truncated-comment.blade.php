<p x-data="{ isCollapsed: false, maxLength: 72, originalContent: '', content: '' }" x-init="originalContent = $el.firstElementChild.textContent.trim();
content = originalContent.slice(0, maxLength)">
    <span x-text="isCollapsed ? originalContent : content">
        {{ $slot }}
    </span><span x-show="!isCollapsed && originalContent.length > maxLength">...</span>
    <button class="text-c-primary hover:underline"
    @click="isCollapsed = !isCollapsed" x-show="originalContent.length > maxLength"
        x-text="isCollapsed ? '' : 'more'"></button>
</p>
