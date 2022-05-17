<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Vouchermall\cSysVoucher;

use App\Models\Vouchermall\M_TerimaVoucher;

class cTerimaVoucher extends Controller
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

    	return view('vouchermall.V_TerimaVoucher')->with('dt', $dt);
    }

	public function search_dt(Request $r)
	{
		
		$keyword    = $r->keyword;
		$kd_unit	= $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$user   	= $r->session()->get('user_id');

		$q = M_TerimaVoucher::search_dt($keyword,$kd_unit,$kd_lokasi,$user);

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
		$no_penerimaan	= $r->no_penerimaan;
        $user           = $r->session()->get('user_id');
        $tgl            = date('Y-m-d H:i:s');

        $q = M_TerimaVoucher::closing($kd_unit, $kd_lokasi, $no_penerimaan, $user, $tgl);
    }

	public function get_voucher(Request $r)
	{
		$kd_unit    = $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$kode		= $r->kode;

		$q = M_TerimaVoucher::get_voucher($kd_unit,$kd_lokasi,$kode);

		return $q;
	}

	public function save(Request $r)
	{
		$act 			= $r->act;
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penerimaan 	= $r->no_penerimaan;
		$tgl_terima_db 	= $r->tgl_terima;
		$tgl_terima		= $this->sysController->date_db($tgl_terima_db);
		$kd_departemen	= $r->kd_departemen;
		
		$user 			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		if($act == "add"){
			$q = M_TerimaVoucher::insert_dt($kd_unit, $kd_lokasi, $no_penerimaan, $tgl_terima, $kd_departemen, $user, $tgl);
			$no_penerimaan = $q;
			echo $no_penerimaan;
		}else{
			$q = M_TerimaVoucher::update_dt($kd_unit, $kd_lokasi, $no_penerimaan, $tgl_terima, $kd_departemen, $user, $tgl);
		}

		/* === Detail === */
		$add_kd_voucher 		= $r->add_kd_voucher;
		$add_kd_barcode 		= $r->add_kd_barcode;
		$add_no_baris			= $r->add_no_baris;
		$add_qty 				= $r->add_qty;
		$add_nominal_voucher 	= $r->add_nominal_voucher;
		$add_fg_add				= $r->add_fg_add;
		$jumlah 				= $r->jumlah;
		$nominal 				= $r->nominal;
		$total					= $r->total;
		
		if($add_kd_voucher != null){
			$add_total 	= count($add_kd_voucher);

			for ($i=0; $i < $add_total; $i++) { 
				if(trim($no_penerimaan) != ""){
					$isExist = M_TerimaVoucher::cekExist($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher[$i], $add_no_baris[$i]);
					if ($isExist) {
						$update_dtl = M_TerimaVoucher::update_dtl($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}else{
						$insert_dtl = M_TerimaVoucher::insert_dtl($kd_unit, $kd_lokasi, $no_penerimaan, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}
				}
			}
		}
	}

	public function show_dtl(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penerimaan 	= $r->no_penerimaan;

		$q = M_TerimaVoucher::show_detail($kd_unit,$kd_lokasi,$no_penerimaan);
		return $q;
	}

	public function delete_detail(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penerimaan 	= $r->no_penerimaan;
		$rowiddtl  		= $r->rowiddtl;
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_TerimaVoucher::delete_detail($kd_unit,$kd_lokasi,$no_penerimaan,$rowiddtl,$user,$tgl);

		return response()->json($q);
	}

	public function delete_dt(Request $r)
	{
		$no_penerimaan  = $r->no_penerimaan;
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_TerimaVoucher::delete_dt($no_penerimaan,$user,$tgl);

		return response()->json($q);
	}
}
