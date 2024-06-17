@props(['color' => 'blue', 'formId' => '', 'buttonType' => 'submit'])

@if ($buttonType === 'editToggle')
    <button @click="isEditing = !isEditing"
    class="p-2 text-{{ $color }}-500 hover:bg-{{ $color }}-100 rounded"
    type="button">
    <span x-show="!isEditing">Edit</span>
    <span x-show="isEditing">Cancel</span>
    </button>
@else
    <button form="{{ $formId }}" class="p-2 text-{{ $color }}-500 hover:bg-{{ $color }}-100 rounded"
        type="submit">{{ $slot }}</button>
@endif
