<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SourceSelectionTest extends DuskTestCase
{
    public function test_sources_page_loads_for_math(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->assertSee('Mathematics — Sources')
                ->assertSee('Choose which sources')
                ->assertSee('arXiv');
        });
    }

    public function test_breadcrumb_shows_discipline(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->assertSee('Disciplines')
                ->assertSee('Mathematics');
        });
    }

    public function test_source_cards_display_kind_badges(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->assertSee('primary')
                ->assertSee('json');
        });
    }

    public function test_can_toggle_source(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->click('[wire\:click="toggleSource(\'arxiv_math_all\')"]')
                ->pause(300);
        });
    }

    public function test_select_all_sources(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->assertSee('/ ' . count(config('sources.list')) . ' selected');
        });
    }

    public function test_select_none_sources(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="selectNone"]')
                ->pause(300)
                ->assertSee('0');
        });
    }

    public function test_save_sources_shows_confirmation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Sources updated')
                ->assertSee('Sources updated');
        });
    }

    public function test_preview_link_exists_for_sources(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->assertSeeLink('Preview');
        });
    }
}
