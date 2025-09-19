<?php

namespace Tests\Feature;

use App\Services\ChatbotKnowledgeService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatbotKnowledgeServiceTest extends TestCase
{
    public function test_generate_knowledge_produces_snapshot(): void
    {
        if (!Schema::hasTable('families') || !Schema::hasTable('family_members')) {
            $this->markTestSkipped('Tabel data keluarga belum tersedia di lingkungan pengujian.');
        }

        $service = $this->app->make(ChatbotKnowledgeService::class);

        $knowledge = $service->generateKnowledge();

        $this->assertNotEmpty($knowledge);
        $this->assertStringContainsString('SNAPSHOT INFORMASI SISTEM', $knowledge);
        $this->assertStringContainsString('DASHBOARD /dashboard', $knowledge);
    }
}
