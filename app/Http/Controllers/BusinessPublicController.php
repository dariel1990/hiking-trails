<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessPublicController extends Controller
{
    public function index(Request $request): \Illuminate\View\View|\Illuminate\Http\JsonResponse
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

        $query->orderByDesc('is_featured')->orderBy('name');

        // AJAX load-more request
        if ($request->ajax()) {
            $typeKey = $request->input('ajax_type');
            $page = (int) $request->input('page', 1);

            $ajaxQuery = $typeKey ? (clone $query)->where('business_type', $typeKey) : clone $query;
            $paginated = $ajaxQuery->paginate(6, ['*'], 'page', $page);

            $html = '';
            foreach ($paginated as $business) {
                $html .= view('businesses._card', compact('business'))->render();
            }

            return response()->json([
                'html' => $html,
                'has_more' => $paginated->hasMorePages(),
                'next_page' => $paginated->currentPage() + 1,
            ]);
        }

        // Normal page load — paginate per type group
        $paginatedGroups = [];
        foreach ($types as $typeKey => $typeLabel) {
            $paginatedGroups[$typeKey] = (clone $query)->where('business_type', $typeKey)->paginate(6);
        }

        // For filtered/flat view
        $businesses = $query->paginate(12);
        $grouped = collect($paginatedGroups);

        return view('businesses.index', compact('grouped', 'types', 'businesses', 'paginatedGroups'));
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
