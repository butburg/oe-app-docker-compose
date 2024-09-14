<div class="flex items-center">
    <input id="{{ $id }}" name="{{ $name }}" type="checkbox" value="1"
        {{ old($name, $checked ?? false) ? 'checked' : '' }}
        class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500" />

    @if(isset($label))
        <label for="{{ $id }}" class="ms-2 text-sm ">
            {!! $label !!}
        </label>
    @endif
</div>
