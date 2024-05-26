<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentStoreRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Optionally, you can list comments, though usually, comments are listed within a post
        return response()->json(Comment::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return a view to create a new comment if necessary
        return response()->view('comments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CommentStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $comment = Comment::create($validated);

        if($comment) {
            session()->flash('notif.success', 'Comment created successfully!');
            return redirect()->back();
        }

        return abort(500); // Return a server error if the comment creation fails
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::findOrFail($id);
        return response()->view('comments.show', ['comment' => $comment]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Retrieve the post with the specified ID
        $comment = Comment::findOrFail($id);

        // Check if the authenticated user owns the post
        if ($comment->user_id !== Auth::id()) {
            return abort(403, 'Unauthorized action.');
        }

        // Pass the post to the view for editing
        return response()->view('comments.form', [
            'comment' => $comment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommentStoreRequest $request, string $id) :RedirectResponse
    {
        $comment = Comment::findOrFail($id);

        $validated = $request->validated();

        if($comment->update($request)){
            session()->flash('notif.success', 'Comment updated successfully!');
            return redirect()->back(); // or redirect to a specific post
        }

        return abort(500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $comment = Comment::findOrFail($id);

        if ($comment->delete()) {
            session()->flash('notif.success', 'Comment deleted successfully!');
            return redirect()->back(); // or redirect to a specific post
        }

        return abort(500);
    }
}
