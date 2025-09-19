<?php

namespace App\Console\Commands;

use App\Services\ChatbotKnowledgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class SyncChatbotAppKnowledge extends Command
{
    protected $signature = 'chatbot:sync-app-knowledge {--dry-run : Tampilkan hasil tanpa menyimpan file}';

    protected $description = 'Bangun file pengetahuan chatbot berdasarkan data aplikasi.';

    public function handle(ChatbotKnowledgeService $service): int
    {
        $this->info('Menghasilkan ringkasan pengetahuan aplikasi...');

        $knowledge = $service->generateKnowledge();

        if ($this->option('dry-run')) {
            $this->line($knowledge);
            $this->newLine();
            $this->comment('Dry run selesai, tidak ada file yang ditulis.');
            return self::SUCCESS;
        }

        try {
            $primaryPath = base_path('app_knowledge.txt');
            File::put($primaryPath, $knowledge);

            $publicPath = public_path('app_knowledge.txt');
            File::ensureDirectoryExists(dirname($publicPath));
            File::put($publicPath, $knowledge);
        } catch (RuntimeException $exception) {
            $this->error('Gagal menyimpan file pengetahuan: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Pengetahuan aplikasi tersimpan di: ' . $primaryPath);
        $this->info('Salinan publik tersedia di: ' . $publicPath);

        $this->comment('Anda dapat menyinkronkan ulang kapan pun setelah data diperbarui.');

        return self::SUCCESS;
    }
}
