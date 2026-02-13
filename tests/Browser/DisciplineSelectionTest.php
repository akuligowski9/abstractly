<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DisciplineSelectionTest extends DuskTestCase
{
    public function test_disciplines_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->assertSee('Weekly Digest')
                ->assertSee('Disciplines')
                ->assertSee('Mathematics');
        });
    }

    public function test_can_toggle_discipline(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->assertSee('Mathematics')
                ->click('[wire\:click="toggleDiscipline(\'math\')"]')
                ->pause(300);
        });
    }

    public function test_select_all_selects_ready_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->assertSee('selected');
        });
    }

    public function test_select_none_clears_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="selectNone"]')
                ->pause(300)
                ->assertSee('0');
        });
    }

    public function test_save_persists_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->click('[wire\:click="toggleDiscipline(\'math\')"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Selection saved')
                ->assertSee('Selection saved');
        });
    }

    public function test_coming_soon_disciplines_are_disabled(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->assertSee('Coming soon')
                ->assertSee('Earth & Environmental Sciences');
        });
    }

    public function test_sources_link_navigates_to_discipline(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->clickLink('Sources')
                ->waitForText('Sources')
                ->assertPathIs('/disciplines/math');
        });
    }
}
