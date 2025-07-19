<?php

namespace App\Exports;

use App\Models\IncomingItem;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ItemReportExport implements FromView
{
    public function view(): View
    {
        return view('exports.stock_report', [
            'incomingItems' => IncomingItem::all()
        ]);
    }
} 