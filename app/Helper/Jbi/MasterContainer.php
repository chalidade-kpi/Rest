<?php

namespace App\Helper\Jbi;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterContainer{

	public static function storeMasterContainer($input){
		$cont_no = $input['CONT_NO'];
		$check = DB::connection('mdm_ilcs')->table('TM_CONTAINER')->where('cont_no', $cont_no)->count();
		 if ($check > 0) {
			 DB::connection('mdm_ilcs')->table('TM_CONTAINER')->where('cont_no', $cont_no)->delete();
			 DB::connection('mdm_ilcs')->table('TM_CONTAINER')->insert([
 				'CONT_NO' => $input['CONT_NO'],
 				'CONT_SIZE' => $input['CONT_SIZE'],
 				'CONT_TYPE' => $input['CONT_TYPE'],
 				'CONT_DANGER' => $input['CONT_DANGER'],
 				'CONT_STATUS' => $input['CONT_STATUS'],
 				'COMMODITY' => $input['COMMODITY'],
 				'CARGO_OWNER' => $input['CARGO_OWNER'],
 			]);
 			return [ "success" => true, "result" => "Success, update data container", "cont_no"=> $cont_no ];
		} else {
			DB::connection('mdm_ilcs')->table('TM_CONTAINER')->insert([
				'CONT_NO' => $input['CONT_NO'],
				'CONT_SIZE' => $input['CONT_SIZE'],
				'CONT_TYPE' => $input['CONT_TYPE'],
				'CONT_DANGER' => $input['CONT_DANGER'],
				'CONT_STATUS' => $input['CONT_STATUS'],
				'COMMODITY' => $input['COMMODITY'],
				'CARGO_OWNER' => $input['CARGO_OWNER'],
			]);
			return [ "success" => true, "result" => "Success, simpan data container", "cont_no"=> $cont_no ];
	}

	}

	public static function storeMaintenanceTariff($input){

			$datenow = Carbon::now()->format('Y-m-d');

			if ($input['TARIFF_ID'] == '') {
				$id = uniqid();
				DB::connection('mdm_ilcs')->table('TM_MTC_TARIFF')->insert([
				 'TARIFF_ID' => $id,
				 'OBJECT_TARIFF' => $input['OBJECT_TARIFF'],
				 'OBJECT_NAME' => $input['OBJECT_NAME'],
				 'CONT_STATUS' => $input['CONT_STATUS'],
				 'JUMLAH' => $input['JUMLAH'],
				 'CONT_TYPE' => $input['CONT_TYPE'],
				 'CONT_TYPE_NAME' => $input['CONT_TYPE_NAME'],
				 'SATUAN' => $input['SATUAN'],
				 'SATUAN_NAME' => $input['SATUAN_NAME'],
				 'TARIFF' => $input['TARIFF'],
				 'BRANCH_ID' => $input['service_branch_id'],
				 'BRANCH_CODE' => $input['service_branch_code'],
				 'CREATED_BY' => $input['CREATED_BY'],
				 'CREATED_DATE' => \DB::raw("TO_DATE('".$datenow."', 'YYYY-MM-DD')"),
			 ]);
			 return [ "success" => true, "result" => "Success, simpan data container"];

			} else {

				$id = $input['TARIFF_ID'];
				DB::connection('mdm_ilcs')->table('TM_MTC_TARIFF')->where('tariff_id', $id)->delete();
				DB::connection('mdm_ilcs')->table('TM_MTC_TARIFF')->insert([
				 'TARIFF_ID' => $id,
				 'OBJECT_TARIFF' => $input['OBJECT_TARIFF'],
				 'OBJECT_NAME' => $input['OBJECT_NAME'],
				 'CONT_STATUS' => $input['CONT_STATUS'],
				 'JUMLAH' => $input['JUMLAH'],
				 'CONT_TYPE' => $input['CONT_TYPE'],
				 'CONT_TYPE_NAME' => $input['CONT_TYPE_NAME'],
				 'SATUAN' => $input['SATUAN'],
				 'SATUAN_NAME' => $input['SATUAN_NAME'],
				 'TARIFF' => $input['TARIFF'],
				 'BRANCH_ID' => $input['service_branch_id'],
				 'BRANCH_CODE' => $input['service_branch_code'],
				 'CREATED_BY' => $input['CREATED_BY'],
				 'CREATED_DATE' => \DB::raw("TO_DATE('".$datenow."', 'YYYY-MM-DD')"),
			 ]);
			 return [ "success" => true, "result" => "Success, update data container"];
			}

	}

}
