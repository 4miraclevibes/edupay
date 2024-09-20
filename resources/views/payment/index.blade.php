@extends('layouts.main')

@section('content')

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <h5 class="card-header">Table Payment</h5>
    <div class="table-responsive text-nowrap p-3">
      <table class="table" id="example">
        <thead>
          <tr class="text-nowrap table-dark">
            <th class="text-white">No</th>
            <th class="text-white">Tanggal</th>
            <th class="text-white">Nama</th>
            <th class="text-white">Service</th>
            <th class="text-white">Code</th>
            <th class="text-white">Price</th>
            <th class="text-white">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
          <tr>
            <th scope="row">{{ $loop->iteration }}</th>
            <td>{{ $item->created_at ?? '-' }}</td>
            <td>{{ $item->user->name ?? '-' }}</td>
            <td>{{ $item->service->name ?? '-' }}</td>
            <td>{{ $item->code }}</td>
            <td>{{ $item->total }}</td>
            <td>{{ $item->status ?? '-' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- / Content -->


@endsection