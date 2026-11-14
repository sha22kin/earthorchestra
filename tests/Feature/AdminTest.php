<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Page;
use App\Models\User;
use App\Models\Visualisation;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = ContentSeeder::class;

    protected $seed = true;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function editor(): User
    {
        return User::factory()->create(['role' => 'editor']);
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Sign in to the admin');
    }

    public function test_an_admin_can_sign_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'correct-horse-battery', 'role' => 'admin']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-horse-battery'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_every_admin_screen_renders(): void
    {
        $this->actingAs($this->admin());
        $v = Visualisation::first();

        foreach ([
            '/admin', '/admin/movements', "/admin/movements/{$v->slug}/edit",
            '/admin/pages', '/admin/pages/home/edit', '/admin/pages/about/edit', '/admin/pages/credits/edit',
            '/admin/arrangements', '/admin/users', '/admin/users/create', '/admin/profile',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dashboard_charts_saves_and_listens(): void
    {
        $a = Arrangement::create(['code' => 'abcd2345', 'levels' => ['near-surface-ozone' => 40]]);
        $this->get('/arrangements/abcd2345')->assertOk();
        $this->get('/arrangements/abcd2345')->assertOk();

        $this->assertSame(2, $a->fresh()->listens);
        $this->assertSame(2, $a->plays()->count());

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Shared-link listens')
            ->assertSee('Most-listened arrangements');
    }

    public function test_a_movement_can_be_edited(): void
    {
        $this->actingAs($this->editor());
        $v = Visualisation::where('slug', 'near-surface-wind-speed')->first();
        $legend = $v->legend;
        $legend['label'] = 'Wind (edited)';

        $this->put("/admin/movements/{$v->slug}", [
            'title' => 'Wind, edited',
            'short_title' => 'Wind',
            'model' => 'GEOS-FP',
            'measurement' => 'Wind speed at 10 m',
            'interaction' => 'cursor',
            'accent' => '#123abc',
            'svs_id' => 5148,
            'data_starts_on' => '2026-09-12',
            'data_ends_on' => '2026-09-27',
            'summary' => 'Short intro.',
            'body' => "First paragraph [1].\n\n## A heading?\n\nSecond\nparagraph on two lines.",
            'legend' => json_encode($legend),
            'citations' => [
                ['reference' => 'Source one.', 'url' => 'https://example.org/one'],
                ['reference' => '', 'url' => ''],
            ],
        ])->assertRedirect("/admin/movements/{$v->slug}/edit")->assertSessionHasNoErrors();

        $v->refresh();
        $this->assertSame('Wind, edited', $v->title);
        $this->assertSame('cursor', $v->interaction);
        $this->assertSame([
            ['type' => 'p', 'text' => 'First paragraph [1].'],
            ['type' => 'h', 'text' => 'A heading?'],
            ['type' => 'p', 'text' => 'Second paragraph on two lines.'],
        ], $v->body);
        $this->assertSame('Wind (edited)', $v->legend['label']);
        $this->assertSame(1, $v->citations()->count());

        $this->get('/')->assertSee('Wind, edited');
        $this->get("/movements/{$v->slug}")->assertSee('A heading?')->assertSee('example.org/one');
    }

    public function test_movement_validation_rejects_a_broken_legend(): void
    {
        $this->actingAs($this->editor());
        $v = Visualisation::first();

        $this->from("/admin/movements/{$v->slug}/edit")->put("/admin/movements/{$v->slug}", [
            'title' => 'x', 'short_title' => 'x', 'model' => 'GEOS-FP', 'measurement' => 'x', 'interaction' => 'cursor',
            'accent' => 'red', 'svs_id' => 1, 'data_starts_on' => '2026-01-02', 'data_ends_on' => '2026-01-01',
            'summary' => 'x', 'body' => 'x', 'legend' => '{"label":"a","unit":"b","stops":[{"color":"#fff","value":1}]}',
        ])->assertSessionHasErrors(['accent', 'data_ends_on', 'legend']);

        $this->assertNotSame('x', $v->fresh()->title);
    }

    public function test_pages_can_be_edited(): void
    {
        $this->actingAs($this->editor());

        $this->put('/admin/pages/about', [
            'title' => 'About us',
            'lede' => 'New lede',
            'paragraphs' => "One.\n\nTwo.\n\nThree.",
            'pullquote' => 'Quote here',
        ])->assertSessionHasNoErrors();
        $this->assertSame(['One.', 'Two.', 'Three.'], Page::where('slug', 'about')->first()->content['paragraphs']);
        $this->get('/about')->assertSee('Quote here');

        $this->put('/admin/pages/credits', [
            'title' => 'Credits',
            'team' => [['role' => 'Coding', 'names' => 'Ana, Ben & Cy'], ['role' => '', 'names' => '']],
            'programs' => [['label' => 'Audio', 'value' => 'FL Studio']],
            'ai_note' => '',
            'citations_note' => 'See each page.',
        ])->assertSessionHasNoErrors();
        $credits = Page::where('slug', 'credits')->first()->content;
        $this->assertSame(['Ana', 'Ben', 'Cy'], $credits['team'][0]['names']);
        $this->get('/credits')->assertSee('Ana &amp; Ben &amp; Cy', escape: false)->assertDontSee('A note on AI usage');
    }

    public function test_editors_cannot_manage_people(): void
    {
        $this->actingAs($this->editor());

        $this->get('/admin/users')->assertForbidden();
        $this->post('/admin/users', [])->assertForbidden();
        $this->get('/admin')->assertDontSee('href="'.route('admin.users.index').'"', escape: false);
    }

    public function test_admins_manage_people_but_cannot_remove_themselves(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->post('/admin/users', [
            'name' => 'New Editor', 'email' => 'new@example.org', 'role' => 'editor',
            'password' => 'long-enough-1', 'password_confirmation' => 'long-enough-1',
        ])->assertRedirect('/admin/users');
        $new = User::where('email', 'new@example.org')->firstOrFail();
        $this->assertTrue(Hash::check('long-enough-1', $new->password));

        $this->delete("/admin/users/{$admin->id}")->assertSessionHasErrors('user');
        $this->put("/admin/users/{$admin->id}", ['name' => 'Me', 'email' => $admin->email, 'role' => 'editor'])
            ->assertSessionHasErrors('role');

        $this->delete("/admin/users/{$new->id}")->assertSessionHasNoErrors();
        $this->assertModelMissing($new);
    }

    public function test_arrangements_can_be_searched_and_deleted(): void
    {
        $this->actingAs($this->editor());
        Arrangement::create(['code' => 'aaaa2222', 'title' => 'Storm front', 'levels' => ['near-surface-ozone' => 40]]);
        $b = Arrangement::create(['code' => 'bbbb3333', 'title' => 'Calm', 'levels' => ['near-surface-ozone' => 40]]);

        $this->get('/admin/arrangements?q=Storm')->assertSee('Storm front')->assertDontSee('bbbb3333');

        $this->delete("/admin/arrangements/{$b->id}")->assertSessionHasNoErrors();
        $this->assertModelMissing($b);
        $this->get('/arrangements/bbbb3333')->assertNotFound();
    }

    public function test_profile_password_change_needs_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);
        $this->actingAs($user);

        $this->put('/admin/profile', [
            'name' => $user->name, 'email' => $user->email,
            'current_password' => 'wrong', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertSessionHasErrors('current_password');

        $this->put('/admin/profile', [
            'name' => 'Renamed', 'email' => $user->email,
            'current_password' => 'old-password-1', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Renamed', $user->name);
        $this->assertTrue(Hash::check('new-password-1', $user->password));
    }

    public function test_seeding_again_does_not_overwrite_admin_edits(): void
    {
        Visualisation::first()->update(['title' => 'Edited in admin']);
        $this->seed(ContentSeeder::class);
        $this->assertSame('Edited in admin', Visualisation::first()->title);
    }
}
