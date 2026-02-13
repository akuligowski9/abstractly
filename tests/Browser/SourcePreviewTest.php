<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SourcePreviewTest extends DuskTestCase
{
    public function test_preview_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->assertSee('Preview:')
                ->assertSee('arXiv')
                ->assertSee('Mathematics (all)');
        });
    }

    public function test_preview_shows_items(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->assertSee('#1')
                ->assertSee('Mock Paper 1');
        });
    }

    public function test_preview_breadcrumb_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->assertSee('Disciplines')
                ->assertSee('Mathematics')
                ->assertSee('Preview');
        });
    }

    public function test_preview_shows_source_url(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->assertSee('export.arxiv.org');
        });
    }

    public function test_preview_back_link_returns_to_sources(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/arxiv_math_all/preview')
                ->clickLink('Back to Mathematics')
                ->assertPathIs('/disciplines/math');
        });
    }

    public function test_preview_biorxiv_source(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/biorxiv_recent/preview')
                ->assertSee('Preview:')
                ->assertSee('bioRxiv')
                ->assertSee('Mock biorxiv Paper 1');
        });
    }

    public function test_preview_invalid_source_returns_404(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/disciplines/math/sources/nonexistent/preview')
                ->assertSee('404');
        });
    }
}
