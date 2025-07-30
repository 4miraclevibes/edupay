@extends('layouts.app')

@section('title', 'Service Status')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Status Service Update</h4>
                </div>
                <div class="card-body">
                    @if(isset($serviceStatus))
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informasi Payment</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Payment ID:</strong></td>
                                        <td>{{ $serviceStatus['payment_id'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Service:</strong></td>
                                        <td>{{ $serviceStatus['service'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            @if($serviceStatus['success'])
                                                <span class="badge bg-success">Berhasil</span>
                                            @else
                                                <span class="badge bg-danger">Gagal</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pesan:</strong></td>
                                        <td>{{ $serviceStatus['message'] ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Detail API</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>API URL:</strong></td>
                                        <td>{{ $serviceStatus['api_url'] ?? 'N/A' }}</td>
                                    </tr>
                                    @if(isset($serviceStatus['response']))
                                        <tr>
                                            <td><strong>Response:</strong></td>
                                            <td>
                                                <pre class="bg-light p-2 rounded">{{ json_encode($serviceStatus['response'], JSON_PRETTY_PRINT) }}</pre>
                                            </td>
                                        </tr>
                                    @endif
                                    @if(isset($serviceStatus['error']))
                                        <tr>
                                            <td><strong>Error:</strong></td>
                                            <td class="text-danger">{{ $serviceStatus['error'] }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h5>Petunjuk Penggunaan</h5>
                            <p>Untuk mengecek status service update, gunakan endpoint berikut:</p>
                            <code>GET /payment/service-status/{payment_id}</code>
                            <br><br>
                            <p>Contoh: <code>/payment/service-status/1</code></p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
