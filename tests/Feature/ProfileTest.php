<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    private function payload(User $user, array $overrides = []): array
    {
        return array_merge([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'bio' => 'Hello there',
        ], $overrides);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $user = User::factory()->create();

        $this->get(route('profile.show', $user))->assertRedirect('/login');
        $this->get('/profile/edit')->assertRedirect('/login');
        $this->patch('/profile', [])->assertRedirect('/login');
    }

    public function test_profile_page_shows_user_info_and_posts(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create(['username' => 'jane_doe', 'bio' => 'Bio text here']);
        Post::factory()->create(['user_id' => $user->id, 'body' => 'My first post']);

        $this->actingAs($viewer)->get('/users/jane_doe')
            ->assertOk()
            ->assertSee('Bio text here')
            ->assertSee('My first post')
            ->assertDontSee('id="edit-profile-button"', false);
    }

    public function test_own_profile_shows_edit_button(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.show', $user))
            ->assertOk()->assertSee('id="edit-profile-button"', false);
    }

    public function test_unknown_username_returns_404(): void
    {
        $this->actingAs(User::factory()->create())->get('/users/nobody_here')->assertNotFound();
    }

    public function test_profile_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', $this->payload($user, ['name' => 'New Name', 'bio' => 'New bio', 'username' => 'new_handle']))
            ->assertRedirect(route('profile.show', ['user' => 'new_handle']))
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new_handle', $user->username);
        $this->assertSame('New bio', $user->bio);
    }

    public function test_cannot_take_another_users_username_or_email(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', $this->payload($user, ['username' => $other->username]))
            ->assertSessionHasErrors('username');

        $this->actingAs($user)
            ->patch('/profile', $this->payload($user, ['email' => $other->email]))
            ->assertSessionHasErrors('email');
    }

    public function test_keeping_own_username_and_email_is_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', $this->payload($user))
            ->assertSessionHasNoErrors();
    }

    public function test_bio_longer_than_160_characters_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', $this->payload($user, ['bio' => str_repeat('a', 161)]))
            ->assertSessionHasErrors('bio');
    }

    public function test_profile_photo_can_be_uploaded_and_old_one_is_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', $this->payload($user, [
            'photo' => UploadedFile::fake()->image('first.jpg', 200, 200),
        ]))->assertSessionHasNoErrors();

        $first = $user->fresh()->profile_photo_path;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);
        $this->assertStringStartsWith('profile-photos/', $first);
        $this->assertStringNotContainsString('first', $first);

        $this->actingAs($user)->patch('/profile', $this->payload($user, [
            'photo' => UploadedFile::fake()->image('second.png', 200, 200),
        ]))->assertSessionHasNoErrors();

        $second = $user->fresh()->profile_photo_path;
        Storage::disk('public')->assertExists($second);
        Storage::disk('public')->assertMissing($first);
    }

    public function test_invalid_photo_types_and_sizes_are_rejected(): void
    {
        $user = User::factory()->create();

        $bad = [
            UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
            UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('vector.svg', 10, 'image/svg+xml'),
            UploadedFile::fake()->image('huge.jpg')->size(3000),
        ];

        foreach ($bad as $file) {
            $this->actingAs($user)
                ->patch('/profile', $this->payload($user, ['photo' => $file]))
                ->assertSessionHasErrors('photo');
        }

        $this->assertNull($user->fresh()->profile_photo_path);
    }

    public function test_user_id_in_request_cannot_edit_someone_else(): void
    {
        $victim = User::factory()->create(['name' => 'Victim']);
        $attacker = User::factory()->create();

        $this->actingAs($attacker)->patch('/profile', $this->payload($attacker, [
            'id' => $victim->id,
            'user_id' => $victim->id,
            'name' => 'Hacked',
        ]));

        $this->assertSame('Victim', $victim->fresh()->name);
        $this->assertSame('Hacked', $attacker->fresh()->name);
    }

    public function test_profile_photo_path_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', $this->payload($user, [
            'profile_photo_path' => 'evil/path.jpg',
        ]));

        $this->assertNull($user->fresh()->profile_photo_path);
    }
}
