<?php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use App\Filament\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class NurseQueue extends ListRecords
{
    protected static string $resource = MedicalRecordResource::class;
    
    protected static ?string $navigationLabel = 'Antrian Perawat';
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?int $navigationSort = 2;

    public function getTitle(): string
    {
        return 'Antrian Perawat';
    }

    protected function getTableQuery(): Builder
    {
        return MedicalRecord::pendingNurse()->with(['familyMember', 'assignedUser']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('queue_position_display')
                    ->label('Antrian')
                    ->badge()
                    ->color('info'),
                TextColumn::make('patient_name')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('patient_rm_number')
                    ->label('No. RM')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('chief_complaint')
                    ->label('Keluhan Utama')
                    ->limit(50),
                TextColumn::make('waiting_time')
                    ->label('Waktu Tunggu')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('assignedUser.name')
                    ->label('Ditangani Oleh')
                    ->placeholder('Belum ditangani'),
            ])
            ->actions([
                Action::make('take_patient')
                    ->label('Ambil Pasien')
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')
                    ->visible(fn (MedicalRecord $record) => !$record->assigned_to)
                    ->action(function (MedicalRecord $record) {
                        $record->assignToUser(auth()->id());
                        $this->redirect(MedicalRecordResource::getUrl('edit', ['record' => $record]));
                    }),
                    
                Action::make('complete_nursing')
                    ->label('Selesai Pemeriksaan')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn (MedicalRecord $record) => $record->assigned_to === auth()->id())
                    ->action(function (MedicalRecord $record) {
                        $record->completeCurrentStage();
                        $this->redirect($this->getResource()::getUrl('nurse-queue'));
                    }),
                    
                Tables\Actions\EditAction::make()
                    ->visible(fn (MedicalRecord $record) => $record->assigned_to === auth()->id()),
            ])
            ->defaultSort('queue_position', 'asc')
            ->poll('30s');
    }
}
