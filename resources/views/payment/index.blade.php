@extends('layouts.main')

@section('content')

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <h5 class="card-header">Table Transaction Details</h5>
    <div class="table-responsive text-nowrap p-3">
      <table class="table" id="example">
        <thead>
          <tr class="text-nowrap table-dark">
            <th class="text-white">No</th>
            <th class="text-white">Tanggal</th>
            <th class="text-white">Nama</th>
            <th class="text-white">Service</th>
            <th class="text-white">Payment Code</th>
            <th class="text-white">Transaction Code</th>
            <th class="text-white">Amount</th>
            <th class="text-white">Case</th>
            <th class="text-white">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
          <tr>
            <th scope="row">{{ $loop->iteration }}</th>
            <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}</td>
            <td>{{ $item->user->name ?? '-' }}</td>
            <td>{{ $item->transaction->payment->service->name ?? '-' }}</td>
            <td>{{ $item->transaction->payment->code ?? '-' }}</td>
            <td>{{ $item->transaction->transaction_code ?? '-' }}</td>
            <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
            <td>{{ $item->case ?? '-' }}</td>
            <td>
              @if($item->status == 'success')
                <span class="badge bg-success">Success</span>
              @elseif($item->status == 'pending')
                <span class="badge bg-warning">Pending</span>
              @elseif($item->status == 'failed')
                <span class="badge bg-danger">Failed</span>
              @else
                <span class="badge bg-secondary">{{ $item->status ?? '-' }}</span>
              @endif
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
