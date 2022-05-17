<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Budget\CMaster;

use App\Models\Vouchermall\M_DashboardFa;

class cDashboardFa extends Controller
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

		$data_voucher1 	= M_DashboardFa::get_voucher1();
		foreach ($data_voucher1 as $data_voucher1_row) {
			$ONHAND_STOCK 	= $data_voucher1_row->ONHAND_STOCK;
			$ONHAND_NOMINAL = $data_voucher1_row->ONHAND_NOMINAL;
        }

		$data_voucher2 	= M_DashboardFa::get_voucher2();
		foreach ($data_voucher2 as $data_voucher2_row) {
			$ONHAND_STOCK2 		= $data_voucher2_row->ONHAND_STOCK;
			$ONHAND_NOMINAL2 	= $data_voucher2_row->ONHAND_NOMINAL;
        }

		$data_voucher3 	= M_DashboardFa::get_voucher3();
		foreach ($data_voucher3 as $data_voucher3_row) {
			$ONHAND_STOCK3 		= $data_voucher3_row->ONHAND_STOCK;
			$ONHAND_NOMINAL3 	= $data_voucher3_row->ONHAND_NOMINAL;
        }

		$data_total 	= M_DashboardFa::get_total_voucher();
		foreach ($data_total as $data_total_row) {
			$TOT_JML 	= $data_total_row->TOT_JML;
			$TOT_NOM 	= $data_total_row->TOT_NOM;
        }

    	$dt = array(
    		'button' 			=> $button,
    		'data_user'			=> $data_user,
			'jml_v1'			=> $ONHAND_STOCK,
			'nom_v1'      		=> $ONHAND_NOMINAL,
			'jml_v2'			=> $ONHAND_STOCK2,
			'nom_v2'      		=> $ONHAND_NOMINAL2,
			'jml_v3'			=> $ONHAND_STOCK3,
			'nom_v3'      		=> $ONHAND_NOMINAL3,
			'tot_jmlvoucher'	=> $TOT_JML,
			'tot_voucher'      	=> $TOT_NOM,
    	);

    	return view('vouchermall.V_DashboardFa')->with('dt', $dt);
    }

	public function search_dt(Request $r)
	{
		
		$keyword    = $r->keyword;
		$user   	= $r->session()->get('user_id');

		$q = M_DashboardFa::search_dt($keyword);

		return response()->json($q);
	}

    public function get_voucher(Request $r)
	{		
		$kd_unit    = $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;

		$q = M_DashboardFa::get_voucher($kd_unit,$kd_lokasi);

		return response()->json($q);
	}
}
