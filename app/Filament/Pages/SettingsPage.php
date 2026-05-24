<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static ?string $title = 'Konfigurasi Toko';
    protected static ?int $navigationSort = 10;
    protected string $view = 'filament.pages.settings-page';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('manager');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'shop_name' => Setting::get('shop_name', 'M-POS ENTERPRISE'),
            'shop_address' => Setting::get('shop_address', 'Jl. Contoh POS No. 123, Kota'),
            'shop_phone' => Setting::get('shop_phone', '08123456789'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('shop_name')
                    ->label('Nama Toko')
                    ->required()
                    ->maxLength(255),
                TextInput::make('shop_phone')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Textarea::make('shop_address')
                    ->label('Alamat Toko')
                    ->required()
                    ->maxLength(500),
            ])
            ->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Berhasil disimpan')
            ->body('Data informasi toko berhasil diperbarui.')
            ->success()
            ->send();
    }
}
