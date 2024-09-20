@extends('layouts.main')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Edit Role</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Role</label>
          <div class="col-sm-10">
            <input type="text" name="name" value="{{ $role->name }}" class="form-control" id="basic-default-name" placeholder="Nama Role" />
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-desc">Deskripsi</label>
          <div class="col-sm-10">
            <input type="text" name="desc" value="{{ $role->desc }}" class="form-control" id="basic-default-desc" placeholder="Deskripsi Role" />
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
