<div class="mx-auto py-6">
    <div class="{{-- grid grid-cols-1 lg:grid-cols-2 gap-4 --}}">
        @foreach ($images->reverse() as $image)
            <div x-data="{ showComments: false }">
                <main class="px-4 py-6 sm:p-6 md:px-8 md:py-10">
                    <div class="mx-auto grid grid-cols-1 md:grid-cols-3 md:gap-x-10">
                        <!-- Image -->
                        <div class="col-span-3 md:col-span-2 flex items-center justify-center">
                            <x-gallery.image_or_placeholder style="max-w-full max-h-[570px] object-contain" :image="$image" />
                        </div>
                        <!-- Text and Comments -->
                        <div class="relative col-span-3 md:col-span-1 px-3 pt-3 md:pt-0 flex flex-col">
                            <!-- Title and Author -->
                            <div class="flex flex-col-reverse rounded-lg bg-none">
                                <p class="mt-1 text-xs font-medium">{{ $image->username }}</p>
                                <h1 class="mt-1 text-lg font-semibold text-black sm:text-slate-800 md:text-2xl">
                                    {{ $image->title }}
                                </h1>
                                <p class="text-sm font-medium leading-4 text-black sm:text-slate-500">Last updated
                                    {{ $image->updated_at->diffForHumans() }}</p>
                            </div>
                            <!-- Add Comment Form (always visible) -->
                            <!-- Comments Section (conditionally visible) -->
                            <div class="mt-4 max-h-64 space-y-4 overflow-auto text-sm leading-6 md:max-h-80 md:flex-grow"
                                x-show="showComments || window.innerWidth >= 768" @click.away="showComments = false">
                                <x-gallery.comment.add-comment-form :image="$image" />
                                <x-gallery.comment.show-comments :image="$image" />
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        @endforeach {{-- images as image --}}
    </div>
</div>
