<div
    class="m-3 flex w-full flex-col rounded-lg bg-c-primary/10 p-6 shadow-lg sm:max-w-xs sm:flex-shrink sm:flex-grow sm:basis-[320px]">
    
    <div class="flex items-center gap-3">
        @include('components.profile.profile-image', [
            'user' => $user,
            'height' => '24',
            'width' => '24',
            'size' => 's',
        ])
        <!-- User Stats -->
        @include('components.profile.user-stats', [
            'user' => $user,
        ])

    </div>
    <!-- Former Name and Title -->
   

        @include('components.profile.user-name', ['user' => $user])
        <p class="mb-2 truncate text-sm text-c-primary/70">{{ $user->email }}
        </p>

        <!-- User Type -->
        <p class="mb-2 text-sm text-c-text">
            <strong>{{ $user->usertype }}</strong>
        </p>

        <!-- Timestamp Group -->
        <div class="mt-2 text-sm text-gray-400">
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
                        class="text-yellow-800">No</span>
                @endif
            </p>
        </div>
  
</div>