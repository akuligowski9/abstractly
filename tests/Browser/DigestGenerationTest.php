<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DigestGenerationTest extends DuskTestCase
{
    public function test_digest_page_loads_empty_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->assertSee('Weekly Digest')
                ->assertSee('No digest yet');
        });
    }

    public function test_generate_button_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->assertSee('Generate digest');
        });
    }

    public function test_edit_selection_link_navigates_to_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->click('a[href*="/disciplines"]')
                ->pause(500)
                ->assertPathIs('/disciplines');
        });
    }

    public function test_full_flow_generate_digest(): void
    {
        $this->browse(function (Browser $browser) {
            // Step 1: Ensure math is selected (selectAll picks all ready disciplines)
            $browser->visit('/disciplines')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Selection saved');

            // Step 2: Select just 1 source for fast generation
            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectNone"]')
                ->pause(300)
                ->click('[wire\:click="toggleSource(\'biorxiv_recent\')"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Sources updated');

            // Step 3: Generate digest
            $browser->visit('/digest')
                ->click('[wire\:click="generate"]')
                ->waitForText('ELI5', 60)
                ->assertSee('SOLO SWE')
                ->assertSee('INVESTOR')
                ->assertSee('Mathematics');
        });
    }

    public function test_digest_shows_color_coded_sections(): void
    {
        $this->browse(function (Browser $browser) {
            // Ensure math selected
            $browser->visit('/disciplines')
                ->click('[wire\:click="selectAll"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Selection saved');

            $browser->visit('/disciplines/math')
                ->click('[wire\:click="selectNone"]')
                ->pause(300)
                ->click('[wire\:click="toggleSource(\'biorxiv_recent\')"]')
                ->pause(300)
                ->click('[wire\:click="save"]')
                ->waitForText('Sources updated');

            // Generate and verify color-coded sections
            $browser->visit('/digest')
                ->click('[wire\:click="generate"]')
                ->waitForText('ELI5', 60)
                ->assertPresent('.border-green-400')  // ELI5
                ->assertPresent('.border-blue-400')   // SWE
                ->assertPresent('.border-amber-400'); // Investor
        });
    }
}
