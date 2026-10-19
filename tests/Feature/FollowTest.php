<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function followRows(User $follower, User $following): int
    {
        return DB::table('follows')
            ->where('follower_id', $follower->id)
            ->where('following_id', $following->id)
            ->count();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('follows.store', $user))->assertRedirect('/login');
        $this->delete(route('follows.destroy', $user))->assertRedirect('/login');
        $this->get(route('follows.followers', $user))->assertRedirect('/login');
        $this->get(route('follows.following', $user))->assertRedirect('/login');
    }

    public function test_user_can_follow_another_user(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->post(route('follows.store', $other))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, $this->followRows($me, $other));
        $this->assertSame(0, $this->followRows($other, $me));
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->post(route('follows.store', $me))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_following_twice_keeps_one_row_and_does_not_crash(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->post(route('follows.store', $other))->assertRedirect();
        $this->actingAs($me)->post(route('follows.store', $other))->assertRedirect();

        $this->assertSame(1, $this->followRows($me, $other));
    }

    public function test_following_an_unknown_user_returns_404(): void
    {
        $this->actingAs(User::factory()->create())->post('/users/nobody_here/follow')->assertNotFound();
    }

    public function test_user_can_unfollow(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $me->following()->attach($other->id);

        $this->actingAs($me)->delete(route('follows.destroy', $other))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $this->followRows($me, $other));
    }

    public function test_unfollowing_someone_you_do_not_follow_is_harmless(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)->delete(route('follows.destroy', $other))->assertRedirect();

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_unfollowing_only_removes_your_own_follow(): void
    {
        $me = User::factory()->create();
        $friend = User::factory()->create();
        $other = User::factory()->create();
        $me->following()->attach($other->id);
        $friend->following()->attach($other->id);

        $this->actingAs($me)->delete(route('follows.destroy', $other));

        $this->assertSame(0, $this->followRows($me, $other));
        $this->assertSame(1, $this->followRows($friend, $other));
    }

    public function test_database_rejects_a_duplicate_follow(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $a->following()->attach($b->id);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('follows')->insert([
            'follower_id' => $a->id,
            'following_id' => $b->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_profile_shows_follower_and_following_counts(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create();
        User::factory(3)->create()->each(fn ($f) => $f->following()->attach($user->id));
        $user->following()->attach(User::factory(2)->create()->pluck('id'));

        $this->actingAs($viewer)->get(route('profile.show', $user))
            ->assertOk()
            ->assertViewHas('user', fn ($u) => (int) $u->followers_count === 3 && (int) $u->following_count === 2);
    }

    public function test_profile_shows_follow_button_for_others_and_edit_button_for_self(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create(['name' => 'Other Person']);

        $this->actingAs($me)->get(route('profile.show', $other))
            ->assertSee('aria-label="Follow Other Person"', false)
            ->assertDontSee('aria-label="Unfollow Other Person"', false)
            ->assertDontSee('id="edit-profile-button"', false);

        $this->actingAs($me)->get(route('profile.show', $me))
            ->assertSee('id="edit-profile-button"', false)
            ->assertDontSee('aria-label="Follow ' . $me->name . '"', false);
    }

    public function test_profile_shows_unfollow_button_when_following(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create(['name' => 'Other Person']);
        $me->following()->attach($other->id);

        $this->actingAs($me)->get(route('profile.show', $other))
            ->assertSee('aria-label="Unfollow Other Person"', false)
            ->assertDontSee('aria-label="Follow Other Person"', false);
    }

    public function test_followers_and_following_lists(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create();
        $fan = User::factory()->create(['name' => 'Fan Alpha']);
        $idol = User::factory()->create(['name' => 'Idol Beta']);
        $fan->following()->attach($user->id);
        $user->following()->attach($idol->id);

        $this->actingAs($viewer)->get(route('follows.followers', $user))
            ->assertOk()->assertSee('Fan Alpha')->assertDontSee('Idol Beta');

        $this->actingAs($viewer)->get(route('follows.following', $user))
            ->assertOk()->assertSee('Idol Beta')->assertDontSee('Fan Alpha');
    }

    public function test_lists_show_empty_messages(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($viewer)->get(route('follows.followers', $user))->assertSee('No followers yet.');
        $this->actingAs($viewer)->get(route('follows.following', $user))->assertSee('Not following anyone yet.');
    }

    public function test_list_buttons_reflect_who_you_follow(): void
    {
        $me = User::factory()->create();
        $user = User::factory()->create();
        $known = User::factory()->create(['name' => 'Known Person']);
        $stranger = User::factory()->create(['name' => 'Stranger Person']);
        $known->following()->attach($user->id);
        $stranger->following()->attach($user->id);
        $me->following()->attach($known->id);

        $this->actingAs($me)->get(route('follows.followers', $user))
            ->assertSee('aria-label="Unfollow Known Person"', false)
            ->assertSee('aria-label="Follow Stranger Person"', false);
    }

    public function test_your_own_row_has_no_follow_button(): void
    {
        $me = User::factory()->create();
        $user = User::factory()->create();
        $me->following()->attach($user->id);

        $this->actingAs($me)->get(route('follows.followers', $user))
            ->assertSee($me->name)
            ->assertDontSee('aria-label="Follow ' . $me->name . '"', false)
            ->assertDontSee('aria-label="Unfollow ' . $me->name . '"', false);
    }

    public function test_follower_list_is_paginated(): void
    {
        $viewer = User::factory()->create();
        $user = User::factory()->create();
        User::factory(25)->create()->each(fn ($f) => $f->following()->attach($user->id));

        $this->actingAs($viewer)->get(route('follows.followers', $user))
            ->assertViewHas('users', fn ($u) => $u->count() === 20 && $u->total() === 25);

        $this->actingAs($viewer)->get(route('follows.followers', $user) . '?page=2')
            ->assertViewHas('users', fn ($u) => $u->count() === 5);
    }
}
