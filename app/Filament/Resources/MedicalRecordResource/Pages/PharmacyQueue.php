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

class PharmacyQueue extends ListRecords
{
    protected static string $resource = MedicalRecordResource::class;
    
    protected static ?string $navigationLabel = 'Antrian Apoteker';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?int $navigationSort = 4;

    public function getTitle(): string
    {
        return 'Antrian Apoteker';
    }

    protected function getTableQuery(): Builder
    {
        return MedicalRecord::pendingPharmacy()->with(['familyMember', 'assignedUser', 'medicineUsages.medicine']);
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
                TextColumn::make('diagnosis_name')
                    ->label('Diagnosis')
                    ->limit(30),
                TextColumn::make('medicineUsages')
                    ->label('Jumlah Obat')
                    ->formatStateUsing(fn ($record) => $record->medicineUsages->count() . ' obat')
                    ->badge()
                    ->color('success'),
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
                        $this->redirect(MedicalRecordResource::getUrl('view', ['record' => $record]));
                    }),
                    
                Action::make('complete_pharmacy')
                    ->label('Selesai Dispensing')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn (MedicalRecord $record) => $record->assigned_to === auth()->id())
                    ->action(function (MedicalRecord $record) {
                        $record->completeCurrentStage();
                        $this->redirect($this->getResource()::getUrl('pharmacy-queue'));
                    }),
                    
                Tables\Actions\ViewAction::make()
                    ->visible(fn (MedicalRecord $record) => $record->assigned_to === auth()->id()),
            ])
            ->defaultSort('queue_position', 'asc')
            ->poll('30s');
    }
}
