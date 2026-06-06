<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\ResignKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ResignKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = ResignKaryawan::with('karyawan');
        
        if (!empty($request->nama_karyawan)) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
            });
        }
        
        $resign = $query->orderBy('tanggal_resign', 'desc')->paginate(10);
        $resign->appends($request->all());

        return view('resign.index', compact('resign'));
    }

    public function create()
    {
        // Hanya karyawan yang masih aktif yang bisa diresignkan
        $karyawan = Karyawan::where('status_aktif_karyawan', 1)->orderBy('nama_karyawan')->get();
        return view('resign.create', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|exists:karyawan,nik',
            'tanggal_resign' => 'required|date',
            'alasan' => 'nullable|string',
        ]);

        $karyawan = Karyawan::where('nik', $request->nik)->first();
        if (!$karyawan) {
             return Redirect::back()->with(['warning' => 'Data Karyawan Tidak Ditemukan']);
        }

        DB::beginTransaction();
        try {
            // Catat history resign
            ResignKaryawan::create([
                'nik' => $request->nik,
                'tanggal_resign' => $request->tanggal_resign,
                'alasan' => $request->alasan,
                'user_id' => auth()->id()
            ]);

            // Non-aktifkan karyawan
            $karyawan->update([
                'status_aktif_karyawan' => 0,
                'tanggal_nonaktif' => $request->tanggal_resign,
            ]);

            DB::commit();
            return Redirect::route('resign.index')->with(['success' => 'Karyawan Berhasil Di-resign-kan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Memproses Data: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $resign = ResignKaryawan::findOrFail($id);
            $karyawan = Karyawan::where('nik', $resign->nik)->first();

            // Kembalikan karyawan menjadi aktif
            if ($karyawan) {
                $karyawan->update([
                    'status_aktif_karyawan' => 1,
                    'tanggal_nonaktif' => null,
                ]);
            }

            $resign->delete();
            
            DB::commit();
            return Redirect::back()->with(['success' => 'Data Resign Berhasil Dibatalkan, Karyawan Kembali Aktif']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Membatalkan Data: ' . $e->getMessage()]);
        }
    }
}
