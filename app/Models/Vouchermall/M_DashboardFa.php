<?php

namespace App\Models\Vouchermall;

use Illuminate\Database\Eloquent\Model;
use DB;

class M_DashboardFa extends Model
{
    public static function get_voucher1()
    {
        $q = DB::select("
            SELECT 
                ONHAND_STOCK    = ISNULL(V_TRX_INV_RKP.ONHAND_STOCK,0),
                ONHAND_NOMINAL	= ISNULL(V_TRX_INV_RKP.JMLNOM_STOCK,0)
            FROM VCH_MST_VOUCHER A
            LEFT OUTER JOIN (  
                SELECT 
                    XA.KD_PERUSAHAAN,
                    XA.KD_VOUCHER,
                    XA.KD_BAGIAN,
                    XA.YYYYMM,
                    XA.FG_TIPE,
                    ONHAND_STOCK = ( ISNULL(XA.BG_JML,0) + ISNULL(XA.IN_JML,0) + ISNULL(XA.AI_JML,0) - ISNULL(XA.OT_JML,0) - ISNULL(XA.MO_JML,0) - ISNULL(XA.AO_JML,0) -  ISNULL(XA.GC_JML,0) + ISNULL(XA.MC_JML,0)),
                    JMLNOM_STOCK = ( ISNULL(XA.BG_NOM,0) + ISNULL(XA.IN_NOM,0) + ISNULL(XA.AI_NOM,0) - ISNULL(XA.OT_NOM,0) - ISNULL(XA.MO_NOM,0) - ISNULL(XA.AO_NOM,0) - ISNULL(XA.GC_NOM,0) + ISNULL(XA.MC_NOM,0))
                FROM VCH_TRX_REKAP  XA
            ) V_TRX_INV_RKP ON 
                V_TRX_INV_RKP.KD_PERUSAHAAN		= A.KD_PERUSAHAAN
                AND V_TRX_INV_RKP.KD_VOUCHER	= A.KD_VOUCHER
                AND V_TRX_INV_RKP.YYYYMM 		= '202203'
                AND V_TRX_INV_RKP.FG_TIPE		= 'INFA'
            WHERE 1=1
                AND A.KD_PERUSAHAAN			= 'SMKG'
                AND V_TRX_INV_RKP.KD_BAGIAN	= '1041'
                AND A.KD_VOUCHER			= 'V0001_25000'
                AND A.FLAG_AKTIF			= 'Y' 
        ");

        return $q;
    }

    public static function get_voucher2()
    {
        $q = DB::select("
            SELECT 
                ONHAND_STOCK    = ISNULL(V_TRX_INV_RKP.ONHAND_STOCK,0),
                ONHAND_NOMINAL	= ISNULL(V_TRX_INV_RKP.JMLNOM_STOCK,0)
            FROM VCH_MST_VOUCHER A
            LEFT OUTER JOIN (  
                SELECT 
                    XA.KD_PERUSAHAAN,
                    XA.KD_VOUCHER,
                    XA.KD_BAGIAN,
                    XA.YYYYMM,
                    XA.FG_TIPE,
                    ONHAND_STOCK = ( ISNULL(XA.BG_JML,0) + ISNULL(XA.IN_JML,0) + ISNULL(XA.AI_JML,0) - ISNULL(XA.OT_JML,0) - ISNULL(XA.MO_JML,0) - ISNULL(XA.AO_JML,0) -  ISNULL(XA.GC_JML,0) + ISNULL(XA.MC_JML,0)),
                    JMLNOM_STOCK = ( ISNULL(XA.BG_NOM,0) + ISNULL(XA.IN_NOM,0) + ISNULL(XA.AI_NOM,0) - ISNULL(XA.OT_NOM,0) - ISNULL(XA.MO_NOM,0) - ISNULL(XA.AO_NOM,0) - ISNULL(XA.GC_NOM,0) + ISNULL(XA.MC_NOM,0))
                FROM VCH_TRX_REKAP  XA
            ) V_TRX_INV_RKP ON 
                V_TRX_INV_RKP.KD_PERUSAHAAN		= A.KD_PERUSAHAAN
                AND V_TRX_INV_RKP.KD_VOUCHER	= A.KD_VOUCHER
                AND V_TRX_INV_RKP.YYYYMM 		= '202203'
                AND V_TRX_INV_RKP.FG_TIPE		= 'INFA'
            WHERE 1=1
                AND A.KD_PERUSAHAAN			= 'SMKG'
                AND V_TRX_INV_RKP.KD_BAGIAN	= '1041'
                AND A.KD_VOUCHER			= 'V0002_50000'
                AND A.FLAG_AKTIF			= 'Y' 
        ");

        return $q;
    }

    public static function get_voucher3()
    {
        $q = DB::select("
            SELECT 
                ONHAND_STOCK    = ISNULL(V_TRX_INV_RKP.ONHAND_STOCK,0),
                ONHAND_NOMINAL	= ISNULL(V_TRX_INV_RKP.JMLNOM_STOCK,0)
            FROM VCH_MST_VOUCHER A
            LEFT OUTER JOIN (  
                SELECT 
                    XA.KD_PERUSAHAAN,
                    XA.KD_VOUCHER,
                    XA.KD_BAGIAN,
                    XA.YYYYMM,
                    XA.FG_TIPE,
                    ONHAND_STOCK = ( ISNULL(XA.BG_JML,0) + ISNULL(XA.IN_JML,0) + ISNULL(XA.AI_JML,0) - ISNULL(XA.OT_JML,0) - ISNULL(XA.MO_JML,0) - ISNULL(XA.AO_JML,0) -  ISNULL(XA.GC_JML,0) + ISNULL(XA.MC_JML,0)),
                    JMLNOM_STOCK = ( ISNULL(XA.BG_NOM,0) + ISNULL(XA.IN_NOM,0) + ISNULL(XA.AI_NOM,0) - ISNULL(XA.OT_NOM,0) - ISNULL(XA.MO_NOM,0) - ISNULL(XA.AO_NOM,0) - ISNULL(XA.GC_NOM,0) + ISNULL(XA.MC_NOM,0))
                FROM VCH_TRX_REKAP  XA
            ) V_TRX_INV_RKP ON 
                V_TRX_INV_RKP.KD_PERUSAHAAN		= A.KD_PERUSAHAAN
                AND V_TRX_INV_RKP.KD_VOUCHER	= A.KD_VOUCHER
                AND V_TRX_INV_RKP.YYYYMM 		= '202203'
                AND V_TRX_INV_RKP.FG_TIPE		= 'INFA'
            WHERE 1=1
                AND A.KD_PERUSAHAAN			= 'SMKG'
                AND V_TRX_INV_RKP.KD_BAGIAN	= '1041'
                AND A.KD_VOUCHER			= 'V0003_100000'
                AND A.FLAG_AKTIF			= 'Y' 
        ");

        return $q;
    }

    public static function get_total_voucher()
    {
        $q = DB::select("
            SELECT
                SUM(ONHAND_STOCK) AS TOT_JML,
                SUM(ONHAND_NOMINAL) AS TOT_NOM
            FROM 
            (
                SELECT 
                    ONHAND_STOCK    = ISNULL(V_TRX_INV_RKP.ONHAND_STOCK,0),
                    ONHAND_NOMINAL	= ISNULL(V_TRX_INV_RKP.JMLNOM_STOCK,0)
                FROM VCH_MST_VOUCHER A
                LEFT OUTER JOIN (  
                    SELECT 
                        XA.KD_PERUSAHAAN,
                        XA.KD_VOUCHER,
                        XA.KD_BAGIAN,
                        XA.YYYYMM,
                        XA.FG_TIPE,
                        ONHAND_STOCK = ( ISNULL(XA.BG_JML,0) + ISNULL(XA.IN_JML,0) + ISNULL(XA.AI_JML,0) - ISNULL(XA.OT_JML,0) - ISNULL(XA.MO_JML,0) - ISNULL(XA.AO_JML,0) -  ISNULL(XA.GC_JML,0) + ISNULL(XA.MC_JML,0)),
                        JMLNOM_STOCK = ( ISNULL(XA.BG_NOM,0) + ISNULL(XA.IN_NOM,0) + ISNULL(XA.AI_NOM,0) - ISNULL(XA.OT_NOM,0) - ISNULL(XA.MO_NOM,0) - ISNULL(XA.AO_NOM,0) - ISNULL(XA.GC_NOM,0) + ISNULL(XA.MC_NOM,0))
                    FROM VCH_TRX_REKAP  XA
                ) V_TRX_INV_RKP ON 
                    V_TRX_INV_RKP.KD_PERUSAHAAN		= A.KD_PERUSAHAAN
                    AND V_TRX_INV_RKP.KD_VOUCHER	= A.KD_VOUCHER
                    AND V_TRX_INV_RKP.YYYYMM 		= '202203'
                    AND V_TRX_INV_RKP.FG_TIPE		= 'INFA'
                WHERE 1=1
                    AND A.KD_PERUSAHAAN			= 'SMKG'
                    AND V_TRX_INV_RKP.KD_BAGIAN	= '1041'
                    AND A.FLAG_AKTIF			= 'Y' 
            ) V_GROUP    
        ");

        return $q;
    }
}
