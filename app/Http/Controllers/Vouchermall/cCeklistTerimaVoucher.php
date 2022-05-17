<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Vouchermall\cSysVoucher;

use App\Models\Vouchermall\M_CeklistTerimaVoucher;

class cCeklistTerimaVoucher extends Controller
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

    	return view('vouchermall.V_CeklistTerimaVoucher')->with('dt', $dt);
    }

	public function search_pengajuan(Request $r)
	{
		
		$keyword    = $r->keyword;
		$kd_unit	= $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$user   	= $r->session()->get('user_id');

		$q = M_CeklistTerimaVoucher::search_pengajuan($keyword,$kd_unit,$kd_lokasi,$user);

		return response()->json($q);
	}

	public function save(Request $r)
	{
		$act 			= $r->act;
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penyerahan 	= $r->no_penyerahan;
		$no_pengajuan 	= $r->no_pengajuan;
		$kd_departemen	= $r->kd_departemen;
		
		$user 			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		/* === Detail === */
		$cek_data 		= $r->cek_data;

		if($cek_data != null){
			$add_total 	= count($cek_data);

			for ($i=0; $i < $add_total; $i++) { 
				if(trim($no_penyerahan) != ""){
					M_CeklistTerimaVoucher::update_dtl($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $cek_data[$i], $user, $tgl);
				}
			}
		}

		M_CeklistTerimaVoucher::stok_rekap($kd_unit, $kd_lokasi, $no_penyerahan, $no_pengajuan, $user, $tgl);
	}

	public function show_dtl(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_penyerahan 	= $r->no_penyerahan;

		$q = M_CeklistTerimaVoucher::show_detail($kd_unit,$kd_lokasi,$no_penyerahan);
		return $q;
	}
}
