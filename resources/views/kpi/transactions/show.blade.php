@extends('layouts.app')
@section('titlepage', 'Detail & Realisasi KPI')
@section('content')
@section('navigasi')
    <span>Detail & Realisasi KPI</span>
@endsection
<div class="row">
    <div class="col-12">
        <form action="{{ route('kpi.transactions.update', $kpi_employee->id) }}?{{ http_build_query(request()->query()) }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-12">
                     <div class="card">

                        <div class="card-body p-3">
                            @php
                                $bgColor = !empty($general_setting->theme_color_1) ? $general_setting->theme_color_1 : '#18b76f';
                            @endphp
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    @if(!empty($kpi_employee->karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $kpi_employee->karyawan->foto))
                                        <img src="{{ getfotoKaryawan($kpi_employee->karyawan->foto) }}" class="avatar avatar-md rounded" style="object-fit: cover;">
                                    @else
                                        <span class="avatar avatar-md rounded d-flex justify-content-center align-items-center text-white fw-bold" 
                                              style="width: 46px; height: 46px; font-size: 20px; background-color: {{ $bgColor }};">
                                            {{ substr($kpi_employee->karyawan->nama_karyawan, 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-dark">{{ $kpi_employee->karyawan->nama_karyawan }}</div>
                                    <div class="text-secondary small mb-1">{{ $kpi_employee->karyawan->jabatan->nama_jabatan }} | {{ $kpi_employee->karyawan->departemen->nama_dept ?? '-' }}</div>
                                    <div class="d-flex align-items-center flex-wrap gap-3 small text-secondary">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-id me-1"></i>
                                            {{ $kpi_employee->karyawan->nik_show ?? $kpi_employee->karyawan->nik }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-calendar-event me-1"></i>
                                            Join: {{ date('d M Y', strtotime($kpi_employee->karyawan->tanggal_masuk)) }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-hourglass me-1"></i>
                                            @php
                                                $awal = new DateTime($kpi_employee->karyawan->tanggal_masuk);
                                                $akhir = new DateTime();
                                                $masa_kerja = $akhir->diff($awal);
                                            @endphp
                                            {{ $masa_kerja->y . ' Th ' . $masa_kerja->m . ' Bln' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto border-start ps-3 d-none d-md-block">
                                    <div class="text-muted small">Periode KPI</div>
                                    <div class="fw-bold">{{ $kpi_employee->period->nama_periode }}</div>
                                    <div class="text-secondary small">
                                        {{ date('d M Y', strtotime($kpi_employee->period->start_date)) }} - {{ date('d M Y', strtotime($kpi_employee->period->end_date)) }}
                                    </div>
                                </div>
                                 <div class="col-md-2 d-none d-md-block border-start ps-3">
                                     <div class="card border-0" style="background-color: {{ $bgColor }};">
                                         <div class="card-body p-2 text-center text-white">
                                             <div class="text-uppercase text-white-50 small fw-bold">Grade</div>
                                             <div class="display-6 fw-bold text-white" id="gradeDisplay">{{ $kpi_employee->grade ?? '-' }}</div>
                                             <div class="text-white-50 small">
                                                 Nilai: <span class="fw-bold text-white" id="totalNilaiDisplay">{{ number_format($kpi_employee->total_nilai, 2) }}</span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                <div class="col-md-2 d-none d-md-block border-start ps-3 text-end">
                                    <div class="mb-2">
                                        @if ($kpi_employee->status == 'draft')
                                            <span class="badge bg-warning">Draft</span>
                                        @elseif ($kpi_employee->status == 'submitted')
                                            <span class="badge bg-info">Submitted</span>
                                        @elseif ($kpi_employee->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $kpi_employee->status }}</span>
                                        @endif
                                    </div>
                                    <div class="btn-list justify-content-end">
                                         <a href="{{ route('kpi.transactions.index', request()->query()) }}" class="btn btn-secondary btn-sm" title="Kembali">
                                             <i class="ti ti-arrow-left"></i> Kembali
                                         </a>
                                         <a href="{{ route('kpi.transactions.print', $kpi_employee->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Print">
                                             <i class="ti ti-printer"></i>
                                         </a>
                                        @can('kpi.transaction.approve')
                                            @if($kpi_employee->status == 'submitted')
                                            <button type="submit" formaction="{{ route('kpi.transactions.approve', $kpi_employee->id) }}" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda Yakin Ingin Menyetujui KPI Ini?');" title="Approve">
                                                <i class="ti ti-check"></i>
                                            </button>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                     <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Input Realisasi KPI</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive rounded-3 overflow-hidden">
                                <table class="table table-bordered table-striped table-hover table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 1%; white-space: nowrap;">No</th>
                                            <th>Nama Indikator</th>
                                            <th style="width: 1%; white-space: nowrap;">Satuan</th>
                                            <th style="width: 1%; white-space: nowrap;">Target</th>
                                            <th style="width: 1%; white-space: nowrap;">Bobot</th>
                                            <th style="width: 10%; white-space: nowrap;">Realisasi</th>
                                            <th style="width: 1%; white-space: nowrap;">Nilai</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                     @foreach ($kpi_employee->details as $detail)
                                         <tr class="align-middle kpi-row" data-target="{{ $detail->target }}" data-jenis-target="{{ $detail->indicator->jenis_target }}" data-bobot="{{ $detail->bobot }}">
                                             <td>
                                                 {{ $loop->iteration }}
                                                 <input type="hidden" name="detail_id[]" value="{{ $detail->id }}">
                                             </td>
                                             <td>
                                                 {{ $detail->indicator->nama_indikator }} <br>
                                                 <small class="text-muted">{{ $detail->indicator->deskripsi }}</small>
                                             </td>
                                             <td>{{ $detail->indicator->satuan }}</td>
                                             <td class="text-center">
                                                 <div class="d-flex align-items-center justify-content-center gap-1">
                                                     {{ $detail->target }}
                                                     @if ($detail->indicator->jenis_target == 'min')
                                                         <i class="ti ti-arrow-down text-danger" title="Minimal (Semakin Kecil Baik)"></i>
                                                     @else
                                                         <i class="ti ti-arrow-up text-success" title="Maksimal (Semakin Besar Baik)"></i>
                                                     @endif
                                                 </div>
                                             </td>
                                             <td class="text-center">{{ $detail->bobot }}</td>
                                             <td>
                                                 @if(strtolower($detail->indicator->satuan) == 'skala')
                                                     @if($kpi_employee->status == 'approved' || $detail->indicator->mode == 'auto')
                                                         <input type="number" step="0.01" class="form-control realisasi-input" name="realisasi[]" value="{{ $detail->realisasi }}" readonly>
                                                     @else
                                                         <select class="form-select realisasi-input" name="realisasi[]" required>
                                                             <option value="">Pilih Skala</option>
                                                             <option value="1" {{ (int)$detail->realisasi == 1 ? 'selected' : '' }}>1 (Sangat Kurang)</option>
                                                             <option value="2" {{ (int)$detail->realisasi == 2 ? 'selected' : '' }}>2 (Kurang)</option>
                                                             <option value="3" {{ (int)$detail->realisasi == 3 ? 'selected' : '' }}>3 (Cukup)</option>
                                                             <option value="4" {{ (int)$detail->realisasi == 4 ? 'selected' : '' }}>4 (Baik)</option>
                                                             <option value="5" {{ (int)$detail->realisasi == 5 ? 'selected' : '' }}>5 (Sangat Baik)</option>
                                                         </select>
                                                     @endif
                                                 @else
                                                     <input type="number" step="0.01" class="form-control realisasi-input" name="realisasi[]" value="{{ $detail->realisasi }}" required {{ $kpi_employee->status == 'approved' || $detail->indicator->mode == 'auto'  ? 'readonly' : '' }}>
                                                 @endif
                                                 @if($detail->indicator->mode == 'auto')
                                                     <small class="text-muted fst-italic d-block mt-1">(Auto: {{ $detail->indicator->metric_source }})</small>
                                                 @endif
                                             </td>
                                             <td class="text-end fw-bold skor-display">
                                                 {{ number_format($detail->skor, 2) }}
                                             </td>
                                         </tr>
                                         
                                         {{-- Include activity details jika indikator menggunakan activity points --}}
                                         @if($detail->indicator->metric_source === 'activity_poin' || $detail->indicator->metric_source === 'activity_count')
                                             @include('kpi.transactions.partials.activity-details')
                                         @endif
                                     @endforeach
                                </tbody>
                            </table>
                        </div>
                            
                            <div class="mt-3 d-flex gap-2">
                                 <a href="{{ route('kpi.transactions.index', request()->query()) }}" class="btn btn-secondary {{ $kpi_employee->status != 'approved' ? 'w-50' : 'w-100' }}">
                                     <i class="ti ti-arrow-left me-1"></i> Kembali
                                 </a>
                                 @if($kpi_employee->status != 'approved')
                                 <button type="submit" class="btn btn-primary w-50">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                         <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                         <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"></path>
                                         <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                         <path d="M14 4l0 4l-6 0l0 -4"></path>
                                     </svg>
                                     Simpan Realisasi
                                 </button>
                                 @endif
                             </div>
                        </div>
                     </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('myscript')
<script>
$(document).ready(function() {
    function calculateKPI() {
        let totalScore = 0;
        
        $('.kpi-row').each(function() {
            const row = $(this);
            const target = parseFloat(row.data('target')) || 0;
            const jenisTarget = row.data('jenis-target');
            const bobot = parseFloat(row.data('bobot')) || 0;
            const realisasiVal = row.find('.realisasi-input').val();
            const realisasi = parseFloat(realisasiVal);
            
            let score = 0;
            
            if (!isNaN(realisasi)) {
                if (jenisTarget === 'max') {
                    if (target > 0) {
                        score = (realisasi / target) * bobot;
                    }
                } else { // min
                    if (realisasi === 0) {
                        score = bobot;
                    } else {
                        score = (target / realisasi) * bobot;
                    }
                }
                
                // Skor tidak boleh melebihi bobot (poin maksimal)
                if (score > bobot) {
                    score = bobot;
                }
            }
            
            // Update individual display
            row.find('.skor-display').text(score.toFixed(2));
            totalScore += score;
        });
        
        // Update total score display
        $('#totalNilaiDisplay').text(totalScore.toFixed(2));
        
        // Update Grade
        let grade = 'E';
        if (totalScore >= 90) grade = 'A';
        else if (totalScore >= 80) grade = 'B';
        else if (totalScore >= 70) grade = 'C';
        else if (totalScore >= 60) grade = 'D';
        
        $('#gradeDisplay').text(grade);
    }
    
    // Activity Points Management
    
    // Toggle activity details row
    $(document).on('click', '.activity-toggle-btn', function(e) {
        e.preventDefault();
        const activityRow = $(this).closest('tr');
        const detailsRow = activityRow.next('.activity-details-row');
        
        if (detailsRow.length) {
            detailsRow.slideToggle(300);
            $(this).find('i').toggleClass('ti-info-circle ti-chevron-down');
        }
    });

    // Save activity points with bulk update
    $(document).on('click', '.save-activity-points-btn', function() {
        const btn = $(this);
        const kpiEmployeeId = btn.data('kpi-employee-id');
        const table = btn.closest('.activity-points-table');
        
        // Collect all changed activity points
        const activities = [];
        table.find('.activity-row').each(function() {
            const activityId = $(this).data('activity-id');
            const poinInput = $(this).find('.activity-poin-input');
            const poinValue = parseFloat(poinInput.val()) || 0;
            
            activities.push({
                id: activityId,
                poin: poinValue
            });
        });

        if (activities.length === 0) {
            alert('Tidak ada aktivitas untuk disimpan');
            return;
        }

        btn.prop('disabled', true);
        btn.html('<i class="ti ti-loader"></i> Menyimpan...');

        $.ajax({
            url: '/api/activity-point/bulk-update',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                kpi_employee_id: kpiEmployeeId,
                activities: activities
            }),
            success: function(response) {
                if (response.success) {
                    // Update display values
                    table.find('.activity-row').each(function() {
                        const poinInput = $(this).find('.activity-poin-input');
                        if (poinInput.length) {
                            const newValue = parseFloat(poinInput.val()) || 0;
                            $(this).find('.activity-poin-input').val(newValue.toFixed(2));
                        }
                    });

                    // Update totals
                    updateActivityTotals(table);
                    
                    // Recalculate KPI
                    calculateKPI();
                    
                    // Show success message
                    showSuccessAlert('Poin aktivitas berhasil disimpan! KPI telah dihitung ulang.');
                    
                    // Update grade and total nilai
                    if (response.data.total_nilai_kpi) {
                        $('#totalNilaiDisplay').text(parseFloat(response.data.total_nilai_kpi).toFixed(2));
                    }
                    if (response.data.grade) {
                        $('#gradeDisplay').text(response.data.grade);
                    }
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Gagal menyimpan poin aktivitas';
                showErrorAlert(errorMsg);
                console.error('Error:', xhr);
            },
            complete: function() {
                btn.prop('disabled', false);
                btn.html('<i class="ti ti-device-floppy"></i> Simpan Perubahan Poin');
            }
        });
    });

    // Revert individual activity point
    $(document).on('click', '.revert-activity-btn', function(e) {
        e.preventDefault();
        
        if (!confirm('Kembalikan poin aktivitas ini ke nilai original?')) {
            return;
        }

        const btn = $(this);
        const row = btn.closest('.activity-row');
        const activityId = row.data('activity-id');
        
        btn.prop('disabled', true);
        btn.html('<i class="ti ti-loader"></i>');

        $.ajax({
            url: '/api/activity-point/' + activityId + '/revert',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    // Update poin input value
                    const poinInput = row.find('.activity-poin-input');
                    if (poinInput.length) {
                        poinInput.val(parseFloat(response.data.poin).toFixed(2));
                    }
                    
                    // Update poin display badge
                    const poinBadge = row.find('td:nth-child(3) .badge');
                    if (poinBadge.length) {
                        poinBadge.removeClass('bg-warning').addClass('bg-success');
                        poinBadge.html(parseFloat(response.data.poin).toFixed(2));
                    }

                    // Update activity totals
                    const table = row.closest('.activity-points-table');
                    updateActivityTotals(table);
                    
                    // Recalculate KPI
                    calculateKPI();
                    
                    showSuccessAlert('Poin aktivitas berhasil dikembalikan ke nilai original');
                    
                    btn.html('<i class="ti ti-undo"></i>');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Gagal mengembalikan poin';
                showErrorAlert(errorMsg);
                console.error('Error:', xhr);
                btn.html('<i class="ti ti-undo"></i>');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Reset activity form
    $(document).on('click', '.reset-activity-form-btn', function() {
        if (confirm('Reset semua perubahan poin ke nilai yang disimpan?')) {
            location.reload();
        }
    });

    // Update activity totals display
    function updateActivityTotals(table) {
        let totalPoin = 0;
        let count = 0;

        table.find('.activity-row').each(function() {
            const poinInput = $(this).find('.activity-poin-input');
            if (poinInput.length) {
                totalPoin += parseFloat(poinInput.val()) || 0;
                count++;
            } else {
                const badgeText = $(this).find('td:nth-child(3)').text().trim();
                totalPoin += parseFloat(badgeText) || 0;
                count++;
            }
        });

        // Update total poin display
        table.closest('.activity-points-table').parent().find('.activity-total-points').text(totalPoin.toFixed(2));
        
        // Update average
        if (count > 0) {
            const average = totalPoin / count;
            table.closest('.activity-points-table').parent().find('.activity-avg-points').text(average.toFixed(2));
        }
    }

    // Show success alert
    function showSuccessAlert(message) {
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('form').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert-success').fadeOut(function() { $(this).remove(); });
        }, 5000);
    }

    // Show error alert
    function showErrorAlert(message) {
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-circle me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('form').prepend(alertHtml);
        
        // Auto-dismiss after 7 seconds
        setTimeout(function() {
            $('.alert-danger').fadeOut(function() { $(this).remove(); });
        }, 7000);
    }
    
    // Listen to changes in realisasi inputs
    $(document).on('input change', '.realisasi-input', function() {
        calculateKPI();
    });
});
</script>
@endpush
