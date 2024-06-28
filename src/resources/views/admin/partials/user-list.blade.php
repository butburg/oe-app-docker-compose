<h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Users List') }}</h3>
<table class="min-w-full bg-white border">
    <thead>
        <tr>
            <th class="py-2 px-4 border">Name</th>
            <th class="py-2 px-4 border">Email</th>
            <th class="py-2 px-4 border">User Type</th>
            <th class="py-2 px-4 border">Posts Count</th>
            <th class="py-2 px-4 border">Comments Count</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td class="py-2 px-4 border">{{ $user->name }}</td>
                <td class="py-2 px-4 border">{{ $user->email }}</td>
                <td class="py-2 px-4 border">{{ $user->usertype }}</td>
                <td class="py-2 px-4 border">{{ $user->posts_count }}</td>
                <td class="py-2 px-4 border">{{ $user->comments_count }}</td>
            </tr>
        @endforeach
    </tbody>
</table>