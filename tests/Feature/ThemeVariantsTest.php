<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ThemeVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_save_each_supported_theme_variant(): void
    {
        $admin = $this->admin();

        foreach (['light-corporate', 'dark-corporate', 'colourful-professional'] as $preset) {
            $this->actingAs($admin)
                ->post(route('admin.settings.update'), [
                    'group' => 'school',
                    'settings_section' => 'theme-colors',
                    'theme_preset' => $preset,
                ])
                ->assertRedirect();

            $this->assertSame($preset, Setting::getValue('theme_preset'));
        }
    }

    public function test_selected_theme_is_exposed_as_a_document_class(): void
    {
        $admin = $this->admin();

        foreach (['light-corporate', 'dark-corporate', 'colourful-professional'] as $preset) {
            Setting::setMany(['theme_preset' => $preset], 'school');
            View::share('schoolSettings', Setting::publicSettings());

            $this->actingAs($admin)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('class="theme-'.$preset.'"', false)
                ->assertSee('data-theme-preset="'.$preset.'"', false)
                ->assertSee('theme-variants.css', false);
        }
    }

    public function test_theme_settings_present_the_three_supported_variants(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.settings', ['section' => 'theme-colors']))
            ->assertOk()
            ->assertSee('Light Corporate (Clean &amp; Professional)', false)
            ->assertSee('Dark Corporate (Dark Mode)', false)
            ->assertSee('Colourful Professional (Vibrant Dashboard)', false)
            ->assertSee('theme-settings-presets.css', false);
    }

    public function test_theme_styles_define_high_contrast_dark_and_distinct_colourful_surfaces(): void
    {
        $variants = file_get_contents(public_path('theme-variants.css'));
        $publicVariants = file_get_contents(public_path('theme-public-variants.css'));

        $this->assertIsString($variants);
        $this->assertStringContainsString('html.theme-dark-corporate', $variants);
        $this->assertStringContainsString('--theme-text: #f8fafc', $variants);
        $this->assertStringContainsString('--dashboard-card-text: #ffffff', $variants);
        $this->assertStringContainsString('html.theme-colourful-professional', $variants);
        $this->assertStringContainsString('radial-gradient', $variants);
        $this->assertStringContainsString('html.theme-dark-corporate .public-shell', $publicVariants);
        $this->assertStringContainsString('html.theme-colourful-professional .public-shell', $publicVariants);
    }

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
