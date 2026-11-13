<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArrangementTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = ContentSeeder::class;

    protected $seed = true;

    public function test_an_arrangement_can_be_saved_and_shared(): void
    {
        $response = $this->postJson('/api/arrangements', [
            'title' => '<i>Storm</i> front',
            'levels' => ['near-surface-wind-speed' => 90, 'precipitation-and-clouds' => 75],
        ])->assertCreated()->assertJsonPath('title', 'Storm front');

        $arrangement = Arrangement::firstOrFail();

        $this->assertSame(8, strlen($arrangement->code));
        $this->assertSame(0, $arrangement->levels['near-surface-temperature'], 'Missing tracks default to silent');
        $this->assertSame(90, $arrangement->levels['near-surface-wind-speed']);
        $response->assertJsonPath('url', route('arrangements.show', $arrangement));

        $this->get($response->json('url'))
            ->assertOk()
            ->assertSee('Storm front')
            ->assertSee('"code":"'.$arrangement->code.'"', escape: false);

        $this->assertSame(1, $arrangement->fresh()->listens);
    }

    public function test_it_rejects_unknown_tracks_out_of_range_levels_and_silence(): void
    {
        $this->postJson('/api/arrangements', ['levels' => ['bogus' => 50]])
            ->assertUnprocessable()->assertJsonValidationErrors('levels');

        $this->postJson('/api/arrangements', ['levels' => ['near-surface-ozone' => 101]])
            ->assertUnprocessable()->assertJsonValidationErrors('levels.near-surface-ozone');

        $this->postJson('/api/arrangements', ['levels' => ['near-surface-ozone' => 0]])
            ->assertUnprocessable()->assertJsonValidationErrors('levels');

        $this->postJson('/api/arrangements', ['title' => str_repeat('x', 61), 'levels' => ['near-surface-ozone' => 10]])
            ->assertUnprocessable()->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('arrangements', 0);
    }

    public function test_saving_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/arrangements', ['levels' => ['near-surface-ozone' => 40]])->assertCreated();
        }

        $this->postJson('/api/arrangements', ['levels' => ['near-surface-ozone' => 40]])->assertTooManyRequests();
    }

    public function test_arrangement_api_returns_levels(): void
    {
        $arrangement = Arrangement::create([
            'code' => Arrangement::freshCode(),
            'levels' => ['near-surface-ozone' => 55],
        ]);

        $this->getJson("/api/arrangements/{$arrangement->code}")
            ->assertOk()
            ->assertJsonPath('levels.near-surface-ozone', 55)
            ->assertJsonPath('title', 'Untitled arrangement')
            ->assertJsonMissingPath('id');

        $this->getJson('/api/arrangements/nope1234')->assertNotFound();
    }
}
