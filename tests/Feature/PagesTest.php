<?php

namespace Tests\Feature;

use App\Models\Visualisation;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = ContentSeeder::class;

    protected $seed = true;

    public function test_home_page_lists_all_six_movements(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (Visualisation::ordered() as $v) {
            $response->assertSee($v->title, escape: false)
                ->assertSee("id=\"{$v->slug}\"", escape: false);
        }
        $response->assertSee('The full orchestra');
    }

    public function test_every_learn_more_page_renders_with_its_citations(): void
    {
        foreach (Visualisation::ordered() as $v) {
            $response = $this->get(route('visualisations.show', $v->slug))->assertOk();

            foreach ($v->citations as $citation) {
                $response->assertSee("id=\"ref-{$citation->position}\"", escape: false);
            }
            $response->assertSee($v->data_starts_on->format('j F Y'));
        }
    }

    public function test_citation_markers_are_hidden_in_the_article(): void
    {
        $this->get('/movements/near-surface-temperature')
            ->assertDontSee('[1]')
            ->assertDontSee('class="cite"', escape: false)
            ->assertSee('id="ref-1"', escape: false);
    }

    public function test_static_pages_render(): void
    {
        $this->get('/about')->assertOk()->assertSee('About us');
        $this->get('/credits')->assertOk()->assertSee('Rishwana Hoque')->assertSee('svs.gsfc.nasa.gov/5152');
    }

    public function test_unknown_movement_is_a_404(): void
    {
        $this->get('/movements/does-not-exist')->assertNotFound()->assertSee("isn't in the score", escape: false);
    }

    public function test_no_tracking_parameters_leak_into_citations(): void
    {
        $this->get('/movements/near-surface-ozone')->assertOk()->assertDontSee('utm_source');
    }

    public function test_legend_ticks_are_placed_by_value(): void
    {
        $temperature = Visualisation::where('slug', 'near-surface-temperature')->first();
        $ticks = collect($temperature->legendTicks())->keyBy('label');

        $this->assertSame(0.0, (float) $ticks['-40']['at']);
        $this->assertEqualsWithDelta(1.0, $ticks['40']['at'], 0.07);
        $this->assertTrue($ticks['0']['at'] > $ticks['-20']['at']);
    }
}
