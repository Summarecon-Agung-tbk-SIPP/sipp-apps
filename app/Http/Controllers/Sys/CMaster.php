<?php

namespace App\Http\Controllers\Sys;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\Sys\MMaster;

class CMaster extends Controller
{
	public function list_modul()
    {
        $q = MMaster::list_modul();

        return $q;
    }

    public function list_role()
    {
        $q = MMaster::list_role();

        return $q;
    }

    public function list_unit()
    {
        $q = MMaster::list_unit();

        return $q;
    }

    public function list_lokasi_all($r)
    {
        $kd_unit = $r->session()->get('kd_unit');

        $q = MMaster::list_lokasi_all($kd_unit);

        return $q;
    }
}