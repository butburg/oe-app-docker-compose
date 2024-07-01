
<x-app-layout>
    {{-- Header section with 'Edit' or 'Create' depending on the existence of $post --}}
    <x-slot name="header">
        <x-header2>{{ isset($post) ? 'Edit' : 'Create' }}</x-header2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-content-bg overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Form for post creation/updation with file upload --}}
                    <form method="post"
                        action="{{ isset($post) ? route('posts.update', $post->id) : route('posts.store') }}"
                        class="mt-6 space-y-6" enctype="multipart/form-data">
                        @csrf {{-- CSRF protection --}}
                        {{-- Use PUT method for edit mode --}}
                        @isset($post)
                            @method('put')
                        @endisset

                        {{-- Post Title Input --}}
                        <div>
                            <x-input-label for="title" value="Title" /> {{-- Label for post title --}}
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                :value="$post->title ?? old('title')" required autofocus /> {{-- Input field for post title --}}
                            <x-input-error class="mt-2" :messages="$errors->get('title')" /> {{-- Display validation errors for post title --}}
                        </div>

                        {{-- Image Input --}}
                        <div>

                            {{-- Display existing image if it exists --}}
                            @isset($post->image_file)
                                <x-input-label for="image_file" value="Actual Image" /> {{-- Label for image --}}
                                <div class="my-2">
                                    <img src="{{ asset('storage/' . $post->image_file) }}" alt="Post Image"
                                        class="w-full h-auto max-h-96 object-contain">
                                </div>
                            @endisset

                            {{-- File input field --}}
                            <x-input-label for="image_file"
                                value="{{ isset($post) ? 'Replace Image (optional)' : 'Image' }}" />
                            <label class="block mt-2">
                                <span class="sr-only">Choose image to upload</span> {{-- Screen reader text --}}
                                <input type="file" id="image_file" name="image_file"
                                    accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp"
                                    class="block w-full text-sm text-slate-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-violet-50 file:text-violet-700
                                    hover:file:bg-violet-100
                                " />
                            </label>
                            {{-- replace the error msg from image file to image. can be implemented as blade template --}}
                            @if ($errors->has('image_file'))
                                @foreach ($errors->get('image_file') as $error)
                                    <div class="text-sm text-red-600 space-y-1">{{ str_replace('image file', 'image', $error) }}
                                    </div>
                                @endforeach
                            @endif {{-- Display validation errors for info file --}}
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
