<h3 class="text-nav-text mb-4 text-lg font-medium">{{ __('Users List') }}</h3>
<table class="bg-nav-bg text-nav-text min-w-full border">
    <thead>
        <tr>
            <th class="border px-4 py-2">Name</th>
            <th class="border px-4 py-2">Email</th>
            <th class="border px-4 py-2">User Type</th>
            <th class="border px-4 py-2"># Posts</th>
            <th class="border px-4 py-2"># Comments</th>
            <th class="border px-4 py-2">Register Date</th>
            <th class="border px-4 py-2">Updated At</th>
            <th class="border px-4 py-2">Email Verified At</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users->sortByDesc('created_at') as $user)
            <tr>
                <td class="border px-4 py-2" title="{{ $user->name }}">{{ Str::limit($user->name, config('app.truncate_name'), '...') }}</td>
                <td class="border px-4 py-2">{{ $user->email }}</td>
                <td class="border px-4 py-2">{{ $user->usertype }}</td>
                <td class="border px-4 py-2">{{ $user->posts_count }}</td>
                <td class="border px-4 py-2">{{ $user->comments_count }}</td>
                <td class="border px-4 py-2">{{ $user->created_at->format('d.m.y') }}</td>
                <td class="border px-4 py-2">{{ $user->updated_at->format('d.m.y') }}</td>
                <td class="border px-4 py-2">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('d.m.y') : 'N/A' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
