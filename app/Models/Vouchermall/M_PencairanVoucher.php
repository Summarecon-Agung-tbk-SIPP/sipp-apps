<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_PencairanVoucher extends Model
{
    public static function get_voucher($kd_unit,$kd_lokasi,$kode)
    {
        $q = DB::table('VCH_MST_VOUCHER')
            ->select(
                'NO_BARCODE',
                'NOMINAL'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_VOUCHER', '=', $kode)
            ->where('FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function search_penjualan($kd_unit,$kd_lokasi,$user)
    {
        $q = DB::select("
            SELECT DISTINCT
                A.KD_PERUSAHAAN, 
                A.KD_LOKASI, 
                A.NO_BUKTI, 
                CONVERT(VARCHAR(10),A.TGL_BUKTI,103) TGL_BUKTI,
                A.USER_KASIR, 
                B.NAMA + '  -  ' + A.USER_KASIR AS NAMA_USER,
                A.KD_BAGIAN, 
                C.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN,
                A.CARA_BAYAR, 
                A.NO_PJS,
                A.NASABAH_ID,
                E.MERK_DAGANG,
                A.USER_BELI, 
                A.KETERANGAN, 
                ISNULL(A.TOTAL_JML_VOUCHER,0) TOTAL_JML_VOUCHER, 
                ISNULL(A.TOTAL_VOUCHER,0) TOTAL_VOUCHER, 
                ISNULL(A.DISCOUNT,0) DISCOUNT, 
                ISNULL(A.JML_BAYAR,0) JML_BAYAR,  
                A.TIPE_KELUAR, 
                A.STATUS, 
                D.GH_FUNCTION_DESC AS TITLE_STATUS,
                A.FLAG_AKTIF, 
                A.ROWID
            FROM VCH_TRN_PENJUALAN A
            INNER JOIN T_USER B ON
                A.USER_KASIR			= B.KODE_USER
            INNER JOIN [DBGJI].[HRMS].[dbo].[TBL_GRUP_BAGIAN] C ON
                A.KD_BAGIAN				= C.KD_GRUP_BAGIAN
            INNER JOIN GS_GEN_HARDCODED D ON
                A.STATUS				= D.GH_FUNCTION_CODE
                AND D.GH_SYS			= 'H'
                AND D.GH_FUNCTION_NAME	= 'VCH_STATUS'
            INNER JOIN PM_PERJANJIAN_SEWA E ON
                A.KD_PERUSAHAAN			= E.KD_PERUSAHAAN
                AND A.KD_LOKASI         = E.KD_LOKASI
                AND A.NO_PJS            = E.NO_PJS
                AND A.NASABAH_ID        = E.NASABAH_ID 
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
                AND A.STATUS            = 'Y'
                AND E.FLAG_AKTIF        = 'A'
            ORDER BY 
                A.ROWID 
            ASC
        ");

        return $q;
    }

    public static function search_dt($keyword,$kd_unit,$kd_lokasi,$user)
    {
        $q = DB::select("
            SELECT 
                A.KD_PERUSAHAAN, 
                A.KD_LOKASI, 
                A.NO_PENCAIRAN, 
                CONVERT(VARCHAR(10),A.TGL_PENCAIRAN,103) TGL_PENCAIRAN, 
                A.DICAIRKAN_OLEH, 
                C.NAMA + '  -  ' + A.DICAIRKAN_OLEH AS NAMA_USER,
                A.KD_BAGIAN,
                D.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN,
                A.NO_BUKTI, 
                B.USER_BELI,
                B.NO_PJS,
                B.NASABAH_ID,
                CONVERT(VARCHAR(10),B.TGL_BUKTI,103) TGL_BUKTI,
                A.KETERANGAN, 
                ISNULL(A.TOTAL_JML_PENCAIRAN,0) TOTAL_JML_PENCAIRAN, 
                ISNULL(A.TOTAL_PENCAIRAN,0) TOTAL_PENCAIRAN,
                A.STATUS, 
                E.GH_FUNCTION_DESC AS TITLE_STATUS,
                A.FLAG_AKTIF
            FROM  VCH_TRN_PENCAIRAN A
            INNER JOIN VCH_TRN_PENJUALAN B ON
                A.KD_PERUSAHAAN				    = B.KD_PERUSAHAAN
                AND A.KD_LOKASI				    = B.KD_LOKASI
                AND A.NO_BUKTI					= B.NO_BUKTI
            INNER JOIN T_USER C ON
                A.DICAIRKAN_OLEH				= C.KODE_USER
            INNER JOIN [DBGJI].[HRMS].[dbo].[TBL_GRUP_BAGIAN] D ON
                A.KD_BAGIAN						= D.KD_GRUP_BAGIAN
            INNER JOIN GS_GEN_HARDCODED E ON
                A.STATUS						= E.GH_FUNCTION_CODE
                AND E.GH_SYS					= 'H'
                AND E.GH_FUNCTION_NAME			= 'VCH_STATUS'
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
                AND A.USER_ENTRY        = '".$user."'
            ORDER BY 
                A.ROWID 
            ASC
        ");

        return $q;
    }

    public static function insert_dt($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $tgl_pencairan, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $tgl_pencairan, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;

            $kd_fungsi 		= 'PVC';
			$lenght 		= '4';
			$ls_lastnumber	= '';

			$q = DB::statement("EXEC SP_VCH_NUMBERING '".$kd_unit."', '".$kd_fungsi."', '".$lenght."', '".$tgl."', '".$ls_lastnumber."', '".$user."', '".$tgl."'");

			$q = DB::table('VCH_MST_NUMBERING_DTL AS A')
			->select(
                'A.KD_FUNGSI', 
                'A.FORMATION', 
                DB::raw('LEFT(A.FORMATION,4) AS TAHUN'), 
                DB::raw('RIGHT(A.FORMATION,2) AS BULAN'), 
                'A.SEQUENCE'
            )
			->where('A.KD_PERUSAHAAN', '=', $kd_unit)
			->where('A.KD_FUNGSI', '=', $kd_fungsi)
			->get();

			$no_dokumen = '';
			foreach ($q as $row) {
				$no_dokumen = $row->KD_FUNGSI.$row->TAHUN.$row->BULAN.$row->SEQUENCE;
			}			

			$q 	= DB::table('VCH_TRN_PENCAIRAN')
			->insert([				
                'KD_PERUSAHAAN'         => $kd_unit, 
                'KD_LOKASI'             => $kd_lokasi, 
				'NO_PENCAIRAN'          => $no_dokumen,  
                'TGL_PENCAIRAN'         => $tgl_pencairan,   
                'DICAIRKAN_OLEH'        => $user,  
                'KD_BAGIAN'             => $kd_departemen,   
                'NO_BUKTI'              => $no_penjualan,   
                'KETERANGAN'            => $keterangan,   
                'TOTAL_JML_PENCAIRAN'   => $jumlahheader,
                'TOTAL_PENCAIRAN'       => $tot_voucher,
                'STATUS'                => 'N', 
                'FLAG_AKTIF'            => 'Y', 
				'USER_ENTRY'			=> $user, 
				'TGL_ENTRY'				=> $tgl
			]);

            return $no_dokumen;
        });
	}

    public static function update_dt($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $tgl_pencairan, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $tgl_pencairan, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl) {            
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;

            $q = DB::table('VCH_TRN_PENCAIRAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENCAIRAN', '=', $no_dokumen)
            ->update([
                'TOTAL_JML_PENCAIRAN'   => $jumlahheader,
                'TOTAL_PENCAIRAN'       => $tot_voucher,
                'NO_BUKTI'              => $no_penjualan,   
                'KETERANGAN'            => $keterangan,   
                'USER_UPDATE'	        => $user,
                'TGL_UPDATE'	        => $tgl
            ]);

            return $no_dokumen;
        });	
	}

    public static function cekExist($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $add_kd_voucher, $add_no_baris)
    {
        $q = DB::table('VCH_TRN_PENCAIRAN_DTL')
            ->select(
                'NO_BARIS'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENCAIRAN', '=', $no_dokumen)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->exists();

        return $q;
    }

	public static function insert_dtl($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENCAIRAN_DTL')
            ->insert([
                'KD_PERUSAHAAN'     => $kd_unit,
                'KD_LOKASI'         => $kd_lokasi,
                'NO_PENCAIRAN'      => $no_dokumen,
                'NO_BUKTI'          => $no_penjualan,
                'KD_VOUCHER'        => $add_kd_voucher,
                'NO_BARIS'          => $add_no_baris,
                'JML_PENCAIRAN'     => $add_qty,
                'NOMINAL_PENCAIRAN' => $add_nominal_voucher,
                'FG_ADD'            => 'Y', 
                'FLAG_AKTIF'        => 'Y', 
                'USER_ENTRY'        => $user, 
                'TGL_ENTRY'         => $tgl
            ]);

            return $q;
        });        
	}

	public static function update_dtl($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENCAIRAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENCAIRAN', '=', $no_dokumen)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->update([
                'JML_PENCAIRAN'     => $add_qty,
                'NOMINAL_PENCAIRAN' => $add_nominal_voucher,
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            return $q;
        });        
	}

	public static function update_jualdtl($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $tgl_pencairan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $no_penjualan, $tgl_pencairan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENJUALAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->update([
                'FG_CAIR'       => 'Y',
                'TGL_CAIR'      => $tgl_pencairan,
                'USER_CAIR'     => $user,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            return $q;
        });        
	}

    public static function show_detail($kd_unit,$kd_lokasi,$no_dokumen)
    {
        $q = DB::table('VCH_TRN_PENCAIRAN_DTL AS A')
            ->select(
                'A.KD_PERUSAHAAN',
                'A.KD_LOKASI',
                'A.NO_PENCAIRAN', 
                'A.KD_VOUCHER', 
                'A.NO_BARIS', 
                'A.JML_PENCAIRAN', 
                'A.NOMINAL_PENCAIRAN', 
                'A.FLAG_AKTIF',
                'A.ROWID',
                'A.FG_ADD',
                'B.NO_BARCODE'
            )
            ->join('VCH_MST_VOUCHER AS B', function($join){
                $join->on('A.KD_PERUSAHAAN', '=', 'B.KD_PERUSAHAAN');
                $join->on('A.KD_VOUCHER', '=', 'B.KD_VOUCHER');
            })
            ->where('A.KD_PERUSAHAAN', '=', $kd_unit)
            ->where('A.KD_LOKASI', '=', $kd_lokasi)
            ->where('A.NO_PENCAIRAN', '=', $no_dokumen)
            ->where('A.FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function delete_detail($kd_unit,$kd_lokasi,$no_dokumen,$rowiddtl,$user,$tgl)
    {
        $q = DB::table('VCH_TRN_PENCAIRAN_DTL')
		    ->where('ROWID', '=', $rowiddtl)
		    ->delete();		

            $q = DB::update("
            UPDATE A SET
                A.TOTAL_JML_PENCAIRAN = (
                    SELECT 
                        SUM(ISNULL(XB.JML_PENCAIRAN,0))
                    FROM VCH_TRN_PENCAIRAN_DTL XB
                    WHERE 1=1
                        AND XB.KD_PERUSAHAAN        = A.KD_PERUSAHAAN
                        AND XB.KD_LOKASI            = A.KD_LOKASI
                        AND XB.NO_PENCAIRAN         = A.NO_PENCAIRAN 
                ),
                A.TOTAL_PENCAIRAN = (
                    SELECT 
                        TOTAL = SUM(ISNULL(TOT_JML * TOT_VOUCHER,0))
                    FROM 
                    (
                        SELECT 
                            XC.JML_PENCAIRAN AS TOT_JML,
                            XC.NOMINAL_PENCAIRAN AS TOT_VOUCHER
                        FROM VCH_TRN_PENCAIRAN_DTL XC
                        WHERE 1=1
                            AND XC.KD_PERUSAHAAN        = A.KD_PERUSAHAAN
                            AND XC.KD_LOKASI	        = A.KD_LOKASI
                            AND XC.NO_PENCAIRAN         = A.NO_PENCAIRAN
                    ) V_DTL
                ),
                A.USER_UPDATE   = '".$user."',
                A.TGL_UPDATE    = '".$tgl."'
            FROM VCH_TRN_PENCAIRAN A
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
                AND A.NO_PENCAIRAN      = '".$no_dokumen."'
        ");

		return $q;
    }

    public static function delete_dt($no_dokumen,$user,$tgl)
    {
        $q = DB::table('VCH_TRN_PENCAIRAN')
            ->where('NO_PENCAIRAN', '=', $no_dokumen)
            ->update(
                [
                    'STATUS'		        => 'C',
                    'FLAG_AKTIF'            => 'N',
                    'USER_BATAL_PENCAIRAN'  => $user,
                    'TGL_BATAL_PENCAIRAN'   => $tgl,
                    'USER_UPDATE'           => $user,
                    'TGL_UPDATE'            => now()
                ]
            );
    }

    public static function closing($kd_unit, $kd_lokasi, $no_dokumen, $user, $tgl) {
		DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENCAIRAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENCAIRAN', '=', $no_dokumen)
            ->update([
                'STATUS'		=> 'Y',
                'USER_APPROVE'	=> $user,
                'TGL_APPROVE'	=> $tgl,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);
        });	
	}
}
