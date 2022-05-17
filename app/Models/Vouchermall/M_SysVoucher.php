<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_SysVoucher extends Model
{
    public static function get_cara_bayar()
    {
        $q = DB::table('VCH_MST_CARABAYAR')
            ->select(
                'KD_BAYAR', 
                'NM_BAYAR'
            )
            ->where('FLAG_AKTIF', '=', 'Y')
            ->orderBy('ROWID', 'ASC')
            ->get();

        return $q;
    }

    public static function get_data_user($user_id)
    {
    	$q = DB::connection('hrms')
    	->table('MST_KARYAWAN AS A')
    	->select(
            'A.NAMA', 
            'C.KD_GRUP_BAGIAN AS KD_DEPARTEMEN', 
            'C.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN'
        )
    	->join('TBL_BAGIAN AS B', 'A.KD_BAGIAN', '=', 'B.KD_BAGIAN')
        ->join('TBL_GRUP_BAGIAN AS C', 'B.KD_BAGIAN', '=', 'C.KD_GRUP_BAGIAN')
    	->where('A.NO_INDUK', '=', $user_id)
    	->get();

    	return $q;
    }

    public static function get_data_voucher($kd_unit)
	{
        $q = DB::table('VCH_MST_VOUCHER')
		->select(
            'KD_VOUCHER', 
            'NOMINAL'
        )
        ->where('KD_PERUSAHAAN', '=', $kd_unit)
		->where('FLAG_AKTIF', '=', 'Y')
		->orderBy('ROWID')
		->get();

    	return $q;
	}

    public static function get_voucher($kd_unit,$kd_lokasi,$kode)
	{
        $q = DB::table('VCH_MST_VOUCHER')
		->select(
            'NO_BARCODE'
        )
        ->where('KD_PERUSAHAAN', '=', $kd_unit)
        ->where('KD_VOUCHER', '=', $kode)
		->where('FLAG_AKTIF', '=', 'Y')
		->get();

    	return $q;
	}

    public static function get_staff($user_id)
    {
    	$q = DB::connection('hrms')
    	->table('MST_KARYAWAN AS A')
    	->select(
            'A.NO_INDUK', 
            'A.NAMA', 
            'C.KD_GRUP_BAGIAN AS KD_DEPARTEMEN', 
            'C.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN'
        )
    	->join('TBL_BAGIAN AS B', 'B.KD_BAGIAN', '=', 'A.KD_BAGIAN')
        ->join('TBL_GRUP_BAGIAN AS C', 'B.KD_BAGIAN', '=', 'C.KD_GRUP_BAGIAN')
    	->where('A.NO_INDUK_APP_CUTI', '=', $user_id)
    	->get();

    	return $q;
    }

    public static function get_nm_unit($kd_unit)
    {
    	$q = DB::table('T_UNIT')
    	->select('NAMA AS NM_UNIT')
    	->where('UNIT_ID', '=', $kd_unit)
    	->get();

    	return $q;
    }

    public static function get_nm_departemen($kd_departemen, $keyword = '')
    {
    	$q = DB::connection('hrms')
    	->table('TBL_GRUP_BAGIAN')
    	->select('NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN')
    	->where('KD_GRUP_BAGIAN', '=', $kd_departemen)
        ->where(function($wh) use ($keyword){
            $wh->where('KD_GRUP_BAGIAN', 'LIKE', '%'.$keyword.'%');
            $wh->orWhere('NAMA_GRUP_BAGIAN', 'LIKE', '%'.$keyword.'%');
        })
    	->get();

    	return $q;
    }

    public static function search_data_voucher($kd_unit)
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
        ->where('KD_PERUSAHAAN', '=', $kd_unit)
        ->where('FLAG_AKTIF', '=', 'Y')
        ->orderBy('A.ROWID', 'ASC')
        ->get();

        return $q;
    }

    public static function search_tenant($kd_unit,$kd_lokasi)
    {
        $q = DB::select("
            SELECT 
                A.NO_PJS,
                NAMA_TOKO = A.MERK_DAGANG, 
                C.NAMA,
                NOMOR = A.TENANT_NO,	
                A.NASABAH_ID,
                A.FLAG_AKTIF
            FROM PM_PERJANJIAN_SEWA A
            LEFT JOIN PM_NASABAH C ON 
                A.NASABAH_ID			= C.NASABAH_ID
            LEFT JOIN PM_NASABAH D ON 
                A.NASABAH_PEMILIK_ID	= D.NASABAH_ID
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
                AND A.FLAG_TENANT		= 'Y' 
                AND A.FLAG_AKTIF	    = 'A'
        ");

        return $q;
    }

}