<?php

namespace App\Http\Controllers;

use App\Models\SpmAchievementOverride;
use Illuminate\Http\Request;

class SpmAchievementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!method_exists($user, 'hasAnyRole') || !$user->hasAnyRole(['super_admin'])) {
            abort(403);
        }

        $data = $request->validate([
            'spm_sub_indicator_id' => ['required', 'exists:spm_sub_indicators,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'value' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
            'redirect' => ['nullable', 'url'],
        ]);

        $payload = [
            'spm_sub_indicator_id' => $data['spm_sub_indicator_id'],
            'year' => $data['year'],
            'month' => $data['month'] ?? null,
            'village_id' => $data['village_id'] ?? null,
        ];

        SpmAchievementOverride::updateOrCreate(
            $payload,
            [
                'value' => $data['value'],
                'updated_by' => $user->id,
                'note' => $data['note'] ?? null,
            ]
        );

        return redirect()->to($data['redirect'] ?? url()->previous())->with('success', 'Capaian disimpan.');
    }
}

