<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_PenjualanVoucher extends Model
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

    public static function search_dt($keyword,$kd_unit,$kd_lokasi,$user)
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
                AND A.USER_ENTRY        = '".$user."'
                AND E.FLAG_AKTIF        = 'A'
            ORDER BY 
                A.ROWID 
            ASC
        ");

        return $q;
    }

    public static function insert_dt($kd_unit, $kd_lokasi, $no_penjualan, $tgl_penjualan, $no_pjs, $id_tenant, $pembeli, $kd_carabayar, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $discount, $jml_bayar, $user, $tgl)
	{

        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penjualan, $tgl_penjualan, $no_pjs, $id_tenant, $pembeli, $kd_carabayar, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $discount, $jml_bayar, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;
            $discount       = preg_replace("/([^0-9\\.])/i", "", $discount);
            $discount       = (is_numeric($discount)) ? $discount : 0;
            $jml_bayar      = preg_replace("/([^0-9\\.])/i", "", $jml_bayar);
            $jml_bayar      = (is_numeric($jml_bayar)) ? $jml_bayar : 0;

            $kd_fungsi 		= 'PVJ';
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

			$no_penjualan = '';
			foreach ($q as $row) {
				$no_penjualan = $row->KD_FUNGSI.$row->TAHUN.$row->BULAN.$row->SEQUENCE;
			}			
		
			$q 	= DB::table('VCH_TRN_PENJUALAN')
			->insert([				
                'KD_PERUSAHAAN'         => $kd_unit, 
                'KD_LOKASI'             => $kd_lokasi, 
				'NO_BUKTI'              => $no_penjualan,  
				'TGL_BUKTI'             => $tgl_penjualan, 
				'USER_KASIR'            => $user, 
				'KD_BAGIAN'             => $kd_departemen,  
				'CARA_BAYAR'            => $kd_carabayar, 
                'NO_PJS'                => $no_pjs, 
                'NASABAH_ID'            => $id_tenant, 
				'USER_BELI'             => $pembeli, 
                'TIPE_KELUAR'           => 'E', 
				'KETERANGAN'            => $keterangan,  
                'TOTAL_JML_VOUCHER'	    => $jumlahheader,
                'TOTAL_VOUCHER'	        => $tot_voucher,
				'DISCOUNT'              => $discount,   
				'JML_BAYAR'             => $jml_bayar,  
                'STATUS'                => 'N', 
				'FLAG_AKTIF'            => 'Y', 
				'USER_ENTRY'			=> $user, 
				'TGL_ENTRY'				=> $tgl
			]);

            return $no_penjualan;
        });
	}

    public static function update_dt($kd_unit, $kd_lokasi, $no_penjualan, $tgl_penjualan, $no_pjs, $id_tenant, $pembeli, $kd_carabayar, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $discount, $jml_bayar, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penjualan, $tgl_penjualan, $no_pjs, $id_tenant, $pembeli, $kd_carabayar, $kd_departemen, $keterangan, $jumlahheader, $tot_voucher, $discount, $jml_bayar, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;
            $discount       = preg_replace("/([^0-9\\.])/i", "", $discount);
            $discount       = (is_numeric($discount)) ? $discount : 0;
            $jml_bayar      = preg_replace("/([^0-9\\.])/i", "", $jml_bayar);
            $jml_bayar      = (is_numeric($jml_bayar)) ? $jml_bayar : 0;
            
            $q = DB::table('VCH_TRN_PENJUALAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->update([
                'CARA_BAYAR'            => $kd_carabayar,  
                'NO_PJS'                => $no_pjs, 
                'NASABAH_ID'            => $id_tenant, 
				'USER_BELI'             => $pembeli, 
				'KETERANGAN'            => $keterangan,
                'TOTAL_JML_VOUCHER'	    => $jumlahheader,
                'TOTAL_VOUCHER'	        => $tot_voucher,
				'DISCOUNT'              => $discount,   
				'JML_BAYAR'             => $jml_bayar,  
                'USER_UPDATE'	        => $user,
                'TGL_UPDATE'	        => $tgl
            ]);

            return $no_penjualan;
        });	
	}

    public static function cekExist($kd_unit, $kd_lokasi, $no_penjualan, $add_kd_voucher, $add_no_baris)
    {
        $q = DB::table('VCH_TRN_PENJUALAN_DTL')
            ->select(
                'NO_BARIS'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->exists();

        return $q;
    }

	public static function insert_dtl($kd_unit, $kd_lokasi, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENJUALAN_DTL')
            ->insert([
                'KD_PERUSAHAAN'     => $kd_unit,
                'KD_LOKASI'         => $kd_lokasi,
                'NO_BUKTI'          => $no_penjualan,
                'KD_VOUCHER'        => $add_kd_voucher,
                'NO_BARIS'          => $add_no_baris,
                'JML_BELI'          => $add_qty,
                'NOMINAL_BELI'      => $add_nominal_voucher,
                'FG_ADD'            => 'Y', 
                'TIPE_KELUAR'       => 'E',
                'FLAG_AKTIF'        => 'Y', 
                'USER_ENTRY'        => $user, 
                'TGL_ENTRY'         => $tgl
            ]);

            return $q;
        });        
	}

	public static function update_dtl($kd_unit, $kd_lokasi, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penjualan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENJUALAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->update([
                'JML_BELI'          => $add_qty,
                'NOMINAL_BELI'      => $add_nominal_voucher,
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            return $q;
        });        
	}

    public static function show_detail($kd_unit,$kd_lokasi,$no_penjualan)
    {
        $q = DB::table('VCH_TRN_PENJUALAN_DTL AS A')
            ->select(
                'A.KD_PERUSAHAAN',
                'A.KD_LOKASI',
                'A.NO_BUKTI',
                'A.KD_VOUCHER',
                'A.NO_BARIS',
                'A.JML_BELI',
                'A.NOMINAL_BELI',
                'A.TIPE_KELUAR',
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
            ->where('A.NO_BUKTI', '=', $no_penjualan)
            ->where('A.FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function delete_detail($kd_unit,$kd_lokasi,$no_penjualan,$rowiddtl,$user,$tgl)
    {
        $q = DB::table('VCH_TRN_PENJUALAN_DTL')
		    ->where('ROWID', '=', $rowiddtl)
		    ->delete();		

            $q = DB::update("
                UPDATE A SET
                    A.TOTAL_JML_VOUCHER = (
                        SELECT 
                            SUM(ISNULL(XB.JML_BELI,0))
                        FROM VCH_TRN_PENJUALAN_DTL XB
                        WHERE 1=1
                            AND XB.KD_PERUSAHAAN    = A.KD_PERUSAHAAN
                            AND XB.KD_LOKASI        = A.KD_LOKASI
                            AND XB.NO_BUKTI         = A.NO_BUKTI 
                    ),
                    A.TOTAL_PEMBAGIAN = (
                        SELECT 
                            TOTAL = SUM(ISNULL(TOT_JML * TOT_VOUCHER,0))
                        FROM 
                        (
                            SELECT 
                                XC.JML_BELI AS TOT_JML,
                                XC.NOMINAL_BELI AS TOT_VOUCHER
                            FROM VCH_TRN_PENJUALAN_DTL XC
                            WHERE 1=1
                                AND XC.KD_PERUSAHAAN    = A.KD_PERUSAHAAN
                                AND XC.KD_LOKASI	    = A.KD_LOKASI
                                AND XC.NO_BUKTI         = A.NO_BUKTI
                        ) V_DTL
                    ),
                    A.USER_UPDATE   = '".$user."',
                    A.TGL_UPDATE    = '".$tgl."'
                FROM VCH_TRN_PENJUALAN A
                WHERE 1=1
                    AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                    AND A.KD_LOKASI         = '".$kd_lokasi."'
                    AND A.NO_BUKTI          = '".$no_penjualan."'
        ");

		return $q;
    }

    public static function delete_dt($no_penjualan, $user, $tgl)
    {
        $q = DB::table('VCH_TRN_PENJUALAN')
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->update(
                [
                    'STATUS'		    => 'C',
                    'FLAG_AKTIF'        => 'N',
                    'USER_BUKTI_BATAL'  => $user,
                    'TGL_BUKTI_BATAL'   => $tgl,
                    'USER_UPDATE'       => $user,
                    'TGL_UPDATE'        => now()
                ]
            );
    }

    public static function closing($kd_unit, $kd_lokasi, $no_penjualan, $user, $tgl) {
		DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penjualan, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENJUALAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_BUKTI', '=', $no_penjualan)
            ->update([
                'STATUS'		=> 'Y',
                'USER_APPROVE'	=> $user,
                'TGL_APPROVE'	=> $tgl,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            $q = DB::select("
                SELECT 
                    NO_BUKTI, 
                    KD_VOUCHER,
                    TGL_BUKTI, 
                    YYYYMM,
                    KD_BAGIAN, 
                    SUM(ISNULL(JML_BELI,0)) JML_BELI,
                    SUM(ISNULL(TOT_NOMINAL,0)) NOMINAL,
                    TOTAL_JML_VOUCHER, 
                    TOTAL_VOUCHER
                FROM
                (
                    SELECT
                        A.NO_BUKTI, 
                        B.KD_VOUCHER,
                        A.TGL_BUKTI,
                        CONVERT(CHAR(6),A.TGL_BUKTI,112) YYYYMM,
                        A.KD_BAGIAN, 
                        B.JML_BELI,
                        TOT_NOMINAL = B.JML_BELI * B.NOMINAL_BELI,
                        A.TOTAL_JML_VOUCHER, 
                        A.TOTAL_VOUCHER
                    FROM VCH_TRN_PENJUALAN A
                    INNER JOIN VCH_TRN_PENJUALAN_DTL B ON
                        A.KD_PERUSAHAAN			= B.KD_PERUSAHAAN
                        AND A.KD_LOKASI			= B.KD_LOKASI
                        AND A.NO_BUKTI			= B.NO_BUKTI
                    WHERE 1=1
                        AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                        AND A.KD_LOKASI         = '".$kd_lokasi."'
                        AND A.NO_BUKTI			= '".$no_penjualan."'
                ) V_DTL_TERIMA
                GROUP BY
                    NO_BUKTI, 
                    KD_VOUCHER,
                    TGL_BUKTI, 
                    YYYYMM,
                    KD_BAGIAN, 
                    TOTAL_JML_VOUCHER, 
                    TOTAL_VOUCHER
            ");

            foreach ($q as $row) {
                $kd_voucher     = $row->KD_VOUCHER;     
                $tgl_jual       = $row->TGL_BUKTI;       
                $kd_bagian      = $row->KD_BAGIAN;
                $yyyymm         = $row->YYYYMM;       
                $jumlah         = $row->JML_BELI;
                $nominal        = $row->NOMINAL;
                $totjml         = $row->TOTAL_JML_VOUCHER;
                $totnominal     = $row->TOTAL_VOUCHER;

                $q_exist = DB::select("
                    SELECT 
                        CNT_RKP		= ROWID,
                        XN_OLDJML	= ISNULL(MO_JML,0),
                        XN_OLDNOM	= ISNULL(MO_NOM,0)
                    FROM VCH_TRX_REKAP
                    WHERE 1=1
                        AND KD_VOUCHER		= '".$kd_voucher."'
                        AND KD_BAGIAN		= '".$kd_bagian."'
                        AND	YYYYMM			= '".$yyyymm."'
                        AND FG_TIPE         = 'INFA'
                ");

                $rowrkp     = 0;     
                $jmloldkp   = 0;    
                $nomoldrkp  = 0;        
                foreach ($q_exist as $row_exist) {
                    $rowrkp     = $row_exist->CNT_RKP;     
                    $jmloldkp   = $row_exist->XN_OLDJML;    
                    $nomoldrkp  = $row_exist->XN_OLDNOM;                  
                }

                $q_rkp = DB::select("
                    SELECT	TOP 1
                        XN_BGJML	= ISNULL(JML_STOK,0),
                        XN_HGJML	= ISNULL(HRG_STOK,0)
                    FROM V_VCH_TRX_INV_RKP_STOK
                    WHERE 1=1
                        AND KD_VOUCHER		= '".$kd_voucher."'
                        AND KD_BAGIAN		= '".$kd_bagian."'
                        AND	YYYYMM			= '".$yyyymm."'
                        AND FG_TIPE         = 'INFA'
                    ORDER BY 
                        YYYYMM 
                    DESC
                ");

                $xnbgjml     = 0;     
                $xnhgjml     = 0;     
                foreach ($q_rkp as $row_rkp) {
                    $xnbgjml     = $row_rkp->XN_BGJML;     
                    $xnhgjml     = $row_rkp->XN_HGJML;     
                }

                if ($q_exist) {
                    DB::table('VCH_TRX_REKAP')
                        ->where('KD_PERUSAHAAN', '=', $kd_unit)
                        ->where('KD_LOKASI', '=', $kd_lokasi)
                        ->where('ROWID', '=', $rowrkp)
                        ->update([
                            'MO_JML'		=> $jumlah + $jmloldkp, 
                            'MO_NOM'		=> $nominal + $nomoldrkp,  
                            'USER_UPDATE'	=> $user,
                            'TGL_UPDATE'	=> $tgl
                        ]);
                }else{
                    DB::table('VCH_TRX_REKAP')
                        ->insert([
                            'KD_PERUSAHAAN' => $kd_unit,         
                            'KD_LOKASI'     => $kd_lokasi,  
                            'KD_VOUCHER'    => $kd_voucher,  
                            'KD_BAGIAN'     => $kd_bagian,  
                            'YYYYMM'        => $yyyymm,  
                            'BG_JML'        => $xnbgjml,  
                            'BG_NOM'        => $xnhgjml,  
                            'IN_JML'        => 0,
                            'IN_NOM'        => 0,
                            'AI_JML'        => 0,  
                            'AI_NOM'        => 0,
                            'OT_JML'        => 0, 
                            'OT_NOM'        => 0,
                            'MO_JML'        => $jumlah,  
                            'MO_NOM'        => $nominal, 
                            'AO_JML'        => 0,
                            'AO_NOM'        => 0,
                            'GC_JML'        => 0,
                            'GC_NOM'        => 0,
                            'MC_JML'        => 0,
                            'MC_NOM'        => 0, 
                            'BG_TOT'        => 0,
                            'FG_TIPE'       => 'OTFA',  
                            'USER_ENTRY'    => $user, 
                            'TGL_ENTRY'     => $tgl,
                            'USER_UPDATE'   => $user, 
                            'TGL_UPDATE'    => $tgl
                        ]);
                }
                
                $q = DB::table('VCH_TRX_INOUT')
                ->insert([
                    'KD_PERUSAHAAN'         => $kd_unit, 
                    'KD_LOKASI'             => $kd_lokasi, 
                    'KD_VOUCHER'            => $kd_voucher, 
                    'KD_BAGIAN'             => $kd_bagian,
                    'NO_REF'                => $no_penjualan,
                    'TGL_TRX'               => $tgl_jual,
                    'FLAG_IO'               => 'MO',
                    'KEY1'                  => 'E',
                    'JML'                   => $jumlah, 
                    'NOMINAL'               => $nominal, 
                    'TOTAL_JML'             => $totjml, 
                    'TOTAL_NOMINAL'         => $totnominal, 
                    'FG_TIPE'               => 'MOFA', 
                    'USER_ENTRY'            => $user, 
                    'TGL_ENTRY'             => $tgl,
                    'USER_UPDATE'           => $user, 
                    'TGL_UPDATE'            => $tgl
                ]);
            }	
        });	
	}
}
