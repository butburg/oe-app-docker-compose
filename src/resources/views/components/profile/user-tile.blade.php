{{-- Small tiles showing the users. Used for the Admin dashboard. --}}
<div
    class="app-surface m-3 flex w-full flex-col p-4 sm:max-w-xs sm:flex-shrink sm:flex-grow sm:basis-[320px] sm:p-5">

    <div class="flex items-center gap-3">
        <!-- User Image -->
        @php
            $isOnline = $user->session ? 'border-2 border-c-accent' : 'border-2 border-c-line';
        @endphp
        <a class="transform transition-transform hover:scale-105"
            href="{{ $user ? route('profile.show', $user) : '' }}">
            @include('components.image_or_placeholder', [
                'image' => $user->image,
                'size_type' => 'xs',
                'alt_title' => 'Your Profile Image',
                'style' => "{$isOnline} my-2 h-20 w-20 rounded-full bg-c-surface-raised object-cover",
                'placeholder' => 'images/starfish.svg',
            ])
        </a>

        <!-- User Stats -->
        @include('components.profile.user-stats', [
            'user' => $user,
        ])
    </div>

    <!-- Former Name and Title -->
    @include('components.profile.user-name', ['user' => $user])
    <p class="mb-2 truncate text-sm text-c-muted">{{ $user->email }}
    </p>

    <!-- User Type -->
    <p class="mb-2 text-sm text-c-text">
        <strong>{{ $user->usertype }}</strong>
    </p>

    <!-- Timestamp Group -->
    <div class="mt-2 text-sm text-c-muted">
        <p>
            <strong>Seen:</strong>
            @if ($user->session)
                {{ \Carbon\Carbon::parse($user->session->last_activity)->diffForHumans() }}
            @else
                Offline
            @endif
        </p>
        <p class="mb-1">
            <strong>Last Updated:</strong>
            {{ $user->updated_at->format('d.m.y H:i') }}
        </p>
        <p class="mb-1">
            <strong>Registered:</strong>
            {{ $user->created_at->format('d.m.y H:i') }}
        </p>
        <p>
            @if ($user->email_verified_at)
                <strong>Email Verified:</strong>
                {{ $user->email_verified_at->format('d.m.y H:i') }}
            @else
                <strong>Email Verified:</strong> <span
                    class="text-rose-300">No</span>
            @endif
        </p>
    </div>

</div>
