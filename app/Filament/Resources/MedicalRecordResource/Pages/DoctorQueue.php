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

class DoctorQueue extends ListRecords
{
    protected static string $resource = MedicalRecordResource::class;
    
    protected static ?string $navigationLabel = 'Antrian Dokter';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?int $navigationSort = 3;

    public function getTitle(): string
    {
        return 'Antrian Dokter';
    }

    protected function getTableQuery(): Builder
    {
        return MedicalRecord::pendingDoctor()->with(['familyMember', 'assignedUser']);
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
                    ->limit(40),
                TextColumn::make('systolic')
                    ->label('TD Sistolik')
                    ->suffix(' mmHg'),
                TextColumn::make('diastolic')
                    ->label('TD Diastolik')
                    ->suffix(' mmHg'),
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
                    
                Action::make('complete_diagnosis')
                    ->label('Selesai Diagnosis')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn (MedicalRecord $record) => $record->assigned_to === auth()->id())
                    ->action(function (MedicalRecord $record) {
                        $record->completeCurrentStage();
                        $this->redirect($this->getResource()::getUrl('doctor-queue'));
                    }),
                    
                Tables\Actions\EditAction::make()
                    ->visible(fn (MedicalRecord $record) => $record->assigned_to === auth()->id()),
            ])
            ->defaultSort('queue_position', 'asc')
            ->poll('30s');
    }
}
