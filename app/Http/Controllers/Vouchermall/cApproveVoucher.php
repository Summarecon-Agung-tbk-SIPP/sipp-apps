<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Sys\SysController;
use App\Http\Controllers\Vouchermall\cSysVoucher;

use App\Models\Vouchermall\M_ApproveVoucher;

class cApproveVoucher extends Controller
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
		$button 		= $this->sysController->get_button($r);
    	$data_user 		= $this->master->get_data_user($user_id);
		$data_voucher 	= $this->master->get_data_voucher($r);

		$dt = array(
			'button' 		=> $button,
			'data_user'		=> $data_user,
			'data_voucher'	=> $data_voucher
		);

		return view('vouchermall.V_ApproveVoucher')->with('dt', $dt);
	}

	public function show_kabag(Request $r)
	{
		$q = self::show_pengajuan($r, 'E');

		return $q;
	}

	public function show_approved(Request $r)
	{
		$q = self::show_pengajuan($r, 'L');

		return $q;
	}

	public function show_canceled(Request $r)
	{
		$q = self::show_pengajuan($r, 'C');

		return $q;
	}

	public function show_pengajuan($r, $status_approval)
	{
		$user        	= $r->session()->get('user_id');

		$list_staff = [];
		$get_staff 	= $this->master->get_staff($user);
		foreach ($get_staff as $get_staff_row) {
			$no_induk = $get_staff_row->NO_INDUK;
			array_push($list_staff, $get_staff_row->NO_INDUK);
		}

		$q = M_ApproveVoucher::show_pengajuan($list_staff, $status_approval);
		foreach ($q as $row) {

			$no_pengajuan 	= $row->NO_PERMINTAAN;
			$nik_pemohon 	= $row->USER_PEMOHON;
			$get_data_user 	= $this->master->get_data_user($nik_pemohon);
			foreach ($get_data_user as $get_data_user_row) {
				$nm_pemohon = $get_data_user_row->NAMA;
			}

			$kd_bagian 		= $row->KD_BAGIAN;
			$nm_departemen 	= '';
			$get_nm_departemen = $this->master->get_nm_departemen($kd_bagian);
			foreach ($get_nm_departemen as $get_nm_departemen_row) {
				$nm_departemen = $get_nm_departemen_row->NM_DEPARTEMEN;
			}

			$kd_unit 		= $row->KD_PERUSAHAAN;
			$nm_unit 		= '';
			$get_nm_unit = $this->master->get_nm_unit($kd_unit);
			foreach ($get_nm_unit as $get_nm_unit_row) {
				$nm_unit = $get_nm_unit_row->NM_UNIT;
			}

			$tgl_pengajuan 	= $row->TGL_PERMINTAAN;
			$tgl_butuh 		= $row->TGL_BUTUH;
			$keperluan 		= $row->KEPERLUAN;
			$tot_jml 		= $row->TOTAL_JML_VOUCHER;
			$tot_nominal 	= $row->TOTAL_VOUCHER;
			$status 		= $row->STATUS;
			$nm_status 		= $row->TITLE_STATUS;
			$rowid 			= $row->ROWID;

			$data[] = array(
				'no_pengajuan'	=> $no_pengajuan,
				'tgl_pengajuan'	=> $tgl_pengajuan,
				'tgl_butuh'		=> $tgl_butuh,
				'nik_pemohon'	=> $nik_pemohon,
				'nm_pemohon'	=> $nm_pemohon,
				'kd_bagian'		=> $kd_bagian,
				'nm_bagian'		=> $nm_departemen,
				'keperluan'		=> $keperluan,
				'tot_jml'		=> $tot_jml,
				'tot_nominal'	=> $tot_nominal,
				'status'		=> $status,
				'nm_status'		=> $nm_status,
				'kd_unit'		=> $kd_unit,
				'nm_unit'		=> $nm_unit,
				'rowid'			=> $rowid,
			);
		}

		if(isset($data)){
			return $data;
		}
	}

	public function show_detail(Request $r)
	{
		$no_pengajuan 	= $r->id;
		$kd_unit		= $r->kd_unit;
		$kd_lokasi  	= $r->kd_lokasi;

		$q = M_ApproveVoucher::show_detail($kd_unit,$kd_lokasi,$no_pengajuan);

		return $q;
	}

	public function save(Request $r)
	{
		$no_pengajuan 		= $r->no_pengajuan;
		$status_approval 	= $r->status_approval;

		$user        		= $r->session()->get('user_id');
		$tgl 				= date('Y-m-d H:i:s');

		if($no_pengajuan != null){
			$total = count($no_pengajuan);

			for($i = 0; $i < $total; $i++){
				$no_pengajuan_=$no_pengajuan[$i];
				if(trim($no_pengajuan_) != ""){
					$q = M_ApproveVoucher::approve_kabag($no_pengajuan_, $status_approval[$no_pengajuan_], $user, $tgl);
				}
			}
		}
	}
}