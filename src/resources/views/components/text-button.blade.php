@props(['formId' => '', 'buttonType' => 'submit'])

@if ($buttonType === 'editToggle')
    <button class="inline-flex min-h-8 items-center rounded-md px-2 text-sm font-medium text-c-muted transition-colors hover:bg-c-surface-raised hover:text-c-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary" type="button"
        @click="isEditing = !isEditing">
        <span x-show="!isEditing">Edit</span>
        <span x-show="isEditing">Cancel</span>
    </button>
@else
    <button  {{$attributes}} form="{{ $formId }}"
        type="submit">{{ $slot }}</button>
@endif
