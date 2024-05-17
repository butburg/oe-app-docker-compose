# Laravel Remember
> My personal learnings (Library of Knowledge or Good to Know's)

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