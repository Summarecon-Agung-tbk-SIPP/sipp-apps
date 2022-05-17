<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\Model;
use DB;

class MUser extends Model
{
	public static function search_dt($keyword)
	{
        $q = DB::table('t_user as a')
		->select('a.kode_user as user_id', 'a.nama', 'a.deskripsi', 'a.flag_aktif')
		->where(function($wh) use ($keyword){
			$wh->where('a.kode_user', 'like', '%'.$keyword.'%')
			->orWhere('a.nama', 'like', '%'.$keyword.'%');
		})
		->get();

		return $q;
	}

	public static function insert_dt($user_id, $nama, $password, $deskripsi, $flag_aktif, $user, $tgl)
	{
		$q = DB::table('t_user')
		->insert([
			'kode_user'		=> $user_id,
			'nik'			=> $user_id,
			'nama'			=> $nama,
			'password'		=> md5($password),
			'deskripsi'		=> $deskripsi,
			'flag_aktif'	=> $flag_aktif,
			'user_entry'	=> $user,
			'tgl_entry'		=> $tgl,
			'user_update'	=> $user,
			'tgl_update'	=> $tgl
		]);

		return $q;
	}

	public static function update_dt($user_id, $nama, $deskripsi, $flag_aktif, $user, $tgl)
	{
		$q = DB::table('t_user')
		->where('kode_user', $user_id)
		->update([
			'deskripsi'		=> $deskripsi,
			'flag_aktif'	=> $flag_aktif,
			'user_update'	=> $user,
			'tgl_update'	=> $tgl
		]);

		return $q;
	}

	public static function update_dt_with_password($user_id, $nama, $password, $deskripsi, $flag_aktif, $user, $tgl)
	{
		$q = DB::table('t_user')
		->where('kode_user', $user_id)
		->update([
			'nama'			=> $nama,
			'password'		=> md5($password),
			'deskripsi'		=> $deskripsi,
			'flag_aktif'	=> $flag_aktif,
			'user_update'	=> $user,
			'tgl_update'	=> $tgl
		]);

		return $q;
	}

	public static function insert_role($user_id, $role_id, $user, $tgl)
	{
		$q = DB::table('t_user_role')
		->insert([
			'kode_user'		=> $user_id,
			'role_id'		=> $role_id,
			'user_entry'	=> $user,
			'tgl_entry'		=> $tgl
		]);

		return $q;
	}

	public static function show_role($user_id)
	{
		$q = DB::table('t_user_role as a')
		->select('a.user_role_id',  'a.role_id', 'b.nama')
		->join('t_role as b', 'b.role_id', 'a.role_id')
		->where('a.kode_user', $user_id)
		->get();

		return $q;
	}

	public static function delete_role($user_role_id)
	{
		$q = DB::table('t_user_role')
		->where('user_role_id', $user_role_id)
		->delete();

		return $q;
	}

	public static function exist_unit($user_id, $kd_unit)
	{
		$q = DB::table('t_unit_role_new')
		->select('unit_role_id')
		->where('kode_user', $user_id)
		->where('unit_id', $kd_unit)
		->get();

		return $q;
	}

	public static function insert_unit($user_id, $kd_unit, $user, $tgl)
	{
		$q = DB::table('t_unit_role_new')
		->insert([
			'unit_id'		=> $kd_unit,
			'kode_user'		=> $user_id,
			'user_entry'	=> $user,
			'tgl_entry'		=> $tgl
		]);

		return $q;
	}

	public static function insert_lokasi($user_id, $kd_unit, $kd_lokasi, $user, $tgl)
	{
		$q = DB::table('t_lokasi_role')
		->insert([
			'kode_user'		=> $user_id,
			'unit_id'		=> $kd_unit,
			'kd_lokasi'		=> $kd_lokasi,
			'user_entry'	=> $user,
			'tgl_entry'		=> $tgl
		]);

		return $q;
	}

	public static function show_unit($user_id)
	{
		$q = DB::table('t_lokasi_role as a')
		->select('a.lokasi_role_id', 'a.unit_id as kd_unit', 'b.nama as nm_unit', 'a.kd_lokasi', 'c.nm_proyek as nm_lokasi')
		->join('t_unit as b', 'b.unit_id', 'a.unit_id')
		->join('t_proyek as c', function($join){
			$join->on('c.unit_id', 'b.unit_id');
			$join->on('c.id_proyek', 'a.kd_lokasi');
		})
		->where('a.kode_user', $user_id)
		->get();

		return $q;
	}

	public static function delete_lokasi($lokasi_role_id)
	{
		$q = DB::table('t_lokasi_role')
		->where('lokasi_role_id', $lokasi_role_id)
		->delete();

		return $q;
	}

	public static function exist_lokasi($user_id, $kd_unit)
	{
		$q = DB::table('t_lokasi_role')
		->select('lokasi_role_id')
		->where('kode_user', $user_id)
		->where('unit_id', $kd_unit)
		->get();

		return $q;
	}

	public static function delete_unit($user_id, $kd_unit)
	{
		$q = DB::table('t_unit_role_new')
		->select('unit_role_id')
		->where('kode_user', $user_id)
		->where('unit_id', $kd_unit)
		->delete();

		return $q;
	}
}