<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeeDetail;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(){
        $data = Service::all();
        return view('service.index', [
            'data' => $data,
        ]);
    }
    
    public function create(){
        $data = Fee::all();
        return view('service.create', [
            'data' => $data
        ]);
    }
    
    public function edit($id){
        $item = Service::find($id);
        $data = Fee::all();
        // dd($data);
        return view('service.edit', [
            'item' => $item,
            'data' => $data,
        ]);
    }
    
    public function store(Request $request){
        $data = $request->all();
        $feeDetails = $request->fees ?? null;
        $service = Service::create($data);
        if($feeDetails !== null){
            $fees = Fee::whereIn('id', $feeDetails)->get();
            foreach($fees as $fee){
                FeeDetail::create([
                    'service_id' => $service->id,
                    'price' => $fee->price,
                    'fee_id' => $fee->id
                ]);
            } 
        }
        return redirect()->route('service.index')->with('success', 'SUKSES');
    }

    public function update(Request $request, $id){
        $item = Service::find($id);
        $data = $request->fees ?? null;
        if($data == null){
            $item->update([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);
            $item->feeDetail()->delete();
        } else {
            $item->update([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);
           FeeDetail::where('service_id', $item->id)->delete();
           $fees = Fee::whereIn('id', $data)->get();
           foreach($fees as $fee){
            FeeDetail::create([
                'fee_id' => $fee->id,
                'service_id' => $item->id,
                'price' => $fee->price,
            ]);
           }
        }
        return redirect()->route('service.index')->with('success', 'SUKSES');
    }

}
