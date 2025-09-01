<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Blade;

class VoiceInputServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Menambahkan method 'enableVoiceInput' ke komponen TextInput Filament
        TextInput::macro('enableVoiceInput', function () {
            $this->extraAttributes(['class' => 'voice-input']);
            return $this;
        });

        // Menambahkan method 'enableVoiceInput' ke komponen Select Filament
        Select::macro('enableVoiceInput', function () {
            $this->extraAttributes(['class' => 'voice-input']);
            return $this;
        });

        // Menambahkan method 'enableVoiceInput' ke komponen Toggle Filament
        Toggle::macro('enableVoiceInput', function () {
            $this->extraAttributes(['class' => 'voice-input']);
            return $this;
        });

        // Tambahkan script voice input ke semua halaman Filament
        Blade::directive('voiceInputScripts', function () {
            return "<?php echo '<script src=\"' . asset('js/voice-input.js') . '\"></script>'; ?>";
        });
    }
}
