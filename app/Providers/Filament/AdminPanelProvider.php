<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Rose,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->font('Outfit')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_START,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <style>
                        /* Custom Login Styling */
                        .fi-simple-main {
                            background-image: radial-gradient(circle at 10% 20%, rgb(30, 41, 59) 0%, rgb(15, 23, 42) 81.3%);
                        }
                        .fi-simple-main .fi-panel-header h1 { color: #fff; font-weight: 800; }
                        .fi-simple-main > div {
                            background: rgba(255, 255, 255, 0.03);
                            backdrop-filter: blur(16px);
                            border-radius: 1.5rem;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                            border: 1px solid rgba(255,255,255,0.05);
                        }
                        .dark .fi-simple-main > div {
                            background: rgba(0, 0, 0, 0.4);
                        }
                        /* Dashboard topbar glass effect */
                        .fi-topbar {
                            background: rgba(255, 255, 255, 0.8) !important;
                            backdrop-filter: blur(12px) !important;
                        }
                        .dark .fi-topbar {
                            background: rgba(17, 24, 39, 0.8) !important;
                        }
                        .fi-sidebar {
                            box-shadow: 4px 0 24px -10px rgba(0,0,0,0.1);
                        }
                    </style>
                '),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // We keep it empty here to let Filament auto-discover the widgets
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
