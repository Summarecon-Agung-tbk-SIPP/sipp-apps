<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_PengajuanVoucher extends Model
{
    public static function search_dt($keyword,$kd_unit,$kd_lokasi,$user)
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

    public static function insert_dt($kd_unit, $kd_lokasi, $no_pengajuan, $status_approval, $tgl_pengajuan, $kd_departemen, $keperluan, $tgl_butuh, $jumlahheader, $tot_voucher, $user, $tgl)
	{

        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_pengajuan, $status_approval, $tgl_pengajuan, $kd_departemen, $keperluan, $tgl_butuh, $jumlahheader, $tot_voucher, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;
            $kd_fungsi 		= 'PVR';
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

			$no_pengajuan = '';
			foreach ($q as $row) {
				$no_pengajuan = $row->KD_FUNGSI.$row->TAHUN.$row->BULAN.$row->SEQUENCE;
			}			
			
			$q 	= DB::table('VCH_TRN_PERMINTAAN')
			->insert([				
                'KD_PERUSAHAAN'         => $kd_unit, 
                'KD_LOKASI'             => $kd_lokasi, 
                'NO_PERMINTAAN'         => $no_pengajuan, 
                'TGL_PERMINTAAN'        => $tgl_pengajuan, 
                'TGL_BUTUH'             => $tgl_butuh, 
                'USER_PEMOHON'          => $user, 
                'KD_BAGIAN'             => $kd_departemen, 
                'KEPERLUAN'             => $keperluan, 
                'TOTAL_JML_VOUCHER'     => $jumlahheader,
                'TOTAL_VOUCHER'         => $tot_voucher,
                'STATUS'                => $status_approval, 
                'FLAG_AKTIF'            => 'Y', 
				'USER_ENTRY'			=> $user, 
				'TGL_ENTRY'				=> $tgl
			]);

            return $no_pengajuan;
        });
	}

    public static function update_dt($kd_unit, $kd_lokasi, $no_pengajuan, $status_approval, $tgl_pengajuan, $kd_departemen, $keperluan, $tgl_butuh, $jumlahheader, $tot_voucher, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_pengajuan, $status_approval, $tgl_pengajuan, $kd_departemen, $keperluan, $tgl_butuh, $jumlahheader, $tot_voucher, $user, $tgl) {
            $tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
            $tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;
            
            $q = DB::table('VCH_TRN_PERMINTAAN')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PERMINTAAN', '=', $no_pengajuan)
            ->update([
                'TOTAL_JML_VOUCHER'     => $jumlahheader,
                'TOTAL_VOUCHER'         => $tot_voucher,
                'KEPERLUAN'             => $keperluan, 
                'TGL_BUTUH'             => $tgl_butuh, 
                'USER_UPDATE'	        => $user,
                'TGL_UPDATE'	        => $tgl
            ]);

            return $no_pengajuan;
        });	
	}

    public static function cekExist($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher, $add_no_baris)
    {
        $q = DB::table('VCH_TRN_PERMINTAAN_DTL')
            ->select(
                'NO_BARIS'
            )
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PERMINTAAN', '=', $no_pengajuan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->exists();

        return $q;
    }

	public static function insert_dtl($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PERMINTAAN_DTL')
            ->insert([
                'KD_PERUSAHAAN'     => $kd_unit,
                'KD_LOKASI'         => $kd_lokasi,
                'NO_PERMINTAAN'     => $no_pengajuan,
                'KD_VOUCHER'        => $add_kd_voucher,
                'NO_BARIS'          => $add_no_baris,
                'JML_DIMINTA'       => $add_qty,
                'NOMINAL_DIMINTA'   => $add_nominal_voucher,
                'FG_ADD'            => 'Y', 
                'FLAG_AKTIF'        => 'Y', 
                'USER_ENTRY'        => $user, 
                'TGL_ENTRY'         => $tgl
            ]);

            return $q;
        });        
	}

	public static function update_dtl($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl)
	{
        return DB::transaction(function () use ($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher, $add_kd_barcode, $add_no_baris, $add_qty, $add_nominal_voucher, $jumlah, $nominal, $total, $user, $tgl) {
            $add_nominal_voucher    = preg_replace("/([^0-9\\.])/i", "", $add_nominal_voucher);
            $add_nominal_voucher    = (is_numeric($add_nominal_voucher)) ? $add_nominal_voucher : 0;
            $jumlah                 = preg_replace("/([^0-9\\.])/i", "", $jumlah);
            $jumlah                 = (is_numeric($jumlah)) ? $jumlah : 0;
            $total                  = preg_replace("/([^0-9\\.])/i", "", $total);
            $total                  = (is_numeric($total)) ? $total : 0;

            $q = DB::table('VCH_TRN_PERMINTAAN_DTL')
            ->where('KD_PERUSAHAAN', '=', $kd_unit)
            ->where('KD_LOKASI', '=', $kd_lokasi)
            ->where('NO_PERMINTAAN', '=', $no_pengajuan)
            ->where('KD_VOUCHER', '=', $add_kd_voucher)
            ->where('NO_BARIS', '=', $add_no_baris)
            ->update([
                'JML_DIMINTA'       => $add_qty,
                'NOMINAL_DIMINTA'   => $add_nominal_voucher,
                'USER_UPDATE'	    => $user,
                'TGL_UPDATE'	    => $tgl
            ]);

            return $q;
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
            ->where('A.NO_PERMINTAAN', '=', $no_pengajuan)
            ->where('A.FLAG_AKTIF', '=', 'Y')
            ->get();

        return $q;
    }

    public static function delete_detail($kd_unit,$kd_lokasi,$no_pengajuan,$rowiddtl,$user,$tgl)
    {
        return DB::transaction(function () use ($kd_unit,$kd_lokasi,$no_pengajuan,$rowiddtl,$user,$tgl) {

            $q = DB::table('VCH_TRN_PERMINTAAN_DTL')
                ->where('ROWID', '=', $rowiddtl)
                ->delete();

            $q = DB::update("
                UPDATE A SET
                    A.TOTAL_JML_VOUCHER = (
                        SELECT 
                            SUM(ISNULL(XB.JML_DIMINTA,0))
                        FROM VCH_TRN_PERMINTAAN_DTL XB
                        WHERE 1=1
                            AND XB.KD_PERUSAHAAN        = A.KD_PERUSAHAAN
                            AND XB.KD_LOKASI            = A.KD_LOKASI
                            AND XB.NO_PERMINTAAN        = A.NO_PERMINTAAN 
                    ),
                    A.TOTAL_PEMBAGIAN = (
                        SELECT 
                            TOTAL = SUM(ISNULL(TOT_JML * TOT_VOUCHER,0))
                        FROM 
                        (
                            SELECT 
                                XC.JML_DIMINTA AS TOT_JML,
                                XC.NOMINAL_DIMINTA AS TOT_VOUCHER
                            FROM VCH_TRN_PERMINTAAN_DTL XC
                            WHERE 1=1
                                AND XC.KD_PERUSAHAAN    = A.KD_PERUSAHAAN
                                AND XC.KD_LOKASI	    = A.KD_LOKASI
                                AND XC.NO_PERMINTAAN    = A.NO_PERMINTAAN
                        ) V_DTL
                    ),
                    A.USER_UPDATE   = '".$user."',
                    A.TGL_UPDATE    = '".$tgl."'
                FROM VCH_TRN_PERMINTAAN A
                WHERE 1=1
                    AND A.KD_PERUSAHAAN     = '".$kd_unit."'
                    AND A.KD_LOKASI         = '".$kd_lokasi."'
                    AND A.NO_PERMINTAAN     = '".$no_pengajuan."'
            ");

		    return $q;
        });        
    }

    public static function delete_dt($rowid, $user)
    {
        $q = DB::table('VCH_TRN_PERMINTAAN')
            ->where('ROWID', '=', $rowid)
            ->update(
                [
                    'FLAG_AKTIF'    => 'N',
                    'USER_UPDATE'    => $user,
                    'TGL_UPDATE'     => now()
                ]
            );
    }
}
