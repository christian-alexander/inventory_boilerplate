<?php
namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ItemsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemController extends Controller
{
    public function index()
    {
        return view('items.index');
    }

    public function getData()
    {
        $items = Item::select(['id', 'code', 'name', 'description', 'quantity', 'price', 'category']);
        
        return DataTables::of($items)
            ->addColumn('action', function($item){
                return '
                    <button class="btn btn-sm btn-primary edit-btn" data-id="'.$item->id.'">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="'.$item->id.'">Delete</button>
                ';
            })
            ->editColumn('price', function($item){
                return 'Rp ' . number_format($item->price, 2);
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:items',
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category' => 'required'
        ]);

        Item::create($request->all());
        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);
        
        $request->validate([
            'code' => 'required|unique:items,code,' . $id,
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category' => 'required'
        ]);

        $item->update($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Item::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function exportExcel()
    {
        return Excel::download(new ItemsExport, 'items.xlsx');
    }

    public function exportPdf()
    {
        $items = Item::all();
        $pdf = Pdf::loadView('items.pdf', compact('items'));
        return $pdf->download('items.pdf');
    }
}