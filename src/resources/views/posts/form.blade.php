<x-app-layout>
    {{-- Header section with 'Edit' or 'Create' depending on the existence of $post --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Use 'Edit' for edit mode and 'Create' for create mode --}}
            {{ isset($post) ? 'Edit' : 'Create' }} 
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Form for post creation/updation with file upload --}}
                    <form method="post" action="{{ isset($post) ? route('posts.update', $post->id) : route('posts.store') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
                        @csrf {{-- CSRF protection --}}
                        {{-- Use PUT method for edit mode --}}
                        @isset($post)
                            @method('put')
                        @endisset

                        {{-- Post Content Input --}}
                        <div>
                            <x-input-label for="title" value="Post" /> {{-- Label for post content --}}
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="$post->title ?? old('title')" required autofocus /> {{-- Input field for post content --}}
                            <x-input-error class="mt-2" :messages="$errors->get('title')" /> {{-- Display validation errors for post content --}}
                        </div>

                        {{-- Info File Input --}}
                        <div>
                            <x-input-label for="info_file" value="Info File" /> {{-- Label for info file --}}
                            <label class="block mt-2">
                                <span class="sr-only">Choose file</span> {{-- Screen reader text --}}
                                <input type="file" id="info_file" name="info_file" accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp" class="block w-full text-sm text-slate-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-violet-50 file:text-violet-700
                                    hover:file:bg-violet-100
                                " /> {{-- File input field --}}
                            </label>
                            {{-- Display existing file if it exists --}}
                            @isset($post->info_file)
                                <div class="text-sm shrink-0 my-2">
                                    <span>Saved File: </span> {{-- Display text indicating file existence --}}
                                    <a href="{{ Storage::url($post->info_file) }}">{{ explode('/', $post->info_file)[3] }}</a> {{-- Display file name with link --}}
                                </div>
                            @endisset
                            <x-input-error class="mt-2" :messages="$errors->get('info_file')" /> {{-- Display validation errors for info file --}}
                        </div>

                        {{-- Save and Cancel Buttons --}}
                        <div class="flex items-center gap-2">
                            <x-primary-button>{{ __('Save') }}</x-primary-button> {{-- Primary button for saving --}}
                            <x-secondary-button onclick="history.back()">{{ __('Cancel') }}</x-secondary-button> {{-- Secondary button for canceling --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
