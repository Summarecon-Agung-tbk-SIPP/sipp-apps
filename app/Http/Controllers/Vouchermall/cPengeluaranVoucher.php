<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Vouchermall\cSysVoucher;

use App\Models\Vouchermall\M_PengeluaranVoucher;

class cPengeluaranVoucher extends Controller
{
	private $sysController;
	private $master;

	public function __construct()
	{
		$this->sysController = new SysController();
		$this->master = new cSysVoucher();
	}

    public function index(Request $r)
    {
    	$user_id 		= $r->session()->get('user_id');
		$kd_unit 		= $r->session()->get('kd_unit');
    	$button 		= $this->sysController->get_button($r);
    	$data_user 		= $this->master->get_data_user($user_id);
		$data_voucher 	= $this->master->get_data_voucher($r);

    	$dt = array(
    		'button' 		=> $button,
    		'data_user'		=> $data_user,
			'data_voucher'	=> $data_voucher
    	);

    	return view('vouchermall.V_PengeluaranVoucher')->with('dt', $dt);
    }

	public function search_pengajuan(Request $r)
	{
		
		$keyword    = $r->keyword;
		$kd_unit	= $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$user   	= $r->session()->get('user_id');

		$q = M_PengeluaranVoucher::search_pengajuan($keyword,$kd_unit,$kd_lokasi,$user);

		return response()->json($q);
	}

	public function search_dt(Request $r)
	{
		
		$keyword    = $r->keyword;
		$kd_unit	= $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$user   	= $r->session()->get('user_id');

		$q = M_PengeluaranVoucher::search_dt($keyword,$kd_unit,$kd_lokasi,$user);

		return response()->json($q);
	}

	public function search_data_voucher(Request $r)
	{
		$kd_unit	= $r->kd_unit;
		$user   	= $r->session()->get('user_id');

		$q = $this->master->search_data_voucher($kd_unit); 

		return response()->json($q);
	}

	public function closing(Request $r)
    {
		$kd_unit    	= $r->kd_unit;
		$kd_lokasi  	= $r->kd_lokasi;
		$no_penyerahan	= $r->no_penyerahan;
        $user           = $r->session()->get('user_id');
        $tgl            = date('Y-m-d H:i:s');

        $q = M_PengeluaranVoucher::closing($kd_unit, $kd_lokasi, $no_penyerahan, $user, $tgl);
    }


	public function get_voucher(Request $r)
	{
		$kd_unit    = $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$kode		= $r->kode;

		$q = M_PengeluaranVoucher::get_voucher($kd_unit,$kd_lokasi,$kode);

		return $q;
	}

	public function save(Request $r)
	{
		$act 			= $r->act;
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penyerahan 	= $r->no_penyerahan;
		$no_pengajuan 	= $r->no_pengajuan;
		$tgl_keluar_db 	= $r->tgl_keluar;
		$tgl_keluar		= $this->sysController->date_db($tgl_keluar_db);
		$kd_departemen	= $r->kd_departemen;
		$jumlahheader	= $r->jumlahheader;
		$jumlah 		= $r->jumlah;
		$nominal 		= $r->nominal;
		$tot_voucher	= $r->tot_voucher;
		$total			= $r->total;

		$tot_voucher    = preg_replace("/([^0-9\\.])/i", "", $tot_voucher);
		$tot_voucher    = (is_numeric($tot_voucher)) ? $tot_voucher : 0;
		$total    		= preg_replace("/([^0-9\\.])/i", "", $total);
		$total    		= (is_numeric($total)) ? $total : 0;
		
		$user 			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		if($act == "add"){
			$q = M_PengeluaranVoucher::insert_dt($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $tgl_keluar, $kd_departemen, $jumlahheader, $tot_voucher, $user, $tgl);
			$no_penyerahan = $q;
			echo $no_penyerahan;			
		}else{
			if($total !=  $tot_voucher){
				echo 'Total Detail Pengeluaran Voucher, Tidak sesuai dengan Total Voucher Pengajuan';	
				return false;			
			}else{
				$q = M_PengeluaranVoucher::update_dt($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $tgl_keluar, $kd_departemen, $jumlahheader, $tot_voucher, $user, $tgl);
				echo 'Data Berhasil Disimpan';	
			}
		}

		/* === Detail === */
		$add_kd_voucher 		= $r->add_kd_voucher;
		$add_kd_barcode 		= $r->add_kd_barcode;
		$add_no_baris			= $r->add_no_baris;
		$add_qty 				= $r->add_qty;
		$add_nominal_voucher 	= $r->add_nominal_voucher;
		$add_fg_add				= $r->add_fg_add;	

		if($add_kd_voucher != null){
			$add_total 	= count($add_kd_voucher);

			for ($i=0; $i < $add_total; $i++) { 
				if(trim($no_penyerahan) != ""){
					$isExist = M_PengeluaranVoucher::cekExist($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher[$i], $add_no_baris[$i]);
					if ($isExist) {
						$update_dtl = M_PengeluaranVoucher::update_dtl($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}else{
						$insert_dtl = M_PengeluaranVoucher::insert_dtl($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);					
					}
				}
			}
		}
	}

	public function show_dtl(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penyerahan 	= $r->no_penyerahan;

		$q = M_PengeluaranVoucher::show_detail($kd_unit,$kd_lokasi,$no_penyerahan);
		return $q;
	}

	public function delete_detail(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penyerahan 	= $r->no_penyerahan;
		$rowiddtl  		= $r->rowiddtl;
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_PengeluaranVoucher::delete_detail($kd_unit,$kd_lokasi,$no_penyerahan,$rowiddtl,$user,$tgl);

		return response()->json($q);
	}

	public function delete_dt(Request $r)
	{
		$no_penyerahan 	= $r->no_penyerahan;
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_PengeluaranVoucher::delete_dt($no_penyerahan,$user,$tgl);

		return response()->json($q);
	}
}
