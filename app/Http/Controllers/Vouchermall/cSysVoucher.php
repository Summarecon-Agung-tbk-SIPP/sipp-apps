<?php

namespace App\Http\Controllers\Vouchermall;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\Vouchermall\M_SysVoucher;

class cSysVoucher extends Controller
{
	public function get_data_user($user_id)
	{
		$q = M_SysVoucher::get_data_user($user_id);

		return $q;
	}

	public function get_data_voucher(Request $r)
	{
		$kd_unit 	= $r->session()->get('kd_unit');

		$q = M_SysVoucher::get_data_voucher($kd_unit);

		return $q;
	}

	public function get_voucher($kd_unit,$kd_lokasi,$kode)
	{
		$q = M_SysVoucher::get_voucher($kd_unit,$kd_lokasi,$kode);

		return $q;
	}

	public function get_staff($user_id)
	{
		$q = M_SysVoucher::get_staff($user_id);

		return $q;
	}

	public function get_nm_unit($kd_unit)
	{
		$q = M_SysVoucher::get_nm_unit($kd_unit);

		return $q;
	}

	public function get_nm_departemen($kd_departemen)
	{
		$q = M_SysVoucher::get_nm_departemen($kd_departemen);

		return $q;
	}

	public function get_cara_bayar()
	{
		$q = M_SysVoucher::get_cara_bayar();

		return $q;
	}

	public function search_data_voucher($kd_unit)
	{
		$q = M_SysVoucher::search_data_voucher($kd_unit);

		return $q;
	}

	public function search_tenant($kd_unit,$kd_lokasi)
	{
		$q = M_SysVoucher::search_tenant($kd_unit,$kd_lokasi);

		return $q;
	}
}