<?php

namespace App\Livewire;

use App\Models\Game;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InstantSearch extends Component
{
    public string $query = '';

    public function render(): View
    {
        $results = collect();

        if (mb_strlen(trim($this->query)) >= 2) {
            $term = trim($this->query);
            $results = Game::published()
                ->with('category')
                ->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('short_description', 'like', "%{$term}%");
                })
                ->orderByDesc('play_count')
                ->limit(6)
                ->get();
        }

        return view('livewire.instant-search', [
            'results' => $results,
        ]);
    }
}

