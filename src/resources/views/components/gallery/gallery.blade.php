<div class="mx-auto py-6">
    <div class="{{-- grid grid-cols-1 lg:grid-cols-2 gap-4 --}}">
        @foreach ($images->sortByDesc('created_at') as $image)
            <div x-data="{ showComments: false }">
                <main class="md:x-8 mx-1 mb-4 rounded-lg bg-c-primary/10 px-2 py-6 sm:mb-6 sm:px-6 md:mb-10">
                    <div class="mx-auto grid grid-cols-1 md:grid-cols-3 md:gap-x-10">
                        <!-- Image -->
                        <div class="col-span-3 flex items-center justify-center md:col-span-2">
                            <x-gallery.image_or_placeholder style="max-w-full max-h-[570px] object-contain"
                                :image="$image" />
                        </div>
                        <!-- Text and Comments -->
                        <div class="relative col-span-3 flex flex-col pt-3 md:col-span-1 md:pt-0">
                            <!-- Title and Author -->
                            <div class="flex flex-col-reverse rounded-lg bg-none">
                                <p class="text-nav-bg mt-1 text-sm font-medium">{{ $image->username }}</p>
                                <h1 class="text-content-bg mt-1 text-lg font-semibold md:text-2xl">
                                    {{ $image->title }}
                                </h1>
                                <p class="text-xs font-medium leading-4" title="Last update {{ $image->updated_at->diffForHumans() }}"> 
                                    {{ $image->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-title-bg mt-4 max-h-64 space-y-4 overflow-auto px-3 text-sm leading-6 sm:px-0 md:max-h-[420px] md:flex-grow"
                                x-show="showComments || window.innerWidth >= 768" @click.away="showComments = false">
                                <!-- Add Comment Form (always visible) -->
                                <x-gallery.comment.add-comment-form :image="$image" />
                                <!-- Comments Section (conditionally visible) -->
                                <x-gallery.comment.show-comments :image="$image" />
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        @endforeach {{-- images as image --}}
    </div>
</div>
