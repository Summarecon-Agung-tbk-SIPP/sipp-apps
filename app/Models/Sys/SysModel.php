<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\Model;
use DB;

class SysModel extends Model
{
	public static function get_parent($user_id)
	{
		$q = DB::table('t_modul as a')
		->select('a.modul_id', 'a.nama_modul', 'a.modulorder', 'a.controller')
		->join('t_role_privilege as b', 'b.modul_id', '=', 'a.modul_id')
		->join('t_user_role as c', function($join) {
			$join->on('c.role_id', '=', 'b.role_id');
		})
		->where('a.parent_id', '=', '0')
		// ->where('a.flag_aktif', '=', '1')
		// ->where(db::raw("isnull(a.flag_new_portal, 'N')"), '=', 'Y')
		->where('c.kode_user', '=', $user_id)
		->groupBy('a.modul_id', 'a.nama_modul', 'a.modulorder', 'a.controller')
		->orderBy('a.nama_modul', 'asc')
		->get();

		return $q;
	}

	public static function get_child($parent_id, $user_id)
	{
		$q = DB::table('t_modul as a')
		->select('a.modul_id', 'a.nama_modul', 'a.modulorder', 'a.controller')
		->join('t_role_privilege as b', 'b.modul_id', '=', 'a.modul_id')
		->join('t_user_role as c', function($join) {
			$join->on('c.role_id', '=', 'b.role_id');
		})
		->where(db::raw("cast(a.parent_id as varchar(50))"), '=', $parent_id)
		->where('a.flag_aktif', '=', '1')
		->where('c.kode_user', '=', $user_id)
		->groupBy('a.modul_id', 'a.nama_modul', 'a.modulorder', 'a.controller')
		->orderBy('a.modulorder', 'asc')
		->orderBy('a.nama_modul', 'asc')
		->get();

		return $q;
	}

	public static function hak_akses($user_id, $controller)
	{
		$q = DB::table('t_user_role as a')
		->select('b.m_insert', 'b.m_save', 'b.m_delete')
		->join('t_role_privilege as b', 'b.role_id', '=', 'a.role_id')
		->join('t_modul as c', 'c.modul_id', '=', 'b.modul_id')
		->where('a.kode_user', '=', $user_id)
		->where('c.controller', '=', $controller)
		->groupBy('b.m_insert', 'b.m_delete', 'b.m_save')
		->get();

		return $q;
	}

	public static function title($controller)
	{
		$q = DB::table('t_modul as a')
		->select('a.nama_modul')
		->where('a.controller', '=', $controller)
		->limit(1)
		->get();

		return $q;
	}

	public static function set_first_unit($user_id)
	{
		$q = DB::table('t_unit as a')
		->select('a.unit_id as kd_unit', 'a.nama as nm_unit', 'd.id_proyek as kd_lokasi', 'd.nm_proyek as nm_lokasi')
		->join('t_unit_role_new as b', 'b.unit_id', '=', 'a.unit_id')
		->join('t_lokasi_role as c', function($join) {
			$join->on('c.unit_id', '=', 'b.unit_id');
			$join->on('c.kode_user', '=', 'b.kode_user');
		})
		->join('t_proyek as d', function($join){
			$join->on('d.unit_id', '=', 'c.unit_id');
			$join->on('d.id_proyek', '=', 'c.kd_lokasi');
		})
		->where('a.flag_aktif', '=', 1)
		->where('b.kode_user', '=', $user_id)
		->groupBy('a.unit_id', 'a.nama', 'd.id_proyek', 'd.nm_proyek')
		->orderBy('a.unit_id', 'asc')
		->limit(1)
		->get();

		return $q;
	}

	public static function search_unit($user_id, $keyword)
	{
		$q = DB::table('t_unit as a')
		->select('a.unit_id as kd_unit', 'a.nama as nm_unit', 'd.id_proyek as kd_lokasi', 'd.nm_proyek as nm_lokasi')
		->join('t_unit_role_new as b', 'b.unit_id', '=', 'a.unit_id')
		->join('t_lokasi_role as c', function($join) {
			$join->on('c.unit_id', '=', 'b.unit_id');
			$join->on('c.kode_user', '=', 'b.kode_user');
		})
		->join('t_proyek as d', function($join){
			$join->on('d.unit_id', '=', 'c.unit_id');
			$join->on('d.id_proyek', '=', 'c.kd_lokasi');
		})
		->where('a.flag_aktif', '=', 1)
		->where('b.kode_user', '=', $user_id)
		->where(function($wh) use ($keyword){
			$wh->where('a.unit_id', 'like', '%'.$keyword.'%')
			->orWhere('a.nama', 'like', '%'.$keyword.'%')
			->orWhere('d.id_proyek', 'like', '%'.$keyword.'%')
			->orWhere('d.nm_proyek', 'like', '%'.$keyword.'%');
		})
		->groupBy('a.unit_id', 'a.nama', 'd.id_proyek', 'd.nm_proyek')
		->orderBy('a.unit_id', 'asc')
		->paginate(10);
		// ->get();

		return $q;
	}

	public static function signin_process($user_id, $password)
	{
		$q = DB::table('t_user')
		->select('nama')
		->where('kode_user', $user_id)
		->where('password', $password)
		->where('flag_aktif', 1)
		->get();

		return $q;
	}
}