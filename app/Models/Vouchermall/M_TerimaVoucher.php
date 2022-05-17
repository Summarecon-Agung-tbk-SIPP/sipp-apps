<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_TerimaVoucher extends Model
{
    public static function search_dt($keyword,$kd_unit,$kd_lokasi,$user)
    {
        $q = DB::select("
            SELECT 
                A.KD_PERUSAHAAN,
                A.KD_LOKASI,
                A.NO_TERIMA_VOUCHER,
                CONVERT(VARCHAR(10),A.TGL_TERIMA,103) TGL_TERIMA,
                B.NAMA + '  -  ' + A.USER_TERIMA AS NAMA_USER,
                A.KD_BAGIAN,
                ISNULL(A.TOTAL_JML_TERIMA,0) TOTAL_JML_TERIMA, 
                ISNULL(A.TOTAL_TERIMA,0) TOTAL_TERIMA,
                A.STATUS,
                D.GH_FUNCTION_DESC AS TITLE_STATUS,
                A.NO_BATAL_TERIMA,
                A.FLAG_AKTIF,
                A.ROWID
            FROM VCH_TRN_PENERIMAAN A
            INNER JOIN T_USER B ON
                A.USER_TERIMA					= B.KODE_USER
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

    public static function insert_dt($kd_unit, $kd_lokasi, $no_penerimaan, $tgl_terima, $kd_departemen, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penerimaan, $tgl_terima, $kd_departemen, $user, $tgl) {
            $kd_fungsi 		= 'PVM';
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

			$no_penerimaan = '';
			foreach ($q as $row) {
				$no_penerimaan = $row->KD_FUNGSI.$row->TAHUN.$row->BULAN.$row->SEQUENCE;
			}			
			
			$q 	= DB::table('VCH_TRN_PENERIMAAN')
			->insert([
				'KD_PERUSAHAAN'			=> $kd_unit, 
				'KD_LOKASI'				=> $kd_lokasi, 
				'NO_TERIMA_VOUCHER'		=> $no_penerimaan, 
				'TGL_TERIMA'			=> $tgl_terima, 
				'USER_TERIMA'			=> $user, 
				'KD_BAGIAN'				=> $kd_departemen, 
				'STATUS'				=> 'N',
				'FLAG_AKTIF'			=> 'Y', 
				'USER_ENTRY'			=> $user, 
				'TGL_ENTRY'				=> $tgl
			]);

            return $no_penerimaan;
        });
	}

    public static function update_dt($kd_unit, $kd_lokasi, $no_penerimaan, $tgl_terima, $kd_departemen, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penerimaan, $tgl_terima, $kd_departemen, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENERIMAAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->update([
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            return $no_penerimaan;
        });	
	}

    public static function cekExist($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher, $add_no_baris)
    {
        $q = DB::table('VCH_TRN_PENERIMAAN_DTL')
            ->select(
                'NO_BARIS'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->exists();

        return $q;
    }

	public static function insert_dtl($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENERIMAAN_DTL')
            ->insert([
                'KD_PERUSAHAAN'         => $kd_unit,
                'KD_LOKASI'             => $kd_lokasi,
                'NO_TERIMA_VOUCHER'     => $no_penerimaan,
                'KD_VOUCHER'            => $add_kd_voucher, 
                'NO_BARIS'              => $add_no_baris, 
                'JML_TERIMA'            => $add_qty, 
                'NOMINAL'               => $add_nominal_voucher, 
                'FG_ADD'                => 'Y', 
                'FLAG_AKTIF'            => 'Y', 
                'USER_ENTRY'            => $user, 
                'TGL_ENTRY'             => $tgl
            ]);

            $q = DB::table('VCH_TRN_PENERIMAAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->update([
                'TOTAL_JML_TERIMA'	=> $jumlah,
                'TOTAL_TERIMA'	    => $total,
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            return $q;
        });        
	}

	public static function update_dtl($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENERIMAAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->update([
                'JML_TERIMA'        => $add_qty, 
                'NOMINAL'           => $add_nominal_voucher, 
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            $q = DB::table('VCH_TRN_PENERIMAAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->update([
                'TOTAL_JML_TERIMA'	=> $jumlah,
                'TOTAL_TERIMA'	    => $total,
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);
            return $q;
        });        
	}

    public static function show_detail($kd_unit,$kd_lokasi,$no_penerimaan)
    {
        $q = DB::table('VCH_TRN_PENERIMAAN_DTL AS A')
            ->select(
                'A.KD_PERUSAHAAN', 
                'A.KD_LOKASI', 
                'A.NO_TERIMA_VOUCHER', 
                'A.KD_VOUCHER', 
                'A.NO_BARIS',
                'A.JML_TERIMA', 
                'A.NOMINAL', 
                'A.CATATAN', 
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
            ->where('A.NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->where('A.FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function delete_detail($kd_unit,$kd_lokasi,$no_penerimaan,$rowiddtl,$user,$tgl)
    {
        $q = DB::table('VCH_TRN_PENERIMAAN_DTL')
		    ->where('ROWID', '=', $rowiddtl)
		    ->delete();		

        $q = DB::update("
            UPDATE A SET
                A.TOTAL_JML_TERIMA = (
                    SELECT 
                        SUM(ISNULL(XB.JML_TERIMA,0))
                    FROM VCH_TRN_PENERIMAAN_DTL XB
                    WHERE 1=1
                        AND XB.KD_PERUSAHAAN        = A.KD_PERUSAHAAN
                        AND XB.KD_LOKASI            = A.KD_LOKASI
                        AND XB.NO_TERIMA_VOUCHER    = A.NO_TERIMA_VOUCHER 
                ),
                A.TOTAL_TERIMA = (
                    SELECT 
                        TOTAL = SUM(ISNULL(TOT_JML * TOT_VOUCHER,0))
                    FROM 
                    (
                        SELECT 
                            XC.JML_TERIMA AS TOT_JML,
                            XC.NOMINAL AS TOT_VOUCHER
                        FROM VCH_TRN_PENERIMAAN_DTL XC
                        WHERE 1=1
                            AND XC.KD_PERUSAHAAN        = A.KD_PERUSAHAAN
                            AND XC.KD_LOKASI	        = A.KD_LOKASI
                            AND XC.NO_TERIMA_VOUCHER    = A.NO_TERIMA_VOUCHER
                    ) V_DTL
                ),
                A.USER_UPDATE   = '".$user."',
                A.TGL_UPDATE    = '".$tgl."'
            FROM VCH_TRN_PENERIMAAN A
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
                AND A.NO_TERIMA_VOUCHER = '".$no_penerimaan."'
        ");

		return $q;
    }

    public static function delete_dt($no_penerimaan, $user, $tgl)
    {
        $q = DB::table('VCH_TRN_PENERIMAAN')
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->update(
                [
                    'STATUS'		    => 'C',
                    'FLAG_AKTIF'        => 'N',
                    'USER_BATAL_TERIMA' => $user,
                    'TGL_BATAL_TERIMA'  => $tgl,
                    'USER_UPDATE'       => $user,
                    'TGL_UPDATE'        => now()
                ]
            );
    }

    public static function closing($kd_unit, $kd_lokasi, $no_penerimaan, $user, $tgl) {
		DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penerimaan, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENERIMAAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_TERIMA_VOUCHER', '=', $no_penerimaan)
            ->update([
                'STATUS'		=> 'Y',
                'USER_APPROVE'	=> $user,
                'TGL_APPROVE'	=> $tgl,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            $q = DB::select("
                SELECT 
                    NO_TERIMA_VOUCHER, 
                    KD_VOUCHER,
                    TGL_TERIMA, 
                    YYYYMM,
                    KD_BAGIAN, 
                    SUM(ISNULL(JML_TERIMA,0)) JML_TERIMA,
                    SUM(ISNULL(TOT_NOMINAL,0)) NOMINAL,
                    TOTAL_JML_TERIMA, 
                    TOTAL_TERIMA
                FROM
                (
                    SELECT
                        A.NO_TERIMA_VOUCHER, 
                        B.KD_VOUCHER,
                        A.TGL_TERIMA,
                        CONVERT(CHAR(6),A.TGL_TERIMA,112) YYYYMM,
                        A.KD_BAGIAN, 
                        B.JML_TERIMA,
                        TOT_NOMINAL = B.JML_TERIMA * B.NOMINAL,
                        A.TOTAL_JML_TERIMA, 
                        A.TOTAL_TERIMA
                    FROM VCH_TRN_PENERIMAAN A
                    INNER JOIN VCH_TRN_PENERIMAAN_DTL B ON
                        A.KD_PERUSAHAAN			= B.KD_PERUSAHAAN
                        AND A.KD_LOKASI			= B.KD_LOKASI
                        AND A.NO_TERIMA_VOUCHER	= B.NO_TERIMA_VOUCHER
                    WHERE 1=1
                        AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                        AND A.KD_LOKASI         = '".$kd_lokasi."'
                        AND A.NO_TERIMA_VOUCHER	= '".$no_penerimaan."'
                ) V_DTL_TERIMA
                GROUP BY
                    NO_TERIMA_VOUCHER, 
                    KD_VOUCHER,
                    TGL_TERIMA, 
                    YYYYMM,
                    KD_BAGIAN, 
                    TOTAL_JML_TERIMA, 
                    TOTAL_TERIMA
            ");
 
			foreach ($q as $row) {
                $kd_voucher     = $row->KD_VOUCHER;     
				$tgl_terima     = $row->TGL_TERIMA;       
                $kd_bagian      = $row->KD_BAGIAN;
                $yyyymm         = $row->YYYYMM;       
                $jumlah         = $row->JML_TERIMA;
                $nominal        = $row->NOMINAL;
                $totjml         = $row->TOTAL_JML_TERIMA;
                $totnominal     = $row->TOTAL_TERIMA;

                $q_exist = DB::select("
                    SELECT 
                        CNT_RKP		= ROWID,
                        XN_OLDJML	= ISNULL(IN_JML,0),
                        XN_OLDNOM	= ISNULL(IN_NOM,0)
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
                            'IN_JML'		=> $jumlah + $jmloldkp, 
                            'IN_NOM'		=> $nominal + $nomoldrkp,  
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
                            'IN_JML'        => $jumlah,  
                            'IN_NOM'        => $nominal,  
                            'AI_JML'        => 0,  
                            'AI_NOM'        => 0,
                            'OT_JML'        => 0,
                            'OT_NOM'        => 0, 
                            'MO_JML'        => 0,
                            'MO_NOM'        => 0,  
                            'AO_JML'        => 0,
                            'AO_NOM'        => 0,
                            'GC_JML'        => 0,
                            'GC_NOM'        => 0,
                            'MC_JML'        => 0,
                            'MC_NOM'        => 0, 
                            'BG_TOT'        => 0,
                            'FG_TIPE'       => 'INFA',  
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
                    'NO_REF'                => $no_penerimaan,
                    'TGL_TRX'               => $tgl_terima,
                    'FLAG_IO'               => 'IN',
                    'KEY1'                  => 'INIT',
                    'JML'                   => $jumlah, 
                    'NOMINAL'               => $nominal, 
                    'TOTAL_JML'             => $totjml, 
                    'TOTAL_NOMINAL'         => $totnominal, 
                    'FG_TIPE'               => 'INFA', 
                    'USER_ENTRY'            => $user, 
                    'TGL_ENTRY'             => $tgl,
                    'USER_UPDATE'           => $user, 
                    'TGL_UPDATE'            => $tgl
                ]);
            }	
        });	
	}

}
