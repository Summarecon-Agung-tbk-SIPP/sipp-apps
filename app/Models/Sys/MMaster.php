<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\Model;
use DB;

class MMaster extends Model
{
	public static function list_modul()
	{
		$q = DB::table('t_modul as a')
		->select('a.modul_id', 'a.nama_modul', 'a.controller')
		->where('a.flag_aktif', '1')
		->orderBy('a.nama_modul')
		->get();

		return $q;
	}

    public static function list_role()
    {
        $q = DB::table('t_role as a')
        ->select('a.role_id', 'a.nama')
        ->get();

        return $q;
    }

    public static function list_unit()
    {
        $q = DB::table('t_lokasi_role as a')
        ->select('a.unit_id as kd_unit', 'b.nama as nm_unit', 'a.kd_lokasi', 'c.nm_proyek as nm_lokasi')
        ->join('t_unit as b', 'b.unit_id', '=', 'a.unit_id')
        ->join('t_proyek as c', function($join){
            $join->on('c.unit_id', 'b.unit_id');
            $join->on('c.id_proyek', 'a.kd_lokasi');
        })
        ->get();

        return $q;
    }

    public static function list_lokasi_all($kd_unit)
    {
        $q = DB::table('t_unit as a')
        ->select('a.unit_id as kd_unit', 'a.nama as nm_unit', 'b.id_proyek as kd_lokasi', 'b.nm_proyek as nm_lokasi')
        ->join('t_proyek as b', function($join){
            $join->on('b.unit_id', 'a.unit_id');
        })
        // ->where('a.unit_id', $kd_unit)
        ->get();

        return $q;
    }
}