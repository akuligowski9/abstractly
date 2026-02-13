<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigationTest extends DuskTestCase
{
    public function test_root_redirects_to_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPathIs('/disciplines');
        });
    }

    public function test_nav_disciplines_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->within('nav', function (Browser $nav) {
                    $nav->clickLink('Disciplines');
                })
                ->assertPathIs('/disciplines');
        });
    }

    public function test_nav_digest_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->click('nav a[href*="/digest"]')
                ->pause(500)
                ->assertPathIs('/digest');
        });
    }

    public function test_nav_logo_links_to_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->within('nav', function (Browser $nav) {
                    $nav->clickLink('Research Digest');
                })
                ->assertPathIs('/disciplines');
        });
    }

    public function test_breadcrumb_from_sources_to_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math')
                ->clickLink('Disciplines')
                ->assertPathIs('/disciplines');
        });
    }

    public function test_breadcrumb_from_preview_to_sources(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->clickLink('Mathematics')
                ->assertPathIs('/disciplines/math');
        });
    }

    public function test_breadcrumb_from_preview_to_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->within('nav:first-of-type', function (Browser $breadcrumb) {
                    $breadcrumb->clickLink('Disciplines');
                })
                ->assertPathIs('/disciplines');
        });
    }

    public function test_digest_breadcrumb_to_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/digest')
                ->click('main a[href*="/disciplines"]')
                ->pause(500)
                ->assertPathIs('/disciplines');
        });
    }

    public function test_active_nav_highlight_on_disciplines(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines')
                ->assertPresent('nav a.text-indigo-600');
        });
    }

    public function test_invalid_discipline_returns_404(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/nonexistent')
                ->assertSee('404');
        });
    }
}
