<?php
// app/Services/AnalyticsService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\FamilyMember;
use App\Models\Family;
use App\Models\Building;
use App\Models\Village;

class AnalyticsService
{
    protected $query;
    protected $dimensions = [];
    protected $metrics = [];
    protected $filters = [];

    public function addDimension($field)
    {
        $this->dimensions[] = $field;
        return $this;
    }

    public function addMetric($field, $operation = 'count')
    {
        $this->metrics[] = [
            'field' => $field,
            'operation' => $operation
        ];
        return $this;
    }

    public function addFilter($field, $operator, $value)
    {
        $this->filters[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    public function analyze()
    {
        $query = $this->buildQuery();
        $results = $this->executeQuery($query);
        return $this->formatResults($results);
    }

    protected function buildQuery()
    {
        $query = FamilyMember::query()
            ->join('families', 'family_members.family_id', '=', 'families.id')
            ->join('buildings', 'families.building_id', '=', 'buildings.id')
            ->join('villages', 'buildings.village_id', '=', 'villages.id');

        foreach ($this->dimensions as $dimension) {
            $query->addSelect($dimension);
            $query->groupBy($dimension);
        }

        foreach ($this->metrics as $metric) {
            $query->addSelect(DB::raw("{$metric['operation']}({$metric['field']}) as {$metric['field']}_{$metric['operation']}"));
        }

        foreach ($this->filters as $filter) {
            $query->where($filter['field'], $filter['operator'], $filter['value']);
        }

        return $query;
    }

    protected function executeQuery($query)
    {
        return $query->get();
    }

    protected function formatResults($results)
    {
        return [
            'data' => $results,
            'dimensions' => $this->dimensions,
            'metrics' => $this->metrics,
            'filters' => $this->filters
        ];
    }
}
