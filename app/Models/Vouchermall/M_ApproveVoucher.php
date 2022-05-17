<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_ApproveVoucher extends Model
{
	public static function show_pengajuan($list_staff, $status_approval)
	{
        $q = DB::table('VCH_TRN_PERMINTAAN AS A')
        ->select(
            'A.KD_PERUSAHAAN',
            'A.KD_LOKASI',
            'A.NO_PERMINTAAN',
            DB::raw('CONVERT(VARCHAR(10),A.TGL_PERMINTAAN,103) TGL_PERMINTAAN'),
            DB::raw('CONVERT(VARCHAR(10),A.TGL_BUTUH,103) TGL_BUTUH'),
            'A.USER_PEMOHON',
            'A.KD_BAGIAN',
            'A.KEPERLUAN',
            'A.TOTAL_JML_VOUCHER' ,
            'A.TOTAL_VOUCHER',
            'A.STATUS',
            'B.GH_FUNCTION_DESC AS TITLE_STATUS',
            'A.ROWID'
        )
        ->join('GS_GEN_HARDCODED AS B', function($join) {
    		$join->on('A.STATUS', '=', 'B.GH_FUNCTION_CODE')
            ->where('B.GH_SYS', '=', 'H')
    		->where('B.GH_FUNCTION_NAME', '=', 'VCH_STATUS');
    	})
        ->where('A.STATUS', '=', $status_approval)
    	->whereIn('A.USER_PEMOHON', $list_staff)
        ->orderBy('A.ROWID', 'DESC')
        ->get();

    	return $q;
	}

    public static function approve_kabag($no_pengajuan_, $status_approval, $user, $tgl)
	{
        return DB::transaction(function () use ($no_pengajuan_, $status_approval, $user, $tgl) {
            $q = DB::table('VCH_TRN_PERMINTAAN')
            ->where('NO_PERMINTAAN', '=', $no_pengajuan_)
            ->update([
                'STATUS' 	    => $status_approval,
                'USER_APPROVE'	=> $user,
                'TGL_APPROVE'	=> $tgl
            ]);

            return $no_pengajuan_;
        });	
	}

    public static function show_detail($kd_unit,$kd_lokasi,$no_pengajuan)
    {
        $q = DB::table('VCH_TRN_PERMINTAAN_DTL AS A')
            ->select(
                'A.KD_PERUSAHAAN',
                'A.KD_LOKASI',
                'A.NO_PERMINTAAN',
                'A.KD_VOUCHER',
                'A.NO_BARIS',
                'A.JML_DIMINTA',
                'A.JML_DIKIRIM',
                'A.JML_APPLY',
                'A.NOMINAL_DIMINTA',
                'A.CATATAN',
                'A.FLAG_AKTIF',
                'A.ROWID'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PERMINTAAN', '=', $no_pengajuan)
            ->where('FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

}
