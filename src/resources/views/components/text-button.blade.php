@props(['formId' => '', 'buttonType' => 'submit'])

@if ($buttonType === 'editToggle')
    <button class="rounded p-2 text-c-primary hover:text-c-primary/80 hover:underline" type="button"
        @click="isEditing = !isEditing">
        <span x-show="!isEditing">Edit</span>
        <span x-show="isEditing">Cancel</span>
    </button>
@else
    <button  {{$attributes}} form="{{ $formId }}"
        type="submit">{{ $slot }}</button>
@endif
