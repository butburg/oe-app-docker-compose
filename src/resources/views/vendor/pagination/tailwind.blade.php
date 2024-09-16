@if ($paginator->hasPages())
    <nav>
        <!-- Mobile page arrows on top -->
        <div class="mx-2 flex flex-1 justify-between sm:hidden">

            {{-- Previous Page Arrow --}}
            @if ($paginator->onFirstPage())
                <span
                    class="relative inline-flex cursor-default items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            @else
                <a class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
                    href="{{ $paginator->previousPageUrl() }}">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            @endif

            <div class="relative inline-block text-left"
                x-data="{ open: false, selectedPage: {{ $paginator->currentPage() }} }">
                <!-- Input Field (Styled like arrows and page numbers) -->
                <div class="relative inline-flex cursor-pointer items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
                    @click="open = !open">
                    Page {{ $paginator->currentPage() }}
                    <svg class="ml-2 h-5 w-5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 9.293a1 1 0 011.414 0L10 12.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <!-- Dropdown with Page Numbers -->
                <div class="absolute z-10 mt-2 w-24 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800"
                    x-show="open" @click.away="open = false">
                    <div class="py-1" role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="options-menu">
                        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                            <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                href="#" role="menuitem"
                                @click="selectedPage = {{ $i }}; window.location.href = '{{ $paginator->url($i) }}'; open = false">Page
                                {{ $i }}</a>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Next Page Arrow --}}
            @if ($paginator->hasMorePages())
                <a class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-blue-700 dark:active:bg-gray-700 dark:active:text-gray-300"
                    href="{{ $paginator->nextPageUrl() }}">
                    <svg class="h-5 w-5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <span
                    class="relative ml-3 inline-flex cursor-default items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">
                    <svg class="h-5 w-5" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </span>
            @endif
        </div>

        {{--         <!-- Mobile Page Links -->
        <div class="mb-9 flex flex-1 flex-wrap justify-center sm:hidden">
            @if ($paginator->lastPage() > 1)
                @php
                    $start = max($paginator->currentPage() - 2, 1); // Show two more pages to the left
                    $end = min($paginator->currentPage() + 2, $paginator->lastPage()); // Show two more pages to the right
                @endphp

                {{-- First Page Link -}}
                @if ($paginator->currentPage() > 3)
                    <a class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        href="{{ $paginator->url(1) }}">1</a>
                    @if ($paginator->currentPage() > 4)
                        <span
                            class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">...</span>
                    @endif
                @endif

                {{-- Page Links -}}
                @for ($i = $start; $i <= $end; $i++)
                    @if ($i == $paginator->currentPage())
                        <span
                            class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800"
                            aria-current="page">{{ $i }}</span>
                    @else
                        <a class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            href="{{ $paginator->url($i) }}">{{ $i }}</a>
                    @endif
                @endfor

                {{-- Last Page Link -}}
                @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                    @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                        <span
                            class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600">...</span>
                    @endif
                    <a class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a>
                @endif
            @endif
        </div> --}}

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-center">
            <div>
                <span
                    class="relative z-0 inline-flex rounded-md shadow-sm rtl:flex-row-reverse">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true"
                            aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="relative inline-flex cursor-default items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800"
                                aria-hidden="true">
                                <svg class="h-5 w-5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-400 focus:z-10 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:border-blue-800 dark:active:bg-gray-700"
                            href="{{ $paginator->previousPageUrl() }}"
                            aria-label="{{ __('pagination.previous') }}"
                            rel="prev">
                            <svg class="h-5 w-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span
                                    class="relative -ml-px inline-flex cursor-default items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 dark:border-gray-600 dark:bg-gray-800">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span
                                            class="relative -ml-px inline-flex cursor-default items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-800">{{ $page }}</span>
                                    </span>
                                @else
                                    <a class="relative -ml-px inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-500 focus:z-10 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300 dark:focus:border-blue-800 dark:active:bg-gray-700"
                                        href="{{ $url }}"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a class="relative -ml-px inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 ring-gray-300 transition duration-150 ease-in-out hover:text-gray-400 focus:z-10 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:border-blue-800 dark:active:bg-gray-700"
                            href="{{ $paginator->nextPageUrl() }}"
                            aria-label="{{ __('pagination.next') }}"
                            rel="next">
                            <svg class="h-5 w-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true"
                            aria-label="{{ __('pagination.next') }}">
                            <span
                                class="relative -ml-px inline-flex cursor-default items-center rounded-r-md border border-gray-600 bg-gray-800 px-2 py-2 text-sm font-medium leading-5 text-gray-500"
                                aria-hidden="true">
                                <svg class="h-5 w-5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-center">
            <p class="text-sm leading-5 text-gray-400">
                {!! __('') !!}
                @if ($paginator->firstItem())
                    <span
                        class="font-medium">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span
                        class="font-medium">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-medium">{{ $paginator->total() }}</span>
                {!! __('') !!}
            </p>
        </div>
    </nav>
@endif
