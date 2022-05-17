<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Sys\CMaster;

use App\Models\Sys\MUser;

class CUser extends Controller
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
		$user_id 			= $r->session()->get('user_id');
		$button 			= $this->sysController->get_button($r);
		$list_role 			= $this->master->list_role();
		$list_lokasi_all 	= $this->master->list_lokasi_all($r);

		$dt = array(
			'button' 			=> $button,
			'list_role'			=> $list_role,
			'list_lokasi_all'	=> $list_lokasi_all
		);

		return view('sys.vUser')->with('dt', $dt);
	}

	public function search_dt(Request $r)
	{
		$keyword = $r->keyword;

		$q = MUser::search_dt($keyword);

		return $q;
	}

	public function save(Request $r)
	{
		$user_id 	= $r->user_id;
		$nama 		= $r->nama;
		$password 	= $r->password;
		$deskripsi 	= $r->deskripsi;
		$flag_aktif	= $r->flag_aktif;
		$act 		= $r->act;
		$user 		= $r->session()->get('user_id');
		$tgl 		= date('Y-m-d H:i:s');

		if($flag_aktif == null) $flag_aktif = 0;

		if($act == "add"){
			$insert_dt = MUser::insert_dt($user_id, $nama, $password, $deskripsi, $flag_aktif, $user, $tgl);
		}else{
			if($password == "123456789abcefghij"){
				$update_dt = MUser::update_dt($user_id, $nama, $deskripsi, $flag_aktif, $user, $tgl);
			}else{
				$update_dt = MUser::update_dt_with_password($user_id, $nama, $password, $deskripsi, $flag_aktif, $user, $tgl);
			}
		}

		$role_id 	= $r->add_role_id;
		if(is_array($role_id)){
			$total = count($role_id);

			for($a = 0; $a < $total; $a++){
				$insert_role = MUser::insert_role($user_id, $role_id[$a], $user, $tgl);
			}
		}

		$unit	= $r->add_unit;
		if(is_array($unit)){
			$total = count($unit);

			for($a = 0; $a < $total; $a++){
				$exp = explode("|", $unit[$a]);
				$kd_unit 	= $exp[0];
				$kd_lokasi 	= $exp[1];

				$exist_unit = MUser::exist_unit($user_id, $kd_unit);
				if(count($exist_unit) == 0){
					$insert_unit = MUser::insert_unit($user_id, $kd_unit, $user, $tgl);
				}
				
				$insert_lokasi = MUser::insert_lokasi($user_id, $kd_unit, $kd_lokasi, $user, $tgl);
			}
		}
	}

	function show_role(Request $r)
	{
		$user_id 	= $r->user_id;

		$q = MUser::show_role($user_id);

		return $q;
	}

	function delete_role(Request $r)
	{
		$user_role_id = $r->user_role_id;

		$q = MUser::delete_role($user_role_id);
	}

	function show_unit(Request $r)
	{
		$user_id 	= $r->user_id;

		$q = MUser::show_unit($user_id);

		return $q;
	}

	function delete_unit(Request $r)
	{
		$lokasi_role_id = $r->lokasi_role_id;
		$user_id 		= $r->user_id;
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		
		$q = MUser::delete_lokasi($lokasi_role_id);

		$exist_lokasi = MUser::exist_lokasi($user_id, $kd_unit);
		if(count($exist_lokasi) == 0){
			$q = MUser::delete_unit($user_id, $kd_unit);
		}
	}
}