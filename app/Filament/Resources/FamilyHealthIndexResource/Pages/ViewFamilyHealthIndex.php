<?php

namespace App\Filament\Resources\FamilyHealthIndexResource\Pages;

use App\Filament\Resources\FamilyHealthIndexResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;

class ViewFamilyHealthIndex extends ViewRecord
{
    protected static string $resource = FamilyHealthIndexResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Keluarga')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('family.family_number')
                                    ->label('Nomor Keluarga'),
                                TextEntry::make('family.head_name')
                                    ->label('Kepala Keluarga'),
                                TextEntry::make('family.building.village.name')
                                    ->label('Desa'),
                            ]),
                    ]),

                Section::make('Hasil Perhitungan IKS')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('iks_value')
                                    ->label('Nilai IKS')
                                    ->formatStateUsing(fn(float $state): string => number_format($state * 100, 2) . '%'),
                                TextEntry::make('health_status')
                                    ->label('Status Kesehatan')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'Keluarga Sehat' => 'success',
                                        'Keluarga Pra-Sehat' => 'warning',
                                        'Keluarga Tidak Sehat' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('calculated_at')
                                    ->label('Tanggal Perhitungan')
                                    ->dateTime(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('relevant_indicators')
                                    ->label('Indikator Relevan'),
                                TextEntry::make('fulfilled_indicators')
                                    ->label('Indikator Terpenuhi'),
                            ]),
                    ]),

                Tabs::make('Indikator')
                    ->tabs([
                        Tabs\Tab::make('Kesehatan Reproduksi')
                            ->schema([
                                $this->buildIndicatorCard(
                                    '1. Keluarga Berencana (KB)',
                                    'kb_relevant',
                                    'kb_status',
                                    'kb_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '2. Persalinan di Fasilitas Kesehatan',
                                    'birth_facility_relevant',
                                    'birth_facility_status',
                                    'birth_facility_detail'
                                ),
                            ]),

                        Tabs\Tab::make('Kesehatan Ibu dan Anak')
                            ->schema([
                                $this->buildIndicatorCard(
                                    '3. Imunisasi Dasar Lengkap',
                                    'immunization_relevant',
                                    'immunization_status',
                                    'immunization_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '4. ASI Eksklusif',
                                    'exclusive_breastfeeding_relevant',
                                    'exclusive_breastfeeding_status',
                                    'exclusive_breastfeeding_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '5. Pemantauan Pertumbuhan Balita',
                                    'growth_monitoring_relevant',
                                    'growth_monitoring_status',
                                    'growth_monitoring_detail'
                                ),
                            ]),

                        Tabs\Tab::make('Penyakit dan Pengobatan')
                            ->schema([
                                $this->buildIndicatorCard(
                                    '6. Pengobatan TB',
                                    'tb_treatment_relevant',
                                    'tb_treatment_status',
                                    'tb_treatment_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '7. Pengobatan Hipertensi',
                                    'hypertension_treatment_relevant',
                                    'hypertension_treatment_status',
                                    'hypertension_treatment_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '8. Pengobatan Gangguan Jiwa',
                                    'mental_treatment_relevant',
                                    'mental_treatment_status',
                                    'mental_treatment_detail'
                                ),
                            ]),

                        Tabs\Tab::make('Gaya Hidup dan Jaminan')
                            ->schema([
                                $this->buildIndicatorCard(
                                    '9. Anggota Keluarga Tidak Merokok',
                                    'no_smoking_relevant',
                                    'no_smoking_status',
                                    'no_smoking_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '10. Kepesertaan JKN',
                                    'jkn_membership_relevant',
                                    'jkn_membership_status',
                                    'jkn_membership_detail'
                                ),
                            ]),

                        Tabs\Tab::make('Sanitasi')
                            ->schema([
                                $this->buildIndicatorCard(
                                    '11. Akses Air Bersih',
                                    'clean_water_relevant',
                                    'clean_water_status',
                                    'clean_water_detail'
                                ),
                                $this->buildIndicatorCard(
                                    '12. Jamban Sehat',
                                    'sanitary_toilet_relevant',
                                    'sanitary_toilet_status',
                                    'sanitary_toilet_detail'
                                ),
                            ]),
                    ]),
            ]);
    }

    private function buildIndicatorCard(string $title, string $relevantField, string $statusField, string $detailField): Card
    {
        return Card::make()
            ->schema([
                Grid::make()
                    ->schema([
                        TextEntry::make($relevantField)
                            ->label('Relevan')
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Ya' : 'Tidak')
                            ->badge()
                            ->color(fn(bool $state): string => $state ? 'info' : 'gray'),

                        TextEntry::make($statusField)
                            ->label('Status')
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Terpenuhi' : 'Belum Terpenuhi')
                            ->badge()
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                            ->visible(fn($record, $state) => $record->$relevantField),

                        TextEntry::make($detailField)
                            ->label('Detail')
                            ->columnSpanFull(),
                    ]),
            ])
            ->heading($title)
            ->columns(2);
    }
}
