<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengiriman - CargoTrace</title>
    <!-- Memanggil CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Manajemen Pengiriman Kargo</h2>
            <a href="{{ route('shipments.create') }}" class="btn btn-primary">+ Buat Resi Baru</a>
        </div>

        <!-- Menampilkan pesan sukses jika ada -->
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-4">No. Resi</th>
                                <th>Pelanggan</th>
                                <th>Maskapai</th>
                                <th>Rute (Asal &rarr; Tujuan)</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shipments as $shipment)
                                <tr>
                                    <td class="ps-4"><span class="fw-bold text-primary">{{ $shipment->shipment_number }}</span></td>
                                    <td>{{ $shipment->customer->name ?? '-' }}</td>
                                    <td>{{ $shipment->carrier->name ?? '-' }}</td>
                                    <td>
                                        {{ $shipment->origin->name ?? '-' }} &rarr; {{ $shipment->destination->name ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $shipment->current_status->value ?? $shipment->current_status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-sm btn-info text-white">Detail</a>
                                        <a href="{{ route('shipments.edit', $shipment->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <em>Belum ada data pengiriman kargo saat ini.</em>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Menampilkan tombol Pagination (Halaman 1, 2, 3...) -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $shipments->links() }}
        </div>
    </div>

    <!-- Memanggil JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>