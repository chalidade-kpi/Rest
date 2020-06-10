<?php

namespace App\Helper\Jbi;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterContainer{

	public static function storeMasterContainer($input){
		$cont_no = $input['CONT_NO'];
		// $check = DB::connection('mdm_ilcs')->table('TM_CONTAINER')->where('cont_no', $cont_no)->count();
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
		return [ "success" => true, "result" => "Success, simpan data container", "cont_no"=> $cont_no ];

		/* if ($check > 0) {
			return [ "success" => false, "result" => "Error, nomor container sudah ada", "cont_no"=> $cont_no ];
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
		return [ "success" => true, "result" => "Success, store master container", "cont_no"=> $cont_no ];
	} */

	}

}
