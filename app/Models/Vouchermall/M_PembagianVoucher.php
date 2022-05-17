<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_PembagianVoucher extends Model
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
            SELECT 
                A.KD_PERUSAHAAN, 
                A.KD_LOKASI, 
                A.NO_PEMBAGIAN, 
                CONVERT(VARCHAR(10),A.TGL_PEMBAGIAN,103) TGL_PEMBAGIAN,
                A.DIBERIKAN_OLEH, 
                B.NAMA + '  -  ' + A.DIBERIKAN_OLEH AS NAMA_USER,
                A.KD_BAGIAN, 
                C.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN,
                A.USER_PENERIMA, 
                A.KETERANGAN, 
                ISNULL(A.TOTAL_JML_PEMBAGIAN,0) TOTAL_JML_PEMBAGIAN, 
                ISNULL(A.TOTAL_PEMBAGIAN,0) TOTAL_PEMBAGIAN,
                A.STATUS, 
                D.GH_FUNCTION_DESC AS TITLE_STATUS,
                A.FLAG_AKTIF, 
                A.ROWID
            FROM VCH_TRN_PEMBAGIAN A
            INNER JOIN T_USER B ON
                A.DIBERIKAN_OLEH				= B.KODE_USER
            INNER JOIN [DBGJI].[HRMS].[dbo].[TBL_GRUP_BAGIAN] C ON
                A.KD_BAGIAN						= C.KD_GRUP_BAGIAN
            INNER JOIN GS_GEN_HARDCODED D ON
                A.STATUS						= D.GH_FUNCTION_CODE
                AND D.GH_SYS					= 'H'
                AND D.GH_FUNCTION_NAME			= 'VCH_STATUS'
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

    public static function get_data_onhand($kd_unit,$kd_lokasi,$kd_departemen,$tgl_pembagian,$voucher_kode)
    {
        $q = DB::select("
            SELECT 
                A.KD_PERUSAHAAN,
                A.KD_VOUCHER,
                A.NM_VOUCHER,
                V_TRX_INV_RKP.KD_BAGIAN,
                ONHAND_STOCK = ISNULL(V_TRX_INV_RKP.ONHAND_STOCK,0)
            FROM VCH_MST_VOUCHER A
            LEFT OUTER JOIN (  
                SELECT 
                    XA.KD_PERUSAHAAN,
                    XA.KD_VOUCHER,
                    XA.KD_BAGIAN,
                    XA.YYYYMM,
                    XA.FG_TIPE,
                    ONHAND_STOCK = ( ISNULL(XA.BG_JML,0) + ISNULL(XA.IN_JML,0) + ISNULL(XA.AI_JML,0) - ISNULL(XA.OT_JML,0) - ISNULL(XA.MO_JML,0) - ISNULL(XA.AO_JML,0) -  ISNULL(XA.GC_JML,0) + ISNULL(XA.MC_JML,0)),
                    JMLNOM_STOCK = ( ISNULL(XA.BG_NOM,0) + ISNULL(XA.IN_NOM,0) + ISNULL(XA.AI_NOM,0) - ISNULL(XA.OT_NOM,0) - ISNULL(XA.MO_NOM,0) - ISNULL(XA.AO_NOM,0) - ISNULL(XA.GC_NOM,0) + ISNULL(XA.MC_NOM,0))
                FROM VCH_TRX_REKAP  XA
            ) V_TRX_INV_RKP ON 
                V_TRX_INV_RKP.KD_PERUSAHAAN		= A.KD_PERUSAHAAN
                AND V_TRX_INV_RKP.KD_VOUCHER	= A.KD_VOUCHER
                AND V_TRX_INV_RKP.YYYYMM 		= '".$tgl_pembagian."'
                AND V_TRX_INV_RKP.FG_TIPE		= 'INDP'
            WHERE 1=1
                AND A.KD_PERUSAHAAN			= '".$kd_unit."'
                AND V_TRX_INV_RKP.KD_BAGIAN	= '".$kd_departemen."'
                AND A.KD_VOUCHER			= '".$voucher_kode."'
                AND A.FLAG_AKTIF			= 'Y' 
        ");

        return $q;
    }

    public static function insert_dt($kd_unit, $kd_lokasi, $no_dokumen, $tgl_pembagian, $kd_departemen, $penerima, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl)
	{

        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $tgl_pembagian, $kd_departemen, $penerima, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;

            $kd_fungsi 		= 'PVB';
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

			$q 	= DB::table('VCH_TRN_PEMBAGIAN')
			->insert([				
                'KD_PERUSAHAAN'         => $kd_unit, 
                'KD_LOKASI'             => $kd_lokasi, 
				'NO_PEMBAGIAN'          => $no_dokumen,  
                'TGL_PEMBAGIAN'         => $tgl_pembagian,   
                'DIBERIKAN_OLEH'        => $user,  
                'KD_BAGIAN'             => $kd_departemen,   
                'USER_PENERIMA'         => $penerima,   
                'KETERANGAN'            => $keterangan,   
                'TOTAL_JML_PEMBAGIAN'   => $jumlahheader,
                'TOTAL_PEMBAGIAN'       => $tot_voucher,
                'STATUS'                => 'N', 
                'FLAG_AKTIF'            => 'Y', 
				'USER_ENTRY'			=> $user, 
				'TGL_ENTRY'				=> $tgl
			]);

            return $no_dokumen;
        });
	}

    public static function update_dt($kd_unit, $kd_lokasi, $no_dokumen, $tgl_pembagian, $kd_departemen, $penerima, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $tgl_pembagian, $kd_departemen, $penerima, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl) {            
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;

            $q = DB::table('VCH_TRN_PEMBAGIAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PEMBAGIAN', '=', $no_dokumen)
            ->update([
                'USER_PENERIMA'         => $penerima,   
                'KETERANGAN'            => $keterangan,  
                'TOTAL_JML_PEMBAGIAN'   => $jumlahheader,
                'TOTAL_PEMBAGIAN'       => $tot_voucher,
                'USER_UPDATE'	        => $user,
                'TGL_UPDATE'	        => $tgl
            ]);

            return $no_dokumen;
        });	
	}

    public static function cekExist($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher, $add_no_baris)
    {
        $q = DB::table('VCH_TRN_PEMBAGIAN_DTL')
            ->select(
                'NO_BARIS'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PEMBAGIAN', '=', $no_dokumen)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->exists();

        return $q;
    }

	public static function insert_dtl($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PEMBAGIAN_DTL')
            ->insert([
                'KD_PERUSAHAAN'     => $kd_unit,
                'KD_LOKASI'         => $kd_lokasi,
                'NO_PEMBAGIAN'      => $no_dokumen,
                'KD_VOUCHER'        => $add_kd_voucher,
                'NO_BARIS'          => $add_no_baris,
                'JML_PEMBAGIAN'     => $add_qty,
                'NOMINAL_PEMBAGIAN' => $add_nominal_voucher,
                'FG_ADD'            => 'Y', 
                'FLAG_AKTIF'        => 'Y', 
                'USER_ENTRY'        => $user, 
                'TGL_ENTRY'         => $tgl
            ]);

            return $q;
        });        
	}

	public static function update_dtl($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PEMBAGIAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PEMBAGIAN', '=', $no_dokumen)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->update([
                'JML_TERIMA'        => $add_qty, 
                'NOMINAL'           => $add_nominal_voucher, 
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            return $q;
        });        
	}

    public static function show_detail($kd_unit,$kd_lokasi,$no_dokumen)
    {
        $q = DB::table('VCH_TRN_PEMBAGIAN_DTL AS A')
            ->select(
                'A.KD_PERUSAHAAN',
                'A.KD_LOKASI',
                'A.NO_PEMBAGIAN', 
                'A.KD_VOUCHER', 
                'A.NO_BARIS', 
                'A.JML_PEMBAGIAN', 
                'A.NOMINAL_PEMBAGIAN', 
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
            ->where('A.NO_PEMBAGIAN', '=', $no_dokumen)
            ->where('A.FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function delete_detail($kd_unit,$kd_lokasi,$no_dokumen,$rowiddtl,$user,$tgl)
    {
        $q = DB::table('VCH_TRN_PEMBAGIAN_DTL')
		    ->where('ROWID', '=', $rowiddtl)
		    ->delete();		

        $q = DB::update("
            UPDATE A SET
                A.TOTAL_JML_PEMBAGIAN = (
                    SELECT 
                        SUM(ISNULL(XB.JML_PEMBAGIAN,0))
                    FROM VCH_TRN_PEMBAGIAN_DTL XB
                    WHERE 1=1
                        AND XB.KD_PERUSAHAAN   = A.KD_PERUSAHAAN
                        AND XB.KD_LOKASI       = A.KD_LOKASI
                        AND XB.NO_PEMBAGIAN    = A.NO_PEMBAGIAN 
                ),
                A.TOTAL_PEMBAGIAN = (
                    SELECT 
                        TOTAL = SUM(ISNULL(TOT_JML * TOT_VOUCHER,0))
                    FROM 
                    (
                        SELECT 
                            XC.JML_PEMBAGIAN AS TOT_JML,
                            XC.NOMINAL_PEMBAGIAN AS TOT_VOUCHER
                        FROM VCH_TRN_PEMBAGIAN_DTL XC
                        WHERE 1=1
                            AND XC.KD_PERUSAHAAN        = A.KD_PERUSAHAAN
                            AND XC.KD_LOKASI	        = A.KD_LOKASI
                            AND XC.NO_PEMBAGIAN         = A.NO_PEMBAGIAN
                    ) V_DTL
                ),
                A.USER_UPDATE   = '".$user."',
                A.TGL_UPDATE    = '".$tgl."'
            FROM VCH_TRN_PEMBAGIAN A
            WHERE 1=1
                AND A.KD_PERUSAHAAN = '".$kd_unit."'
                AND A.KD_LOKASI     = '".$kd_lokasi."'
                AND A.NO_PEMBAGIAN  = '".$no_dokumen."'
        ");

		return $q;
    }

    public static function delete_dt($no_dokumen, $user, $tgl)
    {
        $q = DB::table('VCH_TRN_PEMBAGIAN')
            ->where('NO_PEMBAGIAN', '=', $no_dokumen)
            ->update(
                [
                    'STATUS'		        => 'C',
                    'FLAG_AKTIF'            => 'N',
                    'USER_BATAL_PEMBAGIAN'  => $user,
                    'TGL_BATAL_PEMBAGIAN'   => $tgl,
                    'USER_UPDATE'           => $user,
                    'TGL_UPDATE'            => now()
                ]
            );
    }

    public static function closing($kd_unit, $kd_lokasi, $no_dokumen, $user, $tgl) {
		DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_dokumen, $user, $tgl) {
            $q = DB::table('VCH_TRN_PEMBAGIAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PEMBAGIAN', '=', $no_dokumen)
            ->update([
                'STATUS'		=> 'Y',
                'USER_APPROVE'	=> $user,
                'TGL_APPROVE'	=> $tgl,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            $q = DB::select("
                SELECT 
                    NO_PEMBAGIAN, 
                    KD_VOUCHER,
                    TGL_PEMBAGIAN, 
                    YYYYMM,
                    KD_BAGIAN, 
                    SUM(ISNULL(JML_PEMBAGIAN,0)) JML_PEMBAGIAN,
                    SUM(ISNULL(TOT_NOMINAL,0)) NOMINAL,
                    TOTAL_JML_PEMBAGIAN, 
                    TOTAL_PEMBAGIAN
                FROM
                (
                    SELECT
                        A.NO_PEMBAGIAN, 
                        B.KD_VOUCHER,
                        A.TGL_PEMBAGIAN,
                        CONVERT(CHAR(6),A.TGL_PEMBAGIAN,112) YYYYMM,
                        A.KD_BAGIAN, 
                        B.JML_PEMBAGIAN,
                        TOT_NOMINAL = B.JML_PEMBAGIAN * B.NOMINAL_PEMBAGIAN,
                        A.TOTAL_JML_PEMBAGIAN, 
                        A.TOTAL_PEMBAGIAN
                    FROM VCH_TRN_PEMBAGIAN A
                    INNER JOIN VCH_TRN_PEMBAGIAN_DTL B ON
                        A.KD_PERUSAHAAN			= B.KD_PERUSAHAAN
                        AND A.KD_LOKASI			= B.KD_LOKASI
                        AND A.NO_PEMBAGIAN		= B.NO_PEMBAGIAN
                    WHERE 1=1
                        AND A.KD_PERUSAHAAN = '".$kd_unit."'
                        AND A.KD_LOKASI     = '".$kd_lokasi."'
                        AND A.NO_PEMBAGIAN	= '".$no_dokumen."'
                ) V_DTL_TERIMA
                GROUP BY
                    NO_PEMBAGIAN, 
                    KD_VOUCHER,
                    TGL_PEMBAGIAN, 
                    YYYYMM,
                    KD_BAGIAN, 
                    TOTAL_JML_PEMBAGIAN, 
                    TOTAL_PEMBAGIAN
            ");

            foreach ($q as $row) {
                $kd_voucher     = $row->KD_VOUCHER;     
                $tgl_bagi       = $row->TGL_PEMBAGIAN;       
                $kd_bagian      = $row->KD_BAGIAN;
                $yyyymm         = $row->YYYYMM;       
                $jumlah         = $row->JML_PEMBAGIAN;
                $nominal        = $row->NOMINAL;
                $totjml         = $row->TOTAL_JML_PEMBAGIAN;
                $totnominal     = $row->TOTAL_PEMBAGIAN;

                $q_exist = DB::select("
                    SELECT 
                        CNT_RKP		= ROWID,
                        XN_OLDJML	= ISNULL(OT_JML,0),
                        XN_OLDNOM	= ISNULL(OT_NOM,0)
                    FROM VCH_TRX_REKAP
                    WHERE 1=1
                        AND KD_VOUCHER		= '".$kd_voucher."'
                        AND KD_BAGIAN		= '".$kd_bagian."'
                        AND	YYYYMM			= '".$yyyymm."'
                        AND FG_TIPE         = 'INDP'
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
                        AND FG_TIPE         = 'INDP'
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
                            'OT_JML'		=> $jumlah + $jmloldkp, 
                            'OT_NOM'		=> $nominal + $nomoldrkp,  
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
                            'OT_JML'        => $jumlah,  
                            'OT_NOM'        => $nominal, 
                            'MO_JML'        => 0,
                            'MO_NOM'        => 0,  
                            'AO_JML'        => 0,
                            'AO_NOM'        => 0,
                            'GC_JML'        => 0,
                            'GC_NOM'        => 0,
                            'MC_JML'        => 0,
                            'MC_NOM'        => 0, 
                            'BG_TOT'        => 0,
                            'FG_TIPE'       => 'OTDP',  
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
                    'NO_REF'                => $no_dokumen,
                    'TGL_TRX'               => $tgl_bagi,
                    'FLAG_IO'               => 'OT',
                    'KEY1'                  => 'I',
                    'JML'                   => $jumlah, 
                    'NOMINAL'               => $nominal, 
                    'TOTAL_JML'             => $totjml, 
                    'TOTAL_NOMINAL'         => $totnominal, 
                    'FG_TIPE'               => 'OTDP', 
                    'USER_ENTRY'            => $user, 
                    'TGL_ENTRY'             => $tgl,
                    'USER_UPDATE'           => $user, 
                    'TGL_UPDATE'            => $tgl
                ]);
            }	
        });	
	}
}
