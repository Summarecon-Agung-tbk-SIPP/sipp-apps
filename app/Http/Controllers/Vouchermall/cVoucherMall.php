<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Budget\CMaster;

use App\Models\Vouchermall\M_VoucherMall;

class cVoucherMall extends Controller
{
	private $sysController;
	private $master;

	public function __construct()
	{
		$this->sysController = new SysController();
		$this->master = new CMaster();
	}

    public function index(Request $r)
    {
    	$user_id 		= $r->session()->get('user_id');
    	$button 		= $this->sysController->get_button($r);

    	$data_user 		= $this->master->get_data_user($user_id);

    	$dt = array(
    		'button' 		=> $button,
    		'data_user'		=> $data_user
    	);

    	return view('vouchermall.V_VoucherMall')->with('dt', $dt);
    }

	public function search_dt(Request $r)
	{
		
		$keyword    = $r->keyword;
		$user   	= $r->session()->get('user_id');

		$q = M_VoucherMall::search_dt($keyword);

		return response()->json($q);
	}

    public function get_voucher(Request $r)
	{		
		$kd_unit    = $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;

		$q = M_VoucherMall::get_voucher($kd_unit,$kd_lokasi);

		return response()->json($q);
	}

	public function delete_dt(Request $r)
	{
		$rowid  = $r->rowid;
		$user  	= $r->session()->get('user_id');

		$q = M_VoucherMall::delete_dt($rowid,$user);

		return response()->json($q);
	}

	public function save(Request $r)
	{
		$savebtnval    	= $r->saveBtnVal;
		$kd_unit		= $r->kd_unit;
		$kd_lokasi		= $r->kd_lokasi;
		$kd_voucher    	= $r->kd_voucher;
		$nm_voucher    	= $r->nm_voucher;
		$nominal    	= $r->nominal;
		$keterangan    	= $r->keterangan;
		$fg_aktif    	= $r->fg_aktif;
		$user   		= $r->session()->get('user_id');
		$tgl    		= date('Y-m-d H:i:s');

		if($savebtnval == 'create'){
			$q = M_VoucherMall::simpan_dt($kd_unit,$kd_lokasi,$kd_voucher,$nm_voucher,$nominal,$keterangan,$fg_aktif,$user,$tgl);
		}else{
			$q = M_VoucherMall::update_dt($kd_unit,$kd_lokasi,$kd_voucher,$nm_voucher,$nominal,$keterangan,$fg_aktif,$user,$tgl);
		}
	}
}
