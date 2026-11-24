<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_the_store_overview_and_recent_transactions(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard');

        $response->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Selamat datang kembali, Admin')
            ->assertSee('Penjualan hari ini')
            ->assertSee('Transaksi terbaru')
            ->assertSee('Andi Pratama')
            ->assertSee('Ringkasan pembayaran')
            ->assertSee('Produk terlaris')
            ->assertSee('Admin Toko')
            ->assertSee('Ganti password')
            ->assertSee('Logout')
            ->assertSee('Metode pembayaran');
    }

    public function test_dashboard_exposes_installable_pwa_metadata_and_icons(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/dashboard');
        $manifest = json_decode(
            file_get_contents(public_path('manifest.webmanifest')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $response->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('data-pwa-install', false)
            ->assertSee('Install aplikasi');

        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame(['192x192', '512x512', '512x512'], array_column($manifest['icons'], 'sizes'));

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }
}
