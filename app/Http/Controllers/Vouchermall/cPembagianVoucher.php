<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Vouchermall\cSysVoucher;

use App\Models\Vouchermall\M_PembagianVoucher;

class cPembagianVoucher extends Controller
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
		$data_carabayar = $this->master->get_cara_bayar();

    	$dt = array(
    		'button' 			=> $button,
    		'data_user'			=> $data_user,
			'data_voucher'		=> $data_voucher,
			'data_carabayar'	=> $data_carabayar
    	);

    	return view('vouchermall.V_PembagianVoucher')->with('dt', $dt);
    }

	public function search_dt(Request $r)
	{
		
		$keyword    = $r->keyword;
		$kd_unit	= $r->kd_unit;
		$kd_lokasi  = $r->kd_lokasi;
		$user   	= $r->session()->get('user_id');

		$q = M_PembagianVoucher::search_dt($keyword,$kd_unit,$kd_lokasi,$user);

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

		$q = M_PembagianVoucher::get_voucher($kd_unit,$kd_lokasi,$kode);

		return $q;
	}

	public function get_data_onhand(Request $r)
	{
		$kd_unit    		= $r->kd_unit;
		$kd_lokasi  		= $r->kd_lokasi;
		$kd_departemen		= $r->kd_departemen;
		$tgl_pembagian_db	= $r->tgl_pembagian;
		$tgl_pembagian		= $this->sysController->date_ym($tgl_pembagian_db);
		$voucher_kode		= $r->voucher_kode;

		$q = M_PembagianVoucher::get_data_onhand($kd_unit,$kd_lokasi,$kd_departemen,$tgl_pembagian,$voucher_kode);

		return $q;
	}
	
	public function save(Request $r)
	{
		$act 				= $r->act;
		$kd_unit 			= $r->kd_unit;
		$kd_lokasi 			= $r->kd_lokasi;
		$no_dokumen			= $r->no_dokumen;
		$tgl_pembagian_db	= $r->tgl_pembagian;
		$tgl_pembagian		= $this->sysController->date_db($tgl_pembagian_db);
		$kd_departemen		= $r->kd_departemen;
		$penerima			= $r->penerima;
		$keterangan			= $r->keterangan;
		$jumlahheader		= $r->jumlahheader;
		$jml_onhand			= $r->jml_onhand;
		$tot_voucher		= $r->tot_voucher;

		$user 			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		if($act == "add"){
			$q = M_PembagianVoucher::insert_dt($kd_unit, $kd_lokasi, $no_dokumen, $tgl_pembagian, $kd_departemen, $penerima, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl);
			$no_dokumen = $q;
			echo $no_dokumen;
		}else{
			if($jumlahheader >  $jml_onhand){
				echo 'Jumlah Pembagian Voucher, Melebihi Stok Vaoucher yg ada';	
				return false;			
			}else{
				$q = M_PembagianVoucher::update_dt($kd_unit, $kd_lokasi, $no_dokumen, $tgl_pembagian, $kd_departemen, $penerima, $keterangan, $jumlahheader, $tot_voucher, $user, $tgl);
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
		$jumlah 				= $r->jumlah;
		$nominal 				= $r->nominal;
		$total					= $r->total;

		if($add_kd_voucher != null){
			$add_total 	= count($add_kd_voucher);

			for ($i=0; $i < $add_total; $i++) { 
				if(trim($no_dokumen) != ""){
					$isExist = M_PembagianVoucher::cekExist($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher[$i], $add_no_baris[$i]);
					if ($isExist) {
						$update_dtl = M_PembagianVoucher::update_dtl($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}else{
						$insert_dtl = M_PembagianVoucher::insert_dtl($kd_unit, $kd_lokasi, $no_dokumen, $add_kd_voucher[$i], $add_kd_barcode[$i], $add_no_baris[$i], $add_qty[$i], $add_nominal_voucher[$i], $jumlah, $nominal, $total, $user, $tgl);
					}
				}
			}
		}
	}

	public function show_dtl(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_dokumen 	= $r->no_dokumen;

		$q = M_PembagianVoucher::show_detail($kd_unit,$kd_lokasi,$no_dokumen);
		return $q;
	}

	public function delete_detail(Request $r)
	{
		$kd_unit 		= $r->kd_unit;
		$kd_lokasi 		= $r->kd_lokasi;
		$no_dokumen 	= $r->no_dokumen;
		$rowiddtl  		= $r->rowiddtl;
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_PembagianVoucher::delete_detail($kd_unit,$kd_lokasi,$no_dokumen,$rowiddtl,$user,$tgl);

		return response()->json($q);
	}

	public function delete_dt(Request $r)
	{
		$no_dokumen  	= $r->no_dokumen;
		$user  			= $r->session()->get('user_id');
		$tgl 			= date('Y-m-d H:i:s');

		$q = M_PembagianVoucher::delete_dt($no_dokumen,$user,$tgl);

		return response()->json($q);
	}

	public function closing(Request $r)
    {
		$kd_unit    	= $r->kd_unit;
		$kd_lokasi  	= $r->kd_lokasi;
		$no_dokumen		= $r->no_dokumen;
        $user           = $r->session()->get('user_id');
        $tgl            = date('Y-m-d H:i:s');

        $q = M_PembagianVoucher::closing($kd_unit, $kd_lokasi, $no_dokumen, $user, $tgl);
    }

}