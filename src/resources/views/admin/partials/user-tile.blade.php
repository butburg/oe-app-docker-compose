<div class="flex flex-col w-full sm:flex-grow sm:flex-shrink sm:basis-[320px] sm:max-w-xs m-3 rounded-lg bg-c-primary/10 p-6 shadow-lg">
    <div class="flex items-center gap-3">
        <a class="block" href="{{ route('profile.show', $user->id) }}">
            <div class="relative">
                <!-- Profile Image with Conditional Border -->
                @php
                    $imagePath = $user->image?->variants->firstWhere('size_type', 'l')->path;
                @endphp
                @if ($imagePath)
                    <x-image_or_placeholder alt_title="{{ $user->name }}"
                        style="{{ $user->session ? 'border-4 border-green-500' : '' }} h-24 w-24 rounded-full object-cover"
                        :image="$user->image" size_type="s" />
                @else
                    <!-- Replace `path-to-placeholder-svg` with the actual SVG path you have -->
                    <svg class="{{ $user->session ? 'border-4 border-green-500' : '' }} h-24 w-24 text-gray-300"
                        fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v1h16v-1c0-2.66-5.33-4-8-4z">
                        </path>
                    </svg>
                @endif
            </div>
        </a>
        <!-- Post and Comment Counts with Icons -->
        <div class="text-c-text">
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold">{{ $user->posts_count }}</span>
                <svg class="" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
                    <path
                        d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z" />
                </svg>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-lg font-bold">{{ $user->comments_count }} </span>
                <svg class="" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path
                        d="M2.678 11.894a1 1 0 0 1 .287.801 11 11 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8 8 0 0 0 8 14c3.996 0 7-2.807 7-6s-3.004-6-7-6-7 2.808-7 6c0 1.468.617 2.83 1.678 3.894m-.493 3.905a22 22 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a10 10 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105" />
                </svg>
            </div>
        </div>
    </div>
    <div class="mt-4 flex flex-col">
        <!-- Name and Former Name  and Email -->
        @if ($user->getFormerNameIfApplicable())
            <p class="mx-0 -mb-1 -mt-2 truncate px-0 text-sm text-gray-400">
                <small>Formerly: {{ $user->getFormerNameIfApplicable() }}</small>
            </p>
        @endif
        <h4 class="mb-2 text-xl font-bold text-c-text">{{ $user->name }}</h4>
        <p class="mb-2 truncate text-sm text-c-primary/70">{{ $user->email }}</p>
        <!-- User Type -->
        <p class="mb-2 text-sm text-c-text">
            <strong>{{ $user->usertype }}</strong>
        </p>
        <!-- Timestamp Group -->
        <div class="mt-2 text-sm text-gray-400">
            <p class="mb-1">
                <strong>Registered:</strong> {{ $user->created_at->format('M d, Y') }}
            </p>
            <p class="mb-1">
                <strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y') }}
            </p>
            <p>
                @if ($user->email_verified_at)
                    <strong>Email Verified:</strong> {{ $user->email_verified_at->format('M d, Y') }}
                @else
                    <strong>Email Verified:</strong> <span class="text-yellow-800">No</span>
                @endif
            </p>
            <p>
                <strong>Seen:</strong>
                @if ($user->session)
                    {{ \Carbon\Carbon::parse($user->session->last_activity)->diffForHumans() }}
                @else
                    Offline
                @endif
            </p>
        </div>
    </div>
</div>
