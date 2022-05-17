<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Vouchermall\cSysVoucher;

use App\Models\Vouchermall\M_PengajuanVoucher;

class cPengajuanVoucher extends Controller
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

    	return view('vouchermall.V_PengajuanVoucher')->with('dt', $dt);
    }

	public function search_dt(Request $r)
	{
		
		$keyword    = $r->keyword;
		$kd_unit	= $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$user   	= $r->session()->get('user_id');

		$q = M_PengajuanVoucher::search_dt($keyword,$kd_unit,$kd_lokasi,$user);

		return response()->json($q);
	}

	public function search_data_voucher(Request $r)
	{
		$kd_unit	= $r->kd_unit;
		$user   	= $r->session()->get('user_id');

		$q = $this->master->search_data_voucher($kd_unit); 

		return response()->json($q);
	}

	public function get_voucher(Request $r)
	{
		$kd_unit    = $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$kode		= $r->kode;

		$q = M_PengajuanVoucher::get_voucher($kd_unit,$kd_lokasi,$kode);

		return $q;
	}
	
	public function save(Request $r)
	{
		$act 				= $r->act;
		$kd_unit 			= $r->kd_unit;
		$kd_lokasi 			= $r->kd_lokasi;
		$no_pengajuan		= $r->no_pengajuan;
		$status_approval	= $r->status_approval;
		$tgl_pengajuan_db	= $r->tgl_pengajuan;
		$tgl_pengajuan		= $this->sysController->date_db($tgl_pengajuan_db);
		$kd_departemen		= $r->kd_departemen;
		$keperluan			= $r->keperluan;
		$tgl_butuh_db		= $r->tgl_butuh;
		$tgl_butuh			= $this->sysController->date_db($tgl_butuh_db);
		$jumlahheader		= $r->jumlahheader;
		$tot_voucher		= $r->tot_voucher;
		
		$user 			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		if($act == "add"){
			$q = M_PengajuanVoucher::insert_dt($kd_unit, $kd_lokasi, $no_pengajuan, $status_approval, $tgl_pengajuan, $kd_departemen, $keperluan, $tgl_butuh, $jumlahheader, $tot_voucher, $user, $tgl);
			$no_pengajuan = $q;
			echo $no_pengajuan;
		}else{
			$q = M_PengajuanVoucher::update_dt($kd_unit, $kd_lokasi, $no_pengajuan, $status_approval, $tgl_pengajuan, $kd_departemen, $keperluan, $tgl_butuh, $jumlahheader, $tot_voucher, $user, $tgl);
		}

		/* === Detail === */
		$add_kd_voucher 		= $r->add_kd_voucher;
		$add_kd_barcode 		= $r->add_kd_barcode;
		$add_qty 				= $r->add_qty;
		$add_nominal_voucher 	= $r->add_nominal_voucher;
		$add_no_baris			= $r->add_no_baris;

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
				if(trim($no_pengajuan) != ""){
					$isExist = M_PengajuanVoucher::cekExist($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher[$i], $add_no_baris[$i]);
					if ($isExist) {
						$update_dtl = M_PengajuanVoucher::update_dtl($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}else{
						$insert_dtl = M_PengajuanVoucher::insert_dtl($kd_unit, $kd_lokasi, $no_pengajuan, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}
				}
			}
		}
	}

	public function show_dtl(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_pengajuan 	= $r->no_pengajuan;

		$q = M_PengajuanVoucher::show_detail($kd_unit,$kd_lokasi,$no_pengajuan);
		return $q;
	}

	public function delete_detail(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_pengajuan 	= $r->no_pengajuan;
		$rowiddtl  		= $r->rowiddtl;
		
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_PengajuanVoucher::delete_detail($kd_unit,$kd_lokasi,$no_pengajuan,$rowiddtl,$user,$tgl);

		return response()->json($q);
	}

	public function delete_dt(Request $r)
	{
		$rowid  = $r->rowid;
		$user  	= $r->session()->get('user_id');

		$q = M_PengajuanVoucher::delete_dt($rowid,$user);

		return response()->json($q);
	}
}
