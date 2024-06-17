<main class="py-6 px-4 sm:p-6 md:py-10 md:px-8">
    <div class="max-w-4xl mx-auto grid grid-cols-1 lg:max-w-5xl md:gap-x-20 md:grid-cols-2">
      <div class="relative p-3 col-start-1 row-start-1 flex flex-col-reverse rounded-lg bg-gradient-to-t from-black/75 via-black/0 sm:bg-none sm:row-start-2 sm:p-0 md:row-start-1">
        <h1 class="mt-1 text-lg font-semibold text-white sm:text-slate-900 md:text-2xl ">{{ $image->title }}</h1>
        <p class="text-sm leading-4 font-medium text-white sm:text-slate-500 ">Last updated {{ $image->updated_at->diffForHumans() }}</p>
      </div>
      <div class="grid gap-4 col-start-1 col-end-3 row-start-1 sm:mb-6 sm:grid-cols-4 lg:gap-6 md:col-start-2 md:row-end-6 md:row-span-6 lg:mb-0">
        <x-gallery.image_or_placeholder :image="$image" :style="'w-full h-60 object-contain rounded-lg sm:h-52 sm:col-span-2 md:col-span-full'"/>
      </div>
      <dl class="mt-4 text-xs font-medium flex items-center row-start-2 sm:mt-1 sm:row-start-3 md:mt-2.5 md:row-start-2">
        <dt class="sr-only">Author</dt>
        <dd class="flex items-center">
            {{ $image->username }}
        </dd>
      </dl>
      <div class="mt-4 col-start-1 row-start-3 self-center sm:mt-0 sm:col-start-2 sm:row-start-2 sm:row-span-2 md:mt-6 md:col-start-1 md:row-start-4 md:row-end-4">
        <button type="button" class="bg-indigo-600 text-white text-sm leading-6 font-medium py-2 px-3 rounded-lg">Send</button>
      </div>
      <p class="mt-4 text-sm leading-6 col-start-1 sm:col-span-2 md:mt-6 md:row-start-3 md:col-span-1">
        This sunny and spacious room is for those traveling light and looking for a comfy and cosy place to lay their head for a night or two. This beach house sits in a vibrant neighborhood littered with cafes, pubs, restaurants and supermarkets and is close to all the major attractions such as Edinburgh Castle and Arthur's Seat.
      </p>
    </div>
  </main>