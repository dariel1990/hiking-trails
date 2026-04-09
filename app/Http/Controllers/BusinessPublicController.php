<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessPublicController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $types = Business::getBusinessTypes();

        $query = Business::active()->with(['media' => fn ($q) => $q->where('is_primary', true)->orWhere('media_type', 'photo')]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('tagline', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('business_type', $type);
        }

        // Group by type for categorized display
        $businesses = $query->orderByDesc('is_featured')->orderBy('name')->get();

        $grouped = $businesses->groupBy('business_type');

        return view('businesses.index', compact('grouped', 'types', 'businesses'));
    }

    public function show(Business $business): \Illuminate\View\View
    {
        abort_unless($business->is_active, 404);

        $business->load(['media' => fn ($q) => $q->orderBy('sort_order')->orderBy('created_at')]);

        // Related businesses (same type, not this one)
        $related = Business::active()
            ->where('business_type', $business->business_type)
            ->where('id', '!=', $business->id)
            ->with(['media' => fn ($q) => $q->where('is_primary', true)])
            ->limit(3)
            ->get();

        return view('businesses.show', compact('business', 'related'));
    }
}
