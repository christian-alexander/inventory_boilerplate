<?php
namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Item::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Description',
            'Quantity',
            'Price',
            'Category',
            'Created At'
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->code,
            $item->name,
            $item->description,
            $item->quantity,
            $item->price,
            $item->category,
            $item->created_at->format('Y-m-d H:i:s')
        ];
    }
}