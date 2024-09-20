@extends('layouts.main')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Fee Create</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('fee.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">Fee Name</label>
          <div class="col-sm-10">
            <input type="text" name="name" class="form-control" id="basic-default-name" placeholder="" />
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">Fee Desc</label>
          <div class="col-sm-10">
            <input type="text" name="desc" class="form-control" id="basic-default-name" placeholder="" />
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">Fee Amount</label>
          <div class="col-sm-10">
            <input type="number" name="price" class="form-control" id="basic-default-name" placeholder="" />
          </div>
        </div>
        <div class="row mb-3">
          <label for="exampleFormControlSelect1" class="form-label col-sm-2">Fee Collector</label>
          <div class="col-sm-10">
            <select class="form-select" name="user_id" id="exampleFormControlSelect1" aria-label="Default select example">
              @foreach ($data as $item)
              <option value="{{ $item->id }}">{{ $item->name }}</option>
              @endforeach
            </select>
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