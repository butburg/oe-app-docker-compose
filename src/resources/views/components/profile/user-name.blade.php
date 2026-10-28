<!-- Name and Former Name  -->
@if ($user->getFormerNameIfApplicable())
    <p class="mx-0 -mb-1 truncate px-0 text-sm text-c-quiet">
        <small>Formerly: {{ $user->getFormerNameIfApplicable() }}</small>
    </p>
@else
    <!-- Add a placeholder element to reserve space -->
    <p class="x-0 invisible -mb-1 text-sm text-c-quiet">
        <small>Placeholder</small>
    </p>
@endif
<h4 class="text-lg font-semibold text-c-text" title="{{ $user->name }}">
    {{ Str::limit($user->name, config('app.truncate_name'), '...') }}
</h4>
