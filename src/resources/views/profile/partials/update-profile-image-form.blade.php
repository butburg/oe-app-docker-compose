<section>
    <header>
        <h2 class="text-lg font-semibold text-c-text">
            {{ __('Update Profile Image') }}
        </h2>
        <p class="mt-1 text-sm leading-6 text-c-muted">
            {{ __('Make yourself better reconizable to others.') }}
            @if ($user->image)
                Your actual image:
            @endif
        </p>
    </header>
    {{-- Display existing image if it exists --}}
    <div class="my-2">
        @include('components.image_or_placeholder', [
            'image' => $user->image,
            'size_type' => 'l',
            'alt_title' => 'Your Profile Image',
            'style' => 'my-3 h-80 w-80 rounded-full border-2 border-c-line bg-c-surface-raised object-cover',
            'placeholder' => 'images/starfish.svg',
        ])
    </div>
    <form method="POST" action="{{ route('profile.updateImage') }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Image input field --}}
        <label class="object- my-4 block">
            <span class="sr-only">Choose profile image to upload</span>
            {{-- Screen reader text --}}
            <input
                class="block w-full min-w-0 text-sm text-c-muted file:mr-4 file:rounded-md file:border-0 file:bg-c-surface-raised file:px-3 file:py-2 file:text-sm file:font-medium file:text-c-text hover:file:bg-c-line"
                id="profile_image" name="profile_image" type="file"
                accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" />
        </label>

        {{-- replace the error msg from image file to image. can be implemented as blade template --}}
        @if ($errors->has('image_file'))
            @foreach ($errors->get('image_file') as $error)
                <div class="space-y-1 text-sm text-rose-300">
                    {{ str_replace('image file', 'image', $error) }}
                </div>
            @endforeach
        @endif {{-- Display validation errors for info file --}}

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-image-updated')
                <p class="text-sm text-c-accent" x-data="{ show: true }"
                    x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">
                    {{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
