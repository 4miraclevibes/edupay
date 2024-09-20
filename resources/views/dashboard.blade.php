@extends('layouts.main')

@section('content')

<!-- Content -->

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title text-primary mb-2">Selamat datang, {{ Auth::user()->name }}! 👋</h2>
                            <p class="mb-0">Semoga hari Anda menyenangkan dan produktif.</p>
                            @if (Auth::user()->wallet)
                            <p class="mb-0">Saldo: Rp {{ number_format(Auth::user()->wallet->balance, 0, ',', '.') }}</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="mb-0">{{ now()->format('l, d F Y') }}</p>
                            <p class="mb-0">{{ now()->format('H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (Auth::user()->role->name == 'ADMIN')
    <div class="row mt-4">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Konfirmasi Pembayaran</h5>
                    <form action="{{ route('payment.paymentSuccess') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="code" class="form-label">Kode Pembayaran</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN</label>
                            <input type="password" class="form-control" id="pin" name="pin" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top Up Saldo</h5>
                    <form action="{{ route('topup.store') }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="mb-3">
                            <label for="amount" class="form-label">Jumlah Top Up</label>
                            <input type="number" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN</label>
                            <input type="password" class="form-control" id="pin" name="pin" required>
                        </div>
                        <div class="mb-3">
                            <label for="method" class="form-label">Metode Pembayaran</label>
                            <select class="form-select" id="method" name="method" required>
                                <option value="bni_va">BNI Virtual Account</option>
                                <option value="bca_va">BCA Virtual Account</option>
                                <option value="bri_va">BRI Virtual Account</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Top Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
        @if (Auth::user()->wallet)
        <div class="row mt-4">
            <div class="col-12 col-md-6 mb-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Konfirmasi Pembayaran</h5>
                        <form action="{{ route('payment.paymentSuccess') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="code" class="form-label">Kode Pembayaran</label>
                                <input type="text" class="form-control" id="code" name="code" required>
                            </div>
                            <div class="mb-3">
                                <label for="pin" class="form-label">PIN</label>
                                <input type="password" class="form-control" id="pin" name="pin" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Up Saldo</h5>
                        <form action="{{ route('payment.topUpUser') }}" method="POST">
                            @csrf
                            @method('POST')
                            <div class="mb-3">
                                <label for="total" class="form-label">Jumlah Top Up</label>
                                <input type="number" class="form-control" id="total" name="total" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Top Up</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mt-4">
            <div class="col-12 col-md-6 mb-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Buat Wallet</h5>
                        <form action="{{ route('wallet.store') }}" method="POST">
                            @csrf
                            @method('POST')
                            <div class="mb-3">
                                <label for="pin" class="form-label">PIN</label>
                                <input type="number" class="form-control" id="pin" name="pin" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>

<!-- / Content -->

@endsection