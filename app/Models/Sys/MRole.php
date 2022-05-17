<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\Model;
use DB;

class MRole extends Model
{
	public static function search_dt($keyword)
	{
        $q = DB::table('t_role as a')
		->select('a.role_id', 'a.nama')
		->where('a.nama', 'like', '%'.$keyword.'%')
		->get();

		return $q;
	}

	public static function insert_dt($nama)
	{
		$q = DB::table('t_role')
		->insertGetId([
			'nama'	=> $nama
		]);

		return $q;
	}

	public static function insert_modul($role_id, $modul_id, $m_save, $m_delete, $user, $tgl)
	{
		$q = DB::table('t_role_privilege')
		->insert([
			'role_id'		=> $role_id,
			'modul_id'		=> $modul_id,
			'm_save'		=> $m_save,
			'm_delete'		=> $m_delete,
			'user_entry'	=> $user,
			'tgl_entry'		=> $tgl
		]);

		return $q;
	}

	public static function show_modul($role_id)
	{
		$q = DB::table('t_role_privilege as a')
		->select('a.role_priv_id', 'b.nama_modul', 'b.controller', 'a.m_save', 'a.m_delete')
		->join('t_modul as b', 'b.modul_id', 'a.modul_id')
		->where('a.role_id', $role_id)
		->get();

		return $q;
	}

	public static function delete_modul($role_priv_id)
	{
		$q = DB::table('t_role_privilege')
		->where('role_priv_id', $role_priv_id)
		->delete();

		return $q;
	}
}