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
                            <x-input-label for="title" value="Title" />
                            {{-- Label for post title --}}
                            <x-text-input class="mt-1 block w-full"
                                id="title" name="title" type="text"
                                :value="$post->title ?? old('title')" required autofocus
                                maxlength="120" />
                            {{-- Input field for post title --}}
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            {{-- Display validation errors --}}
                        </div>

                        {{-- Image Input --}}
                        <div>
                            {{-- Display existing image if it exists --}}
                            @isset($post->image)
                                <x-input-label for="image_file"
                                    value="Your Current Image" />
                                <div class="my-2">
                                    @include(
                                        'components.image_or_placeholder',
                                        [
                                            'image' => $post->image,
                                            'size_type' => 'l',
                                            'alt_title' =>
                                                'Your uploaded image',
                                            'style' => '',
                                        ]
                                    )
                                </div>
                            @endisset

                            {{-- File input field --}}
                            <x-input-label for="image_file"
                                value="{{ isset($post) ? 'Replace Image (optional)' : 'Image' }}" />
                            <label class="mt-2 block">
                                <span class="sr-only">Choose image to
                                    upload</span>
                                <input
                                    class="block w-full text-sm text-slate-100 file:mr-4 file:rounded-full file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-violet-700 hover:file:bg-violet-100"
                                    id="image_file" name="image_file"
                                    type="file"
                                    accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" />
                            </label>
                            <x-input-error class="mt-2" :messages="$errors->get('image_file')" />
                        </div>

                        {{-- Protected Checkbox --}}
                        <div class="mt-4">
                            <span class="inline-flex">
                                @include('components.checkbox', [
                                    'id' => 'is_sensitive',
                                    'name' => 'is_sensitive',
                                    'checked' =>
                                        $post->is_sensitive ?? false,
                                    'label' => 'Mark as Protected ',
                                ])
                                <svg class="ml-2"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16" height="16"
                                    fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56" />
                                    <path
                                        d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                                </svg>
                            </span>
                        </div>

                        {{-- Title Info Box --}}
                        <div class="text-md mb-4 max-w-screen-md rounded-lg bg-c-background p-4 text-blue-300"
                            role="alert">
                            <div class="flex">
                                <svg class="me-3 mt-[2px] inline h-4 w-4 flex-shrink-0"
                                    aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                                </svg>
                                <span class="sr-only">Info</span>
                                <div>
                                    <span class="font-medium">Please
                                        Note:</span>
                                    <ul class="mt-1.5 list-inside list-disc">
                                        <ul class="list-inside list-disc">
                                            <li>Your <span
                                                    class="font-medium">title</span>
                                                must be at least <strong>3
                                                    characters long</strong>.
                                            </li>
                                            <li>You can <strong>change the title
                                                    and image</strong> even
                                                after saving the post.</li>
                                            <li>If you're happy with your post,
                                                don’t forget to
                                                <strong>publish</strong> it so
                                                others can see it.
                                            </li>
                                            <li><span
                                                    class="font-medium">Important:</span>
                                                Only images with <strong>no
                                                    copyright
                                                    restrictions</strong> are
                                                allowed.</li>
                                            <li><span
                                                    class="font-medium">Protected
                                                    posts</span> will only be
                                                visible to <strong>registered
                                                    and logged-in
                                                    users</strong>. Use this
                                                option to keep your content
                                                private from the general public,
                                                while still sharing it with site
                                                members.</li>
                                        </ul>

                                    </ul>
                                </div>
                            </div>

                        </div>

                        {{-- Save and Cancel Buttons --}}
                        <div class="flex items-center gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            {{-- Primary button for saving --}}
                            <x-secondary-button
                                onclick="history.back()">{{ __('Cancel') }}</x-secondary-button>
                            {{-- Secondary button for canceling --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
