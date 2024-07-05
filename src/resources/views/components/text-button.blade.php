@props(['formId' => '', 'buttonType' => 'submit'])

@if ($buttonType === 'editToggle')
    <button class="rounded p-2 text-c-secondary hover:text-c-secondary/80 hover:underline" type="button"
        @click="isEditing = !isEditing">
        <span x-show="!isEditing">Edit</span>
        <span x-show="isEditing">Cancel</span>
    </button>
@else
    <button class="rounded p-2 text-c-text hover:text-c-text/80 hover:underline" form="{{ $formId }}"
        type="submit">{{ $slot }}</button>
@endif
