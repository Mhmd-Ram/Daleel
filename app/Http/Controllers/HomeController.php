<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the marketing landing page.
     */
    public function index(): View
    {
        $upcoming = Event::query()
            ->where('is_active', true)
            ->where('end_date_time', '>', now());

        $featuredEvents = (clone $upcoming)
            ->with('category')
            ->orderBy('start_date_time')
            ->take(3)
            ->get();

        $categories = Category::query()
            ->withCount(['events' => fn ($query) => $query->where('is_active', true)->where('end_date_time', '>', now())])
            ->orderByDesc('events_count')
            ->orderBy('name')
            ->get();

        $stats = [
            'events' => (clone $upcoming)->count(),
            'categories' => $categories->count(),
        ];

        return view('home', [
            'featuredEvents' => $featuredEvents,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }
}
