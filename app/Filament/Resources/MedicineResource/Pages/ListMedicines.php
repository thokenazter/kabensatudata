<?php

namespace App\Filament\Resources\MedicineResource\Pages;

use App\Filament\Resources\MedicineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedicines extends ListRecords
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn () => \App\Models\Medicine::count()),
            
            'active' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => \App\Models\Medicine::where('is_active', true)->count()),
            
            'low_stock' => Tab::make('Stok Menipis')
                ->modifyQueryUsing(fn (Builder $query) => $query->lowStock()->where('stock_quantity', '>', 0))
                ->badge(fn () => \App\Models\Medicine::lowStock()->where('stock_quantity', '>', 0)->count())
                ->badgeColor('warning'),
            
            'out_of_stock' => Tab::make('Habis')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock_quantity', '<=', 0))
                ->badge(fn () => \App\Models\Medicine::where('stock_quantity', '<=', 0)->count())
                ->badgeColor('danger'),
        ];
    }
}