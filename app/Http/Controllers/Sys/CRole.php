<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Sys\CMaster;

use App\Models\Sys\MRole;

class CRole extends Controller
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
		$list_modul 	= $this->master->list_modul();

		$dt = array(
			'button' 		=> $button,
			'list_modul'	=> $list_modul
		);

		return view('sys.vRole')->with('dt', $dt);
	}

	public function search_dt(Request $r)
	{
		$keyword = $r->keyword;

		$q = MRole::search_dt($keyword);

		return $q;
	}

	public function save(Request $r)
	{
		$role_id 	= $r->role_id;
		$nama 		= $r->nama;
		$modul_id 	= $r->add_modul_id;
		$akses 		= $r->add_akses;
		$act 		= $r->act;
		$user 		= $r->session()->get('user_id');
		$tgl 		= date('Y-m-d H:i:s');

		if($act == "add"){
			$role_id = MRole::insert_dt($nama);
		}

		if(is_array($modul_id)){
			$total = count($modul_id);

			$m_save = 0;
			$m_delete = 0;
			for($a = 0; $a < $total; $a++){
				if($akses[$a] == "F"){
					$m_save = 1;
					$m_delete = 1;
				}
				
				if($akses[$a] == "S"){
					$m_save = 1;
				}
				
				if($akses[$a] == "D"){
					$m_delete = 1;
				}

				$insert_modul = MRole::insert_modul($role_id, $modul_id[$a], $m_save, $m_delete, $user, $tgl);
			}
		}

		if($act == "add"){
			return $role_id;
		}
	}

	function show_modul(Request $r)
	{
		$role_id 	= $r->role_id;

		$q = MRole::show_modul($role_id);

		return $q;
	}

	function delete_modul(Request $r)
	{
		$role_priv_id = $r->role_priv_id;

		$q = MRole::delete_modul($role_priv_id);
	}
}