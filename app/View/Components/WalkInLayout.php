<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class WalkInLayout extends Component
{
    public function render(): View
    {
        return view('layouts.walk-in');
    }
}