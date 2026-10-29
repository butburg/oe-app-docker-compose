<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Image;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminUserDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $target;
    private User $bystander;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create(['usertype' => 'admin']);
        $this->target = User::factory()->create();
        $this->bystander = User::factory()->create();
    }

    private function deleteUser(User $user, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->delete(route('admin.users.destroy', $user), $data + ['confirm_email' => $user->email]);
    }

    private function storeVariantFiles(Image $image): void
    {
        foreach ($image->variants as $variant) {
            Storage::disk('public')->put($variant->path, 'image');
        }
    }

    public function test_admin_can_delete_the_chosen_user_and_nobody_else(): void
    {
        $response = $this->actingAs($this->admin)->deleteUser($this->target);

        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.index'));
        $this->assertNull($this->target->fresh());
        $this->assertNotNull($this->bystander->fresh());
        $this->assertNotNull($this->admin->fresh());
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_profile_image_is_deleted_but_other_users_images_stay(): void
    {
        $targetImage = Image::factory()->forUser($this->target)->create();
        $bystanderImage = Image::factory()->forUser($this->bystander)->create();
        $this->storeVariantFiles($targetImage);

        $this->actingAs($this->admin)->deleteUser($this->target);

        $this->assertNull($targetImage->fresh());
        foreach ($targetImage->variants as $variant) {
            Storage::disk('public')->assertMissing($variant->path);
        }
        $this->assertNotNull($bystanderImage->fresh());
        $this->assertCount(4, $bystanderImage->fresh()->variants);
    }

    public function test_posts_and_comments_are_kept_anonymously_by_default(): void
    {
        $post = Post::factory()->forUser($this->target)->withImage()->create();
        $comment = Comment::factory()->forUser($this->target)->forPost($post)->create();

        $this->actingAs($this->admin)->deleteUser($this->target);

        $this->assertNull($post->fresh()->user_id);
        $this->assertNotNull($post->fresh()->image);
        $this->assertNull($comment->fresh()->user_id);
    }

    public function test_selected_posts_and_comments_of_the_chosen_user_are_deleted_only(): void
    {
        $targetPost = Post::factory()->forUser($this->target)->withImage()->create();
        $this->storeVariantFiles($targetPost->image);
        $targetComment = Comment::factory()->forUser($this->target)->forPost($targetPost)->create();

        $bystanderPost = Post::factory()->forUser($this->bystander)->withImage()->create();
        $targetCommentOnBystanderPost = Comment::factory()->forUser($this->target)->forPost($bystanderPost)->create();
        $bystanderComment = Comment::factory()->forUser($this->bystander)->forPost($bystanderPost)->create();

        $this->actingAs($this->admin)->deleteUser($this->target, [
            'delete_comments' => '1',
            'delete_posts' => '1',
        ]);

        $this->assertNull($targetPost->fresh());
        $this->assertNull($targetComment->fresh());
        $this->assertNull($targetCommentOnBystanderPost->fresh());
        foreach ($targetPost->image->variants as $variant) {
            Storage::disk('public')->assertMissing($variant->path);
        }

        $this->assertNotNull($bystanderPost->fresh());
        $this->assertNotNull($bystanderPost->fresh()->image);
        $this->assertNotNull($bystanderComment->fresh());
    }

    public function test_sessions_of_the_deleted_user_are_removed(): void
    {
        foreach ([$this->target, $this->bystander] as $user) {
            \DB::table('sessions')->insert([
                'id' => "session-{$user->id}",
                'user_id' => $user->id,
                'payload' => '',
                'last_activity' => now()->timestamp,
            ]);
        }

        $this->actingAs($this->admin)->deleteUser($this->target);

        $this->assertDatabaseMissing('sessions', ['user_id' => $this->target->id]);
        $this->assertDatabaseHas('sessions', ['user_id' => $this->bystander->id]);
    }

    public function test_wrong_confirmation_email_deletes_nobody(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.index'))
            ->deleteUser($this->target, ['confirm_email' => $this->bystander->email]);

        $response->assertSessionHasErrorsIn('adminUserDeletion', 'confirm_email')
            ->assertRedirect(route('admin.index'));
        $this->assertNotNull($this->target->fresh());
        $this->assertNotNull($this->bystander->fresh());
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $this->actingAs($this->admin)->deleteUser($this->admin)->assertForbidden();

        $this->assertNotNull($this->admin->fresh());
    }

    public function test_admin_cannot_delete_another_admin(): void
    {
        $otherAdmin = User::factory()->create(['usertype' => 'admin']);

        $this->actingAs($this->admin)->deleteUser($otherAdmin)->assertForbidden();

        $this->assertNotNull($otherAdmin->fresh());
    }

    public function test_regular_user_cannot_delete_other_users(): void
    {
        $this->actingAs($this->bystander)->deleteUser($this->target)->assertRedirect('/');

        $this->assertNotNull($this->target->fresh());
        $this->assertNotNull($this->bystander->fresh());
    }

    public function test_guest_cannot_delete_users(): void
    {
        $this->deleteUser($this->target)->assertRedirect(route('login'));

        $this->assertNotNull($this->target->fresh());
    }

    public function test_dashboard_shows_delete_button_only_for_deletable_users(): void
    {
        $otherAdmin = User::factory()->create(['usertype' => 'admin']);

        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertOk()
            ->assertSee(route('admin.users.destroy', $this->target))
            ->assertSee(route('admin.users.destroy', $this->bystander))
            ->assertDontSee(route('admin.users.destroy', $this->admin))
            ->assertDontSee(route('admin.users.destroy', $otherAdmin));
    }
}
