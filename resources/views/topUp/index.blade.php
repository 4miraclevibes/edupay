@extends('layouts.main')

@section('content')

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <h5 class="card-header">Table TopUP</h5>
    <div class="table-responsive text-nowrap p-3">
      <table class="table" id="example">
        <thead>
          <tr class="text-nowrap table-dark">
            <th class="text-white">No</th>
            <th class="text-white">Nama</th>
            <th class="text-white">Payment Method</th>
            <th class="text-white">Code</th>
            <th class="text-white">Amount</th>
            <th class="text-white">Link Pembayaran</th>
            <th class="text-white">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
          <tr>
            <th scope="row">{{ $loop->iteration }}</th>
            <td>{{ $item->user->name }}</td>
            <td>{{ $item->paymentMethod->name }}</td>
            <td>{{ $item->code }}</td>
            <td>{{ $item->amount }}</td>
            <td><a href="{{ $item->link }}" target="_blank" class="btn btn-success btn-sm">link</a></td>
            <td>{{ $item->status }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- / Content -->


@endsection