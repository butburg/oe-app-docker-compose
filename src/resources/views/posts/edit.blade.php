<x-app-layout>
    {{-- Header section with 'Edit' or 'Create' depending on the existence of $post --}}
    <x-slot name="header">
        <x-header2>{{ isset($post) ? 'Edit' : 'Create' }}</x-header2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-c-primary/20 shadow-sm sm:rounded-lg">
                <div class="p-6 text-c-text">
                    {{-- Form for post creation/updation with file upload --}}
                    <form class="mt-6 space-y-6" method="post"
                        action="{{ isset($post) ? route('posts.update', $post->id) : route('posts.store') }}"
                        enctype="multipart/form-data">
                        @csrf {{-- CSRF protection --}}
                        {{-- Use PUT method for edit mode --}}
                        @isset($post)
                            @method('put')
                        @endisset

                        {{-- Post Title Input --}}
                        <div>
                            <x-input-label for="title" value="Title" /> {{-- Label for post title --}}
                            <x-text-input class="mt-1 block w-full" id="title" name="title" type="text"
                                :value="$post->title ?? old('title')" required autofocus maxlength="120" /> {{-- Input field for post title --}}
                            <x-input-error class="mt-2" :messages="$errors->get('title')" /> {{-- Display validation errors for post title --}}
                        </div>

                        {{-- Image Input --}}
                        <div>

                            {{-- Display existing image if it exists --}}
                            @isset($post->image_file)
                                <x-input-label for="image_file" value="Actual Image" /> {{-- Label for image --}}
                                <div class="my-2">
                                    <img class="h-auto max-h-96 w-full object-contain"
                                        src="{{ asset('storage/' . $post->image_file) }}" alt="Post Image">
                                </div>
                            @endisset

                            {{-- File input field --}}
                            <x-input-label for="image_file"
                                value="{{ isset($post) ? 'Replace Image (optional)' : 'Image' }}" />
                            <label class="mt-2 block">
                                <span class="sr-only">Choose image to upload</span> {{-- Screen reader text --}}
                                <input
                                    class="block w-full text-sm text-slate-100 file:mr-4 file:rounded-full file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-violet-700 hover:file:bg-violet-100"
                                    id="image_file" name="image_file" type="file"
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
                        </div>

                        <div class="text-md mb-4 flex rounded-lg bg-c-background p-4 text-blue-300" role="alert">
                            <svg class="me-3 mt-[2px] inline h-4 w-4 flex-shrink-0" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="sr-only">Info</span>
                            <div>
                                <span class="font-medium">Ensure that these requirements are met:</span>
                                <ul class="mt-1.5 list-inside list-disc">
                                    <li>Your title must be at least 3 characters long.</li>
                                    <li>After saving, you can still change the title and image.</li>
                                    <li>Don’t forget to publish your post so others can see it! 😉</li>
                                    <li>Only images with no copyright restrictions are allowed. It’s best if you own the
                                        image.</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Save and Cancel Buttons --}}
                        <div class="flex items-center gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button> {{-- Primary button for saving --}}
                            <x-secondary-button onclick="history.back()">{{ __('Cancel') }}</x-secondary-button>
                            {{-- Secondary button for canceling --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
