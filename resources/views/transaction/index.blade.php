@extends('layouts.main')

@section('content')

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <h5 class="card-header">Table Transaction</h5>
    <div class="table-responsive text-nowrap p-3">
      <table class="table" id="example">
        <thead>
          <tr class="text-nowrap table-dark">
            <th class="text-white">No</th>
            <th class="text-white">Sender</th>
            <th class="text-white">Receiver</th>
            <th class="text-white">Service</th>
            <th class="text-white">Total</th>
            <th class="text-white">Status</th>

            <th class="text-white">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
          <tr>
            <th scope="row">{{ $loop->iteration }}</th>
            <td>{{ $item->transactionDetail->where('case', 'sender')->first()->user->name ?? '' }}</td>
            <td>{{ $item->transactionDetail->where('case', 'receiver')->first()->user->name ?? '' }}</td>
            <td>{{ $item->payment->service->name }}</td>
            <td>{{ $item->total }}</td>
            <td>{{ $item->status }}</td>
            <td>
              <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="{{ route('user.edit', $item->id) }}"><i class="bx bx-edit-alt me-1"></i>Edit</a>
                  <a class="dropdown-item" href="{{ route('user.destroy', $item->id) }}"><i class="bx bx-trash me-1"></i>Delete</a>
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- / Content -->


@endsection