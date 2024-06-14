@props(['action', 'method', 'formId'])

<form action="{{ $action }}" method="POST" id="{{ $formId }}"
@if($method === 'DELETE')
        onsubmit="return confirm('Are you sure you want to delete this comment?');"
    @endif class="flex items-center w-full">
    @csrf
    @if ($method)
        @method($method)
    @endif
    {{ $slot }}
</form>