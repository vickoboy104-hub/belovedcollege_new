<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_layout_loads_mobile_correction_layers_and_route_scope(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('mobile-interface.css', false);
        $response->assertSee('mobile-interface-fixes.css', false);
        $response->assertSee('route-dashboard', false);
        $response->assertSee('dashboard-stats-grid', false);
    }

    public function test_mobile_css_keeps_controls_compact_and_navigation_clear(): void
    {
        $mobileCss = file_get_contents(public_path('mobile-interface.css'));
        $followupCss = file_get_contents(public_path('mobile-interface-fixes.css'));

        $this->assertIsString($mobileCss);
        $this->assertIsString($followupCss);
        $this->assertStringContainsString('max-height: 2.95rem', $mobileCss);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr))', $mobileCss);
        $this->assertStringContainsString('.mobile-bottom-nav-link.is-active:has', $followupCss);
        $this->assertStringContainsString('word-break: keep-all', $followupCss);
    }
}
