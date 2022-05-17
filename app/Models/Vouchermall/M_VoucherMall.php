<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_VoucherMall extends Model
{
    public static function search_dt($keyword)
    {
        $q = DB::table('VCH_MST_VOUCHER AS A')
		->select(
            'KD_PERUSAHAAN', 
            'KD_VOUCHER', 
            'NM_VOUCHER', 
            'NO_BARCODE', 
            'MASA_AKTIF', 
            'TGL_EXPIRED', 
            'KETERANGAN', 
            'FLAG_AKTIF',
            'NOMINAL',
            'ROWID'
        )
        ->where(function($wr) use ($keyword){
            $wr->where('A.KD_VOUCHER', 'LIKE', '%'.$keyword.'%');
            $wr->Where('A.NM_VOUCHER', 'LIKE', '%'.$keyword.'%');
            $wr->Where('A.FLAG_AKTIF', '=', 'Y');
        })
        ->orderBy('A.ROWID', 'ASC')
        ->get();

        return $q;
    }

    public static function get_voucher($kd_unit,$kd_lokasi)
	{
        $q = DB::table('VCH_MST_VOUCHER')
		->select(
            'KD_PERUSAHAAN', 
            'KD_VOUCHER', 
            'NM_VOUCHER', 
            'NOMINAL',
            'NO_BARCODE', 
            'MASA_AKTIF', 
            'TGL_EXPIRED', 
            'KETERANGAN', 
            'FLAG_AKTIF',
            'ROWID'
        )
        ->where('KD_PERUSAHAAN', '=', $kd_unit)
		->where('FLAG_AKTIF', '=', 'Y')
		->orderBy('ROWID')
		->get();

    	return $q;
	}

    public static function delete_dt($rowid,$user){

        $q = DB::table('VCH_MST_VOUCHER')
        ->where('ROWID','=',$rowid)
        ->where('FLAG_AKTIF','=','Y')
        ->update(
            [
            'FLAG_AKTIF'    => 'N',
            'USER_UPDATE'    => $user,
            'TGL_UPDATE'     => now()
            ]
        );      
    }

    public static function simpan_dt($kd_unit,$kd_lokasi,$kd_voucher,$nm_voucher,$nominal,$keterangan,$fg_aktif,$user,$tgl){
        DB::transaction(function () use ($kd_unit,$kd_lokasi,$kd_voucher,$nm_voucher,$nominal,$keterangan,$fg_aktif,$user,$tgl) {
            $nominal    = preg_replace("/([^0-9\\.])/i", "", $nominal);
			$nominal    = (is_numeric($nominal)) ? $nominal : 0;
            $nominaldok = (is_numeric(round($nominal))) ? round($nominal) : 0;

            $tglx       = '2022-01-01';

            $kd_fungsi	= 'V';
			$q			= DB::statement("EXEC SP_VCH_NUMBERING '".$kd_unit."','".$kd_fungsi."','4','".$tglx."','','".$user."','".$tgl."'");

			$q = DB::table('VCH_MST_NUMBERING_DTL AS A')
			->select(
                'A.KD_PERUSAHAAN', 
                'A.KD_FUNGSI', 
                DB::RAW('LTRIM(RTRIM(A.FORMATION)) AS FORMATION'),
                'A.SEQUENCE'
            )
			->where('A.KD_PERUSAHAAN', '=', $kd_unit)
			->where('A.KD_FUNGSI', '=', $kd_fungsi)
			->get();

			$no_dok = '';
			foreach ($q as $row) {
				// $no_dok = $row->KD_FUNGSI.$row->FORMATION.$row->SEQUENCE;
                $no_dok = $row->KD_FUNGSI.$row->SEQUENCE.'_'.$nominaldok;
			}

			$q 	= DB::table('VCH_MST_VOUCHER')
			->insert([
                'KD_PERUSAHAAN' => $kd_unit,
                'KD_VOUCHER'    => $no_dok, 
                'NM_VOUCHER'    => $nm_voucher, 
                'NOMINAL'       => $nominal,
                'NO_BARCODE'    => $no_dok, 
                'KETERANGAN'    => $keterangan,
                'FLAG_AKTIF'    => $fg_aktif,
                'USER_ENTRY'    => $user,
                'TGL_ENTRY'     => $tgl
			]);

			return $q;
		});
    }

    public static function update_dt($kd_unit,$kd_lokasi,$kd_voucher,$nm_voucher,$nominal,$keterangan,$fg_aktif,$user,$tgl){
        DB::transaction(function () use ($kd_unit,$kd_lokasi,$kd_voucher,$nm_voucher,$nominal,$keterangan,$fg_aktif,$user,$tgl) {
            $nominal    = preg_replace("/([^0-9\\.])/i", "", $nominal);
			$nominal    = (is_numeric($nominal)) ? $nominal : 0;
            $nominaldok = (is_numeric(round($nominal))) ? round($nominal) : 0;

			$q = DB::table('VCH_MST_VOUCHER')
			->where('KD_PERUSAHAAN', $kd_unit)
            ->where('KD_VOUCHER', $kd_voucher)
			->update([
                'NM_VOUCHER'    => $nm_voucher, 
                'NOMINAL'       => $nominal,
                'KETERANGAN'    => $keterangan,
                'FLAG_AKTIF'    => $fg_aktif,
                'USER_UPDATE'   => $user,
                'TGL_UPDATE'    => $tgl
			]);

			return $q;
		});
    }
}
