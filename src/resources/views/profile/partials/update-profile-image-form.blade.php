<section>
    <header>
        <h2 class="text-content-text text-lg font-medium">
            {{ __('Update Profile Image') }}
        </h2>
        <p class="mt-1 text-sm text-gray-300">
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
            'style' =>
                'h-80 w-80 border-4 border-green-500 rounded-full bg-gray-200 object-cover',
            'placeholder' => 'storage/files/images/starfish.svg',
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
                class="block w-full text-slate-100 file:mr-4 file:rounded-full file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:font-semibold file:text-violet-700 hover:file:bg-violet-100"
                id="profile_image" name="profile_image" type="file"
                accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" />
        </label>

        {{-- replace the error msg from image file to image. can be implemented as blade template --}}
        @if ($errors->has('image_file'))
            @foreach ($errors->get('image_file') as $error)
                <div class="space-y-1 text-sm text-red-600">
                    {{ str_replace('image file', 'image', $error) }}
                </div>
            @endforeach
        @endif {{-- Display validation errors for info file --}}

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-image-updated')
                <p class="text-sm text-gray-600" x-data="{ show: true }"
                    x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)">
                    {{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
