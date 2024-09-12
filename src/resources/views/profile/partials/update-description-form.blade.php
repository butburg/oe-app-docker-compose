<section>
    <header>
        <h2 class="text-lg font-medium text-content-text">
            {{ __('Update Description') }}
        </h2>

        <p class="mt-1 text-sm text-gray-300">
            {{ __('Write a short description about yourself.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.updateDescription') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $user->description) }}</textarea>
            <x-input-error :messages="$errors->updateDescription->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'description-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
