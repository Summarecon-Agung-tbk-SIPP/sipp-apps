<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
// use App\Http\Controllers\Sys\CMaster;

use App\Models\Sys\MModul;

class CModul extends Controller
{
	private $sysController;
	private $master;

	public function __construct()
	{
		$this->sysController = new SysController();
		// $this->master = new CMaster();
	}

	public function index(Request $r)
	{
		$user_id 		= $r->session()->get('user_id');
		$button 		= $this->sysController->get_button($r);
		$list_parent 	= MModul::list_parent();

		$dt = array(
			'button' 		=> $button,
			'list_parent'	=> $list_parent
		);

		return view('sys.vModul')->with('dt', $dt);
	}

	public function search_dt(Request $r)
	{
		$keyword = $r->keyword;

		$q = MModul::search_dt($keyword);

		return $q;
	}

	public function save(Request $r)
	{
		$modul_id 	= $r->modul_id;
		$nama 		= $r->nama;
		$controller = $r->controller;
		$parent_id 	= $r->parent_id;
		$order 		= $r->order;
		$flag_aktif	= $r->flag_aktif;
		$act 		= $r->act;
		$nm_user 	= $r->session()->get('nama');

		if($flag_aktif == "") $flag_aktif = 0;

		if($act == "add"){
			$insert_dt = MModul::insert_dt($nama, $controller, $parent_id, $order, $flag_aktif, $nm_user);

			return $insert_dt;
		}else{
			$update_dt = MModul::update_dt($modul_id, $nama, $controller, $parent_id, $order, $flag_aktif);
		}
	}
}