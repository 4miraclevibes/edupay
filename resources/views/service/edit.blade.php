@extends('layouts.main')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Fee update for service <span class="fw-bold">{{ $item->name }}</span></h5>
    </div>
    <div class="card-body">
      <form action="{{ route('service.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">Service Name</label>
          <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="basic-default-name" value="{{ $item->name }}" placeholder="" />
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">Service Desc</label>
          <div class="col-sm-10">
            <input type="text" name="desc" class="form-control" id="basic-default-name" value="{{ $item->desc }}" placeholder="" />
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-sm-2 form-check-label" for="defaultCheck3">Fee</label>
          <div class="col-sm-10 row">
            @foreach ($data as $fee)
            <div class="{{ $loop->iteration > 1 ? 'mt-3' : '' }}">
              <input class="form-check-input" type="checkbox" name="fees[]" value="{{ $fee->id }}" {{ $item->feeDetail->where('fee_id', $fee->id)->first() == null ? '' : 'checked' }} id="defaultCheck3" />
              <label class="form-check-label" for="defaultCheck3">{{ $fee->name }} - {{ $fee->price }}</label>
            </div>
            @endforeach
          </div>
        </div>
        <div class="row justify-content-end">
          <div class="col-sm-10">
            <button type="submit" class="btn btn-sm btn-dark mt-3">Kirim</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection