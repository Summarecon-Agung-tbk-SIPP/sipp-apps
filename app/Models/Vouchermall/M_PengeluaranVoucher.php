<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_PengeluaranVoucher extends Model
{
    public static function search_pengajuan($keyword,$kd_unit,$kd_lokasi,$user)
    {
        $q = DB::select("
            SELECT 
                A.KD_PERUSAHAAN,
                A.KD_LOKASI,
                A.NO_PERMINTAAN,
                CONVERT(VARCHAR(10),A.TGL_PERMINTAAN,103) TGL_PERMINTAAN,
                CONVERT(VARCHAR(10),A.TGL_BUTUH,103) TGL_BUTUH,
                B.NAMA + '  -  ' + A.USER_PEMOHON AS NAMA_USER,
                A.KD_BAGIAN,
                C.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN,
                A.KEPERLUAN,
                ISNULL(A.TOTAL_JML_VOUCHER,0) TOTAL_JML_VOUCHER, 
                ISNULL(A.TOTAL_VOUCHER,0) TOTAL_VOUCHER,
                A.STATUS,
                D.GH_FUNCTION_DESC AS TITLE_STATUS,
                A.FLAG_AKTIF,
                A.ROWID
            FROM VCH_TRN_PERMINTAAN A
            INNER JOIN T_USER B ON
                A.USER_PEMOHON					= B.KODE_USER
            INNER JOIN [DBGJI].[HRMS].[dbo].[TBL_GRUP_BAGIAN] C ON
                A.KD_BAGIAN						= C.KD_GRUP_BAGIAN
            INNER JOIN GS_GEN_HARDCODED D ON
                A.STATUS						= D.GH_FUNCTION_CODE
                AND D.GH_SYS					= 'H'
                AND D.GH_FUNCTION_NAME			= 'VCH_STATUS'
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
                AND A.STATUS            = 'L'
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
                A.NO_PENGELUARAN, 
                A.NO_PERMINTAAN, 
                CONVERT(VARCHAR(10),A.TGL_KELUAR,103) TGL_KELUAR,
                A.DISERAHKAN_OLEH, 
                C.NAMA + '  -  ' + A.DISERAHKAN_OLEH AS NAMA_SERAH,
                D.NAMA + '  -  ' + B.USER_PEMOHON AS NAMA_PEMOHON,
                A.KD_BAGIAN, 
                E.NAMA_GRUP_BAGIAN AS NM_DEPARTEMEN,
                A.TIPE_KELUAR, 
                A.STATUS, 
                F.GH_FUNCTION_DESC AS TITLE_STATUS,
                A.FLAG_AKTIF, 
                A.ROWID,
                CONVERT(VARCHAR(10),B.TGL_PERMINTAAN,103) TGL_PERMINTAAN,
                CONVERT(VARCHAR(10),B.TGL_BUTUH,103) TGL_BUTUH,
                B.KEPERLUAN,
                ISNULL(A.TOTAL_JML_KELUAR,0) TOTAL_JML_KELUAR, 
                ISNULL(A.TOTAL_NOMINAL_KELUAR,0) TOTAL_NOMINAL_KELUAR,
                ISNULL(B.TOTAL_JML_VOUCHER,0) TOTAL_JML_VOUCHER, 
                ISNULL(B.TOTAL_VOUCHER,0) TOTAL_VOUCHER
            FROM VCH_TRN_PENGELUARAN A
            INNER JOIN VCH_TRN_PERMINTAAN B ON
                A.KD_PERUSAHAAN				    = B.KD_PERUSAHAAN
                AND A.KD_LOKASI				    = B.KD_LOKASI
                AND A.NO_PERMINTAAN			    = B.NO_PERMINTAAN
            INNER JOIN T_USER C ON
                A.DISERAHKAN_OLEH				= C.KODE_USER
            INNER JOIN T_USER D ON
                B.USER_PEMOHON				    = D.KODE_USER
            INNER JOIN [DBGJI].[HRMS].[dbo].[TBL_GRUP_BAGIAN] E ON
                A.KD_BAGIAN						= E.KD_GRUP_BAGIAN
            INNER JOIN GS_GEN_HARDCODED F ON
                A.STATUS						= F.GH_FUNCTION_CODE
                AND F.GH_SYS					= 'H'
                AND F.GH_FUNCTION_NAME			= 'VCH_STATUS'
            WHERE 1=1
                AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                AND A.KD_LOKASI         = '".$kd_lokasi."'
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

    public static function insert_dt($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $tgl_keluar, $kd_departemen, $jumlahheader, $tot_voucher, $user, $tgl)
	{

        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $tgl_keluar, $kd_departemen, $jumlahheader, $tot_voucher, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;

            $kd_fungsi 		= 'PVK';
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

			$no_penyerahan = '';
			foreach ($q as $row) {
				$no_penyerahan = $row->KD_FUNGSI.$row->TAHUN.$row->BULAN.$row->SEQUENCE;
			}			
			
			$q 	= DB::table('VCH_TRN_PENGELUARAN')
			->insert([
                'KD_PERUSAHAAN'			=> $kd_unit, 
				'KD_LOKASI'				=> $kd_lokasi, 
                'NO_PENGELUARAN'        => $no_penyerahan, 
                'NO_PERMINTAAN'         => $no_pengajuan, 
                'TGL_KELUAR'            => $tgl_keluar, 
                'DISERAHKAN_OLEH'       => $user,  
                'KD_BAGIAN'             => $kd_departemen, 
                'TOTAL_JML_KELUAR'      => $jumlahheader,  
                'TOTAL_NOMINAL_KELUAR'  => $tot_voucher, 
                'TIPE_KELUAR'           => 'I',  
                'STATUS'				=> 'N',
				'FLAG_AKTIF'			=> 'Y',  
				'USER_ENTRY'			=> $user, 
				'TGL_ENTRY'				=> $tgl
			]);

            // echo 'sukses_'.$no_penyerahan;
            return $no_penyerahan;
        });
	}

    public static function update_dt($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $tgl_keluar, $kd_departemen, $jumlahheader, $tot_voucher, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $tgl_keluar, $kd_departemen, $jumlahheader, $tot_voucher, $user, $tgl) {
            $tot_voucher  = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher  = (is_numeric($tot_voucher)) ? $tot_voucher : 0;

            $q = DB::table('VCH_TRN_PENGELUARAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENGELUARAN', '=', $no_penyerahan)
            ->update([
                'NO_PERMINTAAN'         => $no_pengajuan, 
                'TOTAL_JML_KELUAR'      => $jumlahheader,  
                'TOTAL_NOMINAL_KELUAR'  => $tot_voucher, 
                'USER_UPDATE'	        => $user,
                'TGL_UPDATE'	        => $tgl
            ]);

            return $no_penyerahan;
        });	
	}

    public static function cekExist($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher, $add_no_baris)
    {
        $q = DB::table('VCH_TRN_PENGELUARAN_DTL')
            ->select(
                'NO_BARIS'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENGELUARAN', '=', $no_penyerahan)
            ->where('NO_PERMINTAAN', '=', $no_pengajuan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->exists();

        return $q;
    }

	public static function insert_dtl($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;
            
            $q = DB::table('VCH_TRN_PENGELUARAN_DTL')
            ->insert([
                'KD_PERUSAHAAN'         => $kd_unit,
                'KD_LOKASI'             => $kd_lokasi,
                'NO_PENGELUARAN'        => $no_penyerahan,
                'NO_PERMINTAAN'         => $no_pengajuan,
                'KD_VOUCHER'            => $add_kd_voucher, 
                'NO_BARIS'              => $add_no_baris,
                'JML_KELUAR'            => $add_qty, 
                'NOMINAL_KELUAR'        => $add_nominal_voucher, 
                'FG_ADD'                => 'Y', 
                'FLAG_CEK'              => 'N', 
                'FLAG_AKTIF'            => 'Y', 
                'USER_ENTRY'            => $user, 
                'TGL_ENTRY'             => $tgl
            ]);

            return $q;
        });        
	}

    public static function update_dtl($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PENGELUARAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENGELUARAN', '=', $no_penyerahan)
            ->where('NO_PERMINTAAN', '=', $no_pengajuan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->update([
                'JML_KELUAR'        => $add_qty, 
                'NOMINAL_KELUAR'    => $add_nominal_voucher, 
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            return $q;
        });        
	}

    public static function show_detail($kd_unit,$kd_lokasi,$no_penyerahan)
    {
        $q = DB::table('VCH_TRN_PENGELUARAN_DTL AS A')
            ->select(
                'A.KD_PERUSAHAAN', 
                'A.KD_LOKASI', 
                'A.NO_PENGELUARAN', 
                'A.NO_PERMINTAAN', 
                'A.KD_VOUCHER', 
                'A.NO_BARIS', 
                'A.JML_KELUAR', 
                'A.JML_OPNAME', 
                'A.NOMINAL_KELUAR', 
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
            ->where('A.NO_PENGELUARAN', '=', $no_penyerahan)
            ->where('A.FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function delete_detail($kd_unit,$kd_lokasi,$no_penyerahan,$rowiddtl,$user,$tgl)
    {
        $q = DB::table('VCH_TRN_PENGELUARAN_DTL')
		    ->where('ROWID', '=', $rowiddtl)
		    ->delete();

		return $q;
    }

    public static function delete_dt($no_penyerahan, $user, $tgl)
    {
        $q = DB::table('VCH_TRN_PENGELUARAN')
            ->where('NO_PENGELUARAN', '=', $no_penyerahan)
            ->update(
                [
                    'STATUS'		            => 'C',
                    'FLAG_AKTIF'                => 'N',
                    'USER_BATAL_PENGELUARAN'    => $user,
                    'TGL_BATAL_PENGELUARAN'     => $tgl,
                    'USER_UPDATE'               => $user,
                    'TGL_UPDATE'                => now()
                ]
            );
    }

    public static function closing($kd_unit, $kd_lokasi, $no_penyerahan, $user, $tgl) {
		DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENGELUARAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENGELUARAN', '=', $no_penyerahan)
            ->update([
                'STATUS'		=> 'Y',
                'USER_APPROVE'	=> $user,
                'TGL_APPROVE'	=> $tgl,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            $q = DB::select("
                SELECT 
                    NO_PENGELUARAN, 
                    NO_PERMINTAAN, 
                    KD_VOUCHER,
                    TGL_KELUAR, 
                    YYYYMM,
                    KD_BAGIAN, 
                    SUM(ISNULL(JML_KELUAR,0)) JML_KELUAR,
                    SUM(ISNULL(TOT_NOMINAL,0)) NOMINAL,
                    TOTAL_JML_KELUAR, 
                    TOTAL_NOMINAL_KELUAR
                FROM
                (
                    SELECT
                        A.NO_PENGELUARAN, 
                        A.NO_PERMINTAAN, 
                        B.KD_VOUCHER,
                        A.TGL_KELUAR,
                        CONVERT(CHAR(6),A.TGL_KELUAR,112) YYYYMM,
                        A.KD_BAGIAN, 
                        B.JML_KELUAR,
                        TOT_NOMINAL = B.JML_KELUAR * B.NOMINAL_KELUAR,
                        A.TOTAL_JML_KELUAR, 
                        A.TOTAL_NOMINAL_KELUAR
                    FROM VCH_TRN_PENGELUARAN A
                    INNER JOIN VCH_TRN_PENGELUARAN_DTL B ON
                        A.KD_PERUSAHAAN			= B.KD_PERUSAHAAN
                        AND A.KD_LOKASI			= B.KD_LOKASI
                        AND A.NO_PENGELUARAN	= B.NO_PENGELUARAN
                        AND A.NO_PERMINTAAN		= B.NO_PERMINTAAN
                    WHERE 1=1
                        AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                        AND A.KD_LOKASI         = '".$kd_lokasi."'
                        AND A.NO_PENGELUARAN	= '".$no_penyerahan."'
                ) V_DTL_TERIMA
                GROUP BY
                    NO_PENGELUARAN, 
                    NO_PERMINTAAN, 
                    KD_VOUCHER,
                    TGL_KELUAR, 
                    YYYYMM,
                    KD_BAGIAN, 
                    TOTAL_JML_KELUAR, 
                    TOTAL_NOMINAL_KELUAR
            ");

            foreach ($q as $row) {
                $no_permintaan  = $row->NO_PERMINTAAN;    
                $kd_voucher     = $row->KD_VOUCHER;     
                $tgl_keluar     = $row->TGL_KELUAR;       
                $kd_bagian      = $row->KD_BAGIAN;
                $yyyymm         = $row->YYYYMM;       
                $jumlah         = $row->JML_KELUAR;
                $nominal        = $row->NOMINAL;
                $totjml         = $row->TOTAL_JML_KELUAR;
                $totnominal     = $row->TOTAL_NOMINAL_KELUAR;

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
                    'NO_REF'                => $no_penyerahan,
                    'TGL_TRX'               => $tgl_keluar,
                    'FLAG_IO'               => 'OT',
                    'KEY1'                  => $no_permintaan,
                    'JML'                   => $jumlah, 
                    'NOMINAL'               => $nominal, 
                    'TOTAL_JML'             => $totjml, 
                    'TOTAL_NOMINAL'         => $totnominal, 
                    'FG_TIPE'               => 'OTFA', 
                    'USER_ENTRY'            => $user, 
                    'TGL_ENTRY'             => $tgl,
                    'USER_UPDATE'           => $user, 
                    'TGL_UPDATE'            => $tgl
                ]);
            }	
        });	
	}

}
