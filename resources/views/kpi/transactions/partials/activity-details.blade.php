<!-- Activity Points Detail Section -->
@if($detail->indicator->metric_source === 'activity_poin' || $detail->indicator->metric_source === 'activity_count')
    @php
        $activityService = new \App\Services\KpiActivityPointsService();
        $activityDetail = $activityService->getActivityPointsDetail(
            $kpi_employee->nik,
            $kpi_employee->period->start_date,
            $kpi_employee->period->end_date
        );
        $canEditPoint = auth()->user() && (auth()->user()->hasRole('admin') || auth()->user()->hasPermissionTo('kpi.transaction.update'));
    @endphp
    
    <tr class="activity-details-row" style="display: none; background-color: #f8f9fa;">
        <td colspan="7" class="p-3">
            <div class="card border-light">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="ti ti-chart-dots me-2"></i>
                            Detail Aktivitas Karyawan
                        </h6>
                        <small class="text-muted">Setiap aktivitas yang diinput akan ditampilkan di sini dengan poin yang dapat disesuaikan oleh admin</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-link toggle-details" data-bs-toggle="collapse" data-bs-target="#activity-list-{{ $loop->iteration }}">
                        <i class="ti ti-chevron-down"></i>
                    </button>
                </div>
                <div class="card-body p-2" id="activity-list-{{ $loop->iteration }}" style="max-height: 400px; overflow-y: auto;">
                    @if($activityDetail['count'] > 0)
                        <div class="row mb-3">
                            <div class="col-auto">
                                <strong>Total Poin:</strong> 
                                <span class="badge bg-success activity-total-points">{{ number_format($activityDetail['total_poin'], 2) }}</span>
                            </div>
                            <div class="col-auto">
                                <strong>Rata-rata:</strong> 
                                <span class="badge bg-info activity-avg-points">{{ number_format($activityDetail['average'], 2) }}</span>
                            </div>
                            <div class="col-auto">
                                <strong>Jumlah:</strong> 
                                <span class="badge bg-secondary activity-count">{{ $activityDetail['count'] }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <table class="table table-sm table-hover mb-0 activity-points-table" data-kpi-employee-id="{{ $kpi_employee->id }}">
                                <thead>
                                    <tr class="small text-muted table-light">
                                        <th style="width: 1%">No</th>
                                        <th>Aktivitas Karyawan</th>
                                        <th style="width: 12%; text-align: right;">Poin</th>
                                        @if($canEditPoint)
                                            <th style="width: 12%; text-align: right;">Poin Disesuaikan</th>
                                        @endif
                                        <th style="width: 12%;">Tipe</th>
                                        <th style="width: 15%;">Tanggal Input</th>
                                        @if($canEditPoint && $kpi_employee->status != 'approved')
                                            <th style="width: 8%; text-align: center;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activityDetail['activities'] as $activity)
                                        <tr class="small align-middle activity-row" data-activity-id="{{ $activity->id }}">
                                            <td><strong>{{ $loop->iteration }}</strong></td>
                                            <td>
                                                <span title="{{ $activity->aktivitas }}" class="activity-description">
                                                    {{ substr($activity->aktivitas, 0, 80) }}{{ strlen($activity->aktivitas) > 80 ? '...' : '' }}
                                                </span>
                                            </td>
                                            <td style="text-align: right;">
                                                @if(!empty($activity->poin_original))
                                                    <span class="badge bg-warning" title="Nilai original: {{ number_format($activity->poin_original, 2) }}">
                                                        <i class="ti ti-alert-circle"></i> {{ number_format($activity->poin, 2) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">{{ number_format($activity->poin, 2) }}</span>
                                                @endif
                                            </td>
                                            @if($canEditPoint)
                                                <td style="text-align: right;">
                                                    @if($kpi_employee->status != 'approved')
                                                        <input type="number" step="0.01" min="0" max="100" 
                                                               class="form-control form-control-sm activity-poin-input" 
                                                               value="{{ number_format($activity->poin, 2) }}"
                                                               title="Edit poin aktivitas - min 0, max 100">
                                                    @else
                                                        <span class="badge bg-info">{{ number_format($activity->poin, 2) }}</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                @if($activity->tipe_poin === 'auto')
                                                    <span class="badge bg-light text-dark" title="Otomatis dihitung dari word count + photo bonus">
                                                        <i class="ti ti-robot"></i> Auto
                                                    </span>
                                                @elseif($activity->tipe_poin === 'manual')
                                                    <span class="badge bg-warning" title="Input manual oleh {{ $activity->poin_input_by ?? 'Unknown' }}">
                                                        <i class="ti ti-hand-click"></i> Manual
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $activity->tipe_poin }}</span>
                                                @endif
                                                
                                                @if(!empty($activity->poin_adjusted_by))
                                                    <br>
                                                    <small class="text-muted d-block mt-1">
                                                        <i class="ti ti-edit"></i> Disesuaikan {{ $activity->poin_adjusted_at?->diffForHumans() ?? '' }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-muted small">
                                                <div>{{ $activity->created_at->format('d/m/Y') }}</div>
                                                <small>{{ $activity->created_at->format('H:i') }}</small>
                                            </td>
                                            @if($canEditPoint && $kpi_employee->status != 'approved')
                                                <td style="text-align: center;">
                                                    <div class="btn-list flex-nowrap justify-content-center">
                                                        @if(!empty($activity->poin_original))
                                                            <button type="button" class="btn btn-sm btn-ghost-secondary revert-activity-btn" 
                                                                    title="Kembalikan ke nilai original: {{ number_format($activity->poin_original, 2) }}">
                                                                <i class="ti ti-undo"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($canEditPoint && $kpi_employee->status != 'approved')
                            <div class="mt-3 d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-sm btn-secondary reset-activity-form-btn">
                                    <i class="ti ti-refresh"></i> Reset
                                </button>
                                <button type="button" class="btn btn-sm btn-primary save-activity-points-btn" 
                                        data-kpi-employee-id="{{ $kpi_employee->id }}">
                                    <i class="ti ti-device-floppy"></i> Simpan Perubahan Poin
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-inbox" style="font-size: 32px;"></i>
                            <p class="mb-0 mt-2">Belum ada aktivitas dalam periode ini</p>
                            <small>Karyawan dapat menambahkan aktivitas di menu Aktivitas Karyawan</small>
                        </div>
                    @endif
                </div>
            </div>
        </td>
    </tr>
    
@endif

