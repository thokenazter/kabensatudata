
  Class "App\Filament\Resources\MedicalRecordResource\Pages\CurrentServing" not found

  at app/Filament/Resources/MedicalRecordResource.php:1183
    1179▕
    1180▕             // NEW: Queue Dashboard & Display
    1181▕             'queue-dashboard' => Pages\QueueDashboard::route('/queue-dashboard'),
    1182▕             'queue-display' => Pages\QueueDisplay::route('/queue-display'),
  ➜ 1183▕             'current-serving' => Pages\CurrentServing::route('/current-serving/{role}'),
    1184▕         ];
    1185▕     }
    1186▕
    1187▕     // NEW: Static methods untuk queue management

      +37 vendor frames

  38  [internal]:0
      Illuminate\Foundation\Application::Illuminate\Foundation\{closure}(Object(Filament\FilamentServiceProvider), "Filament\FilamentServiceProvider")
      +6 vendor frames

  45  artisan:13
      Illuminate\Foundation\Application::handleCommand(Object(Symfony\Component\Console\Input\ArgvInput))