<div class="flex items-center">
    <input id="{{ $id }}" name="{{ $name }}" type="checkbox" value="1"
        {{ old($name, $checked ?? false) ? 'checked' : '' }}
        class="h-4 w-4 rounded border-c-line bg-c-surface-raised accent-c-primary text-c-primary focus:ring-2 focus:ring-c-primary" />

    @if(isset($label))
        <label for="{{ $id }}" class="ms-2 text-sm text-c-text">
            {!! $label !!}
        </label>
    @endif
</div>
