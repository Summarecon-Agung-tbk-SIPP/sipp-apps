<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_CeklistTerimaVoucher extends Model
{
    public static function search_pengajuan($keyword,$kd_unit,$kd_lokasi,$user)
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
                B.TOTAL_JML_VOUCHER ,
                B.TOTAL_VOUCHER
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
                AND B.USER_PEMOHON      = '".$user."'
                AND A.STATUS            = 'Y'
            ORDER BY 
                A.ROWID 
            ASC
        ");

        return $q;
    }

    public static function update_dtl($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $cek_data, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $cek_data, $user, $tgl) {
            $q = DB::table('VCH_TRN_PENGELUARAN_DTL')
            ->where('ROWID', '=', $cek_data)
            ->update([
                'FLAG_CEK'      => 'Y',
                'USER_CEK'      => $user,
                'TGL_CEK'       => $tgl,
                'USER_UPDATE'	=> $user,
                'TGL_UPDATE'	=> $tgl
            ]);

            return $no_penyerahan;
        });	
	}

    public static function stok_rekap($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $user, $tgl) {
            $q = DB::select("
                SELECT 
                    NO_PERMINTAAN, 
                    NO_PENGELUARAN, 
                    KD_VOUCHER,
                    TGL_PERMINTAAN, 
                    YYYYMM,
                    KD_BAGIAN, 
                    SUM(ISNULL(JML_KELUAR,0)) JML_KELUAR,
                    SUM(ISNULL(TOT_NOMINAL,0)) NOMINAL,
                    TOTAL_JML_VOUCHER, 
                    TOTAL_VOUCHER
                FROM
                (
                    SELECT
                        A.NO_PERMINTAAN, 
                        B.NO_PENGELUARAN, 
                        B.KD_VOUCHER,
                        A.TGL_PERMINTAAN,
                        CONVERT(CHAR(6),A.TGL_PERMINTAAN,112) YYYYMM,
                        A.KD_BAGIAN, 
                        B.JML_KELUAR,
                        TOT_NOMINAL = B.JML_KELUAR * B.NOMINAL_KELUAR,
                        A.TOTAL_JML_VOUCHER, 
                        A.TOTAL_VOUCHER
                    FROM VCH_TRN_PERMINTAAN A
                    INNER JOIN VCH_TRN_PENGELUARAN_DTL B ON
                        A.KD_PERUSAHAAN		= B.KD_PERUSAHAAN
                        AND A.KD_LOKASI		= B.KD_LOKASI
                        AND A.NO_PERMINTAAN	= B.NO_PERMINTAAN
                    WHERE 1=1
                        AND A.KD_PERUSAHAAN = '".$kd_unit."'
                        AND A.KD_LOKASI     = '".$kd_lokasi."'
                        AND A.NO_PERMINTAAN	= '".$no_pengajuan."'
                ) V_DTL_TERIMA
                GROUP BY
                    NO_PERMINTAAN, 
                    NO_PENGELUARAN, 
                    KD_VOUCHER,
                    TGL_PERMINTAAN, 
                    YYYYMM,
                    KD_BAGIAN, 
                    TOTAL_JML_VOUCHER, 
                    TOTAL_VOUCHER
            ");

            foreach ($q as $row) {
                $kd_voucher     = $row->KD_VOUCHER;     
                $tgl_permintaan = $row->TGL_PERMINTAAN;       
                $kd_bagian      = $row->KD_BAGIAN;
                $yyyymm         = $row->YYYYMM;       
                $jumlah         = $row->JML_KELUAR;
                $nominal        = $row->NOMINAL;
                $totjml         = $row->TOTAL_JML_VOUCHER;
                $totnominal     = $row->TOTAL_VOUCHER;

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
                            'FG_TIPE'       => 'INDP',  
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
                    'NO_REF'                => $no_pengajuan,
                    'TGL_TRX'               => $tgl_permintaan,
                    'FLAG_IO'               => 'IN',
                    'KEY1'                  => $no_penyerahan,
                    'JML'                   => $jumlah, 
                    'NOMINAL'               => $nominal, 
                    'TOTAL_JML'             => $totjml, 
                    'TOTAL_NOMINAL'         => $totnominal, 
                    'FG_TIPE'               => 'INDP', 
                    'USER_ENTRY'            => $user, 
                    'TGL_ENTRY'             => $tgl,
                    'USER_UPDATE'           => $user, 
                    'TGL_UPDATE'            => $tgl
                ]);
            }

            // return $q;
        });	
	}
    
    public static function show_detail($kd_unit,$kd_lokasi,$no_penyerahan)
    {
        $q = DB::table('VCH_TRN_PENGELUARAN_DTL AS A')
            ->select(
                'KD_PERUSAHAAN', 
                'KD_LOKASI', 
                'NO_PENGELUARAN', 
                'NO_PERMINTAAN', 
                'KD_VOUCHER', 
                'NO_BARIS', 
                'JML_KELUAR', 
                'JML_OPNAME', 
                'NOMINAL_KELUAR', 
                'FLAG_CEK',
                'USER_CEK',
                'TGL_CEK',
                'ROWID'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PENGELUARAN', '=', $no_penyerahan)
            ->where('FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }
}
