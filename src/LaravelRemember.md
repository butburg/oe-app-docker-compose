# Laravel Remember
> My personal learnings (Library of Knowledge or Good to Know's)


## Laravel new CRUD

Example with adding Comments to the app:

Do below steps or just run: **`php artisan make:model Comment -mcfs --resource`** to do most of the steps.


1. **Define Routes for Comments**. First, add resources routes for the comments in routes/web.php.
    ```php
    use App\Http\Controllers\CommentController;
    Route::resources([
        'posts' => PostController::class,
        'comments' => CommentController::class,
    ]);
    ```

2. **Generate CommentController**. Use the Artisan command to generate a new controller for comments.
    ```
    php artisan make:controller CommentController --resource
    OR 
    docker-compose run --rm artisan make:controller CommentController --resource
    ```

3. **Define Request Validation**. Create a request class for validating comments. Use Artisan to generate this class:
    ```
    php artisan make:request CommentStoreRequest
    ``` 
    Than set authorize() to true. Add rules like `'comment' => 'required|string|min:1|max:1000',`. Change Namespace to `namespace App\Http\Requests\Comment;`.

4. **Implement CommentController.** In `app/Http/Controllers/CommentController.php`, implement the *CRUD* methods.

5. **Adjust Blade Views**. If you have Blade views for displaying, creating, and editing comments, make sure they are correctly set up to handle the form submissions and display comments within the context of a post. You might include a form for creating a comment in a post view (posts.show):
    ```php
    {{-- Display Comments --}}
    @foreach ($post->comments as $comment)
        <div>
            <p>{{ $comment->comment }}</p>
            <small>By {{ $comment->user->name }}</small>
        </div>
    @endforeach

    {{-- Add Comment Form --}}
    @if (Auth::check())
        <form action="{{ route('comments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="post_id" value="{{ $post->id }}">
            <textarea name="comment" required></textarea>
            <button type="submit">Add Comment</button>
        </form>
    @endif
    ```

6. **Update Comment Model and Relationships**. Ensure that your Comment model is properly set up (fillable) to handle relationships. For instance, your Comment model should look something like this: 
    ```php
    class Comment extends Model
    {
        use HasFactory;

        protected $fillable = ['user_id', 'post_id', 'comment'];

        public function user()
        {
            return $this->belongsTo(User::class);
        }

        public function post()
        {
            return $this->belongsTo(Post::class);
        }
    }
    ```

## Laravel Service or Action or Jobs?

### Action
is like a Job (?). It contains one method, its a one time pass, with quick execution. It can handle the request for us. We call it in our Controller Class like:

```$return = $action->handle($request);``` 

### Service 
contains several methods and we will give each method specific parameters. Call it like:

```$return = service->store($reqeust->user_id, $reqeust->name)```

### Implementation in Controller
Regardless of whether you use an Action or a Service, both need to be included in the controller's function:

```php
public function voice(StoreRequest $request, SomeService $service)
{
    // Service usage example
    $return = $service->store($request->user_id, $request->name);
}

public function voice(StoreRequest $request, SomeAction $action)
{
    // Action usage example
    $return = $action->handle($request);
}
```