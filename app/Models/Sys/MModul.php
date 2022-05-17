<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\Model;
use DB;

class MModul extends Model
{
	public static function list_parent()
	{
		$q = DB::table('t_modul as a')
		->select('a.modul_id', 'a.nama_modul', 'a.controller')
		->where('a.controller', '#')
		->where('a.flag_aktif', '1')
		->get();

		return $q;
	}

	public static function search_dt($keyword)
	{
        $q = DB::table('t_modul as a')
		->select(
			'a.modul_id', 'a.nama_modul as nama', 'a.controller', 'a.parent_id', 'a.modulorder as order',
			'a.pembuat', 'a.flag_aktif'
		)
		->where(function($wh) use ($keyword){
			$wh->where('a.nama_modul', 'like', '%'.$keyword.'%')
			->orWhere('a.controller', 'like', '%'.$keyword.'%');
		})
		->get();

		return $q;
	}

	public static function insert_dt($nama, $controller, $parent_id, $order, $flag_aktif, $nm_user)
	{
		$q = DB::table('t_modul')
		->insertGetId([
			'nama_modul'	=> $nama,
			'controller'	=> $controller,
			'parent_id'		=> $parent_id,
			'modulorder'	=> $order,
			'flag_aktif'	=> $flag_aktif,
			'pembuat'		=> $nm_user
		]);

		return $q;
	}

	public static function update_dt($modul_id, $nama, $controller, $parent_id, $order, $flag_aktif)
	{
		$q = DB::table('t_modul')
		->where('modul_id', $modul_id)
		->update([
			'nama_modul'	=> $nama,
			'controller'	=> $controller,
			'parent_id'		=> $parent_id,
			'modulorder'	=> $order,
			'flag_aktif'	=> $flag_aktif
		]);

		return $q;
	}
}