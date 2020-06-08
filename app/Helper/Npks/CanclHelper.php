<?php

namespace App\Helper\Npks;

use Illuminate\Support\Facades\DB;

class CanclHelper{
	public static function canceledReqPrepareGD($input,$config){
		$cnclHdr = DB::connection('omuster')->table('TX_HDR_CANCELLED')->where('cancelled_id',$input['id'])->first();
		if (empty($cnclHdr)) {
			return ['Success' => false, 'result_msg' => 'canceled request not found'];
		}else if (in_array($cnclHdr->cancelled_status, [2,3])) {
			return ['Success' => false, 'result_msg' => "Fail, requst already send!"];
		}
		$reqsHdr = DB::connection('omuster')->table($config['head_table'])->where($config['head_no'],$cnclHdr->cancelled_req_no)->first();
		if (empty($reqsHdr)) {
			return ['Success' => false, 'result_msg' => 'canceled request not found'];
		}
		$reqsHdr = (array)$reqsHdr;
		$pluck = DB::connection('omuster')->table('TX_DTL_CANCELLED')->where('cancl_hdr_id',$input['id'])->pluck('cancl_cont');
		if (empty($pluck) or empty($pluck[0])) {
			$pluck = DB::connection('omuster')->table('TX_DTL_CANCELLED')->where('cancl_hdr_id',$input['id'])->pluck('cancl_si');
			if (empty($pluck) or empty($pluck[0])){
				return ['Success' => false, 'result_msg' => 'dtl canceled is null'];
			}
		}
		$cekStart = DB::connection('omuster')->table($config['head_tab_detil'])
			->where($config['head_forigen'],$reqsHdr[$config['head_primery']])
			->whereIn($config['DTL_BL'],$pluck)
			->get();

		return [
			'Success' => true,
			'cnclHdr' => $cnclHdr,
			'reqsHdr' => $reqsHdr,
			'cekStart' => $cekStart
		];
	}

	public static function canceledReqPrepare($input, $config, $up){
		$canceledReqPrepareGD = static::canceledReqPrepareGD($input,$config);
		if ($canceledReqPrepareGD['Success'] == false) {
			return $canceledReqPrepareGD;
		}

		$cnclHdr = $canceledReqPrepareGD['cnclHdr'];
		$reqsHdr = $canceledReqPrepareGD['reqsHdr'];
		$cekStart = $canceledReqPrepareGD['cekStart'];

		if ($up == false) {
			return ['Success' => true, 'find' => $reqsHdr, 'canc' => (array)$cnclHdr];
		}

		foreach ($cekStart as $cek) {
			$cek = (array)$cek;
			if ($cek[$config['DTL_FL_REAL']] != 1 and !in_array($input['nota_id'],[21,22])) {
				return [
					'Success' => false,
					'no_item' => $cek[$config['DTL_BL']],
					'result_msg' => 'Fail, '.$cek[$config['DTL_BL']].' telah masuk tahap realisasi'
				];
			}
		}
		$cnclDtl = DB::connection('omuster')->table('TX_DTL_CANCELLED')->where('cancl_hdr_id',$cnclHdr->cancelled_id)->get(); //kurang di SUM
		foreach ($cnclDtl as $list) {
			$noDtl = $list->cancl_cont.$list->cancl_si;
			$reqDtl = DB::connection('omuster')->table($config['head_tab_detil'])->where([
				$config['head_forigen'] => $reqsHdr[$config['head_primery']],
				$config['DTL_BL'] => $noDtl
			])->first();
			if (empty($reqDtl)) {
				return [
					'Success' => false,
					'no_item' => $noDtl,
					'result_msg' => 'Fail, '.$noDtl.' tidak ditemukan'
				];
			}
			$reqDtl = (array)$reqDtl;
			if ($reqDtl[$config['DTL_FL_REAL']] != 1 and !in_array($input['nota_id'],[21,22])) {
				return [
					'Success' => false,
					'no_item' => $reqDtl[$config['DTL_BL']],
					'result_msg' => 'Fail, '.$reqDtl[$config['DTL_BL']].' sudah melakukan realisasi'
				];
			}
			if ($config['DTL_QTY'] == 1 or $config['kegiatan_batal'] == 21) {
				$reqDtlQty = 1;
			}else{
				$reqDtlQty = $reqDtl[$config['DTL_QTY']]-$reqDtl[$config['DTL_QTY_CANC']];
          if($input['nota_id'] == 21) { $reqDtlQty = $reqDtlQty-$reqDtl['rec_cargo_dtl_real_qty']; }
              else if($input['nota_id'] == 22) { $reqDtlQty = $reqDtlQty-$reqDtl['del_cargo_dtl_real_qty']; }
			}
			if ($list->cancl_qty > $reqDtlQty) {
				return [
					'Success' => false,
					'no_item' => $reqDtl[$config['DTL_BL']],
					'result_msg' => 'Fail, '.$reqDtl[$config['DTL_BL']].' qty yang dibatalkan melebihi data request'
				];
			}
		}
		static::canceledReqPrepareContainerOrBarang($config,$reqsHdr,$cnclHdr,$cnclDtl);
		return ['Success' => true, 'find' => $reqsHdr, 'canc' => (array)$cnclHdr];
	}

	public static function canceledReqPrepareContainerOrBarang($config,$reqsHdr,$cnclHdr,$cnclDtl){
		foreach ($cnclDtl as $list) {
			$noDtl = $list->cancl_cont.$list->cancl_si;
			if (!empty($config['DTL_IS_CANCEL']) && !isset($config['DTL_IS_CANCELLED'])){
				$upd = [
					$config['DTL_IS_ACTIVE'] => 'N',
					$config['DTL_IS_CANCEL'] => 'Y'
				];
			}else{
			$oldDtl = DB::connection('omuster')->table($config['head_tab_detil'])->where([
				$config['head_forigen'] => $reqsHdr[$config['head_primery']],
				$config['DTL_BL'] => $noDtl
			])->get();
			$oldDtl = $oldDtl[0];
			$oldDtl = (array)$oldDtl;
				$upd = [
					$config['DTL_IS_CANCEL'] => 'Y',
					$config['DTL_QTY_CANC'] => $list->cancl_qty
					// $config['DTL_QTY_CANC'] => $list->cancl_qty + $oldDtl[$config['DTL_QTY_CANC']]
				];
			}
			DB::connection('omuster')->table($config['head_tab_detil'])->where([
				$config['head_forigen'] => $reqsHdr[$config['head_primery']],
				$config['DTL_BL'] => $noDtl
			])->update($upd);

			if ($config['head_table'] == "TX_HDR_STUFF" or $config['head_table'] == "TX_HDR_STRIPP") {
				static::prepareDuplicateRec($reqsHdr[$config['head_no']],$noDtl);
			}
		}

		// Tambahan Change Header Flag
		if ($config['CANCELLED_STATUS'] == 21 || $config['CANCELLED_STATUS'] == 22) {

		} else {
			static::turnOffReq($config,$reqsHdr);
		}
	}

	public static function turnOffReq($config,$reqsHdr){
		$dtlIsActive = DB::connection('omuster')->table($config['head_tab_detil'])->where([
		$config['head_forigen'] => $reqsHdr[$config['head_primery']],
		$config['DTL_IS_ACTIVE'] => 'Y',
		$config['DTL_IS_CANCEL'] => 'N'
		])->get();

		if (count($dtlIsActive) == 0) {
			$updateHdrFlagCancel = DB::connection('omuster')
			->table($config['head_table'])
			->where($config['head_primery'], $reqsHdr[$config['head_primery']])
			->update([$config['head_status'] => 9]);
			if ($config['head_table'] == "TX_HDR_STUFF" or $config['head_table'] == "TX_HDR_STRIPP") {
				DB::connection('omuster')->table('TX_HDR_REC')->where('rec_no',$reqsHdr[$config['head_no']])->update(['rec_status'=>12]);
			}
		}
	}

	public static function turnDrafReq($config,$reqsHdr){
		$dtlIsActive = DB::connection('omuster')->table($config['head_tab_detil'])->where([
		$config['head_forigen'] => $reqsHdr[$config['head_primery']],
		$config['DTL_IS_ACTIVE'] => 'Y',
		$config['DTL_IS_CANCEL'] => 'N'
		])->get();

		if (count($dtlIsActive) > 0) {
			$updateHdrFlagCancel = DB::connection('omuster')
			->table($config['head_table'])
			->where($config['head_primery'], $reqsHdr[$config['head_primery']])
			->update([$config['head_status'] => 1]);
			if ($config['head_table'] == "TX_HDR_STUFF" or $config['head_table'] == "TX_HDR_STRIPP") {
				DB::connection('omuster')->table('TX_HDR_REC')->where('rec_no',$reqsHdr[$config['head_no']])->update(['rec_status'=>10]);
			}
		}
	}

	public static function prepareDuplicateRec($recNo,$noDtl){
		$recHdr = DB::connection('omuster')->table('TX_HDR_REC')->where('rec_no',$recNo)->first();
		if (!empty($recHdr)) {
			DB::connection('omuster')->table('TX_DTL_REC')->where([
				'rec_hdr_id' => $recHdr->rec_id,
				'rec_dtl_cont' => $noDtl
			])->update([
				'rec_dtl_isactive' => 'N',
				'rec_dtl_iscancelled' => 'Y'
			]);
		}
	}

	public static function undoPrepareDuplicateRec($recNo,$noDtl){
		$recHdr = DB::connection('omuster')->table('TX_HDR_REC')->where('rec_no',$recNo)->first();
		if (!empty($recHdr)) {
			DB::connection('omuster')->table('TX_DTL_REC')->where([
				'rec_hdr_id' => $recHdr->rec_id,
				'rec_dtl_cont' => $noDtl
			])->update([
				'rec_dtl_isactive' => 'Y',
				'rec_dtl_iscancelled' => 'N'
			]);
		}
	}

	public static function cekReqOrCanc($input,$config){
		$migrateTariff = true;
		$findCanc = null;
		if (!empty($input['canceled']) and $input['canceled'] == 'true') {
			$findCanc = DB::connection('omuster')->table('TX_HDR_CANCELLED')->where('cancelled_id',$input['id'])->first();
			$migrateTariff = false;
		}

		if (empty($findCanc)) {
			$find = DB::connection('omuster')->table($config['head_table'])->where($config['head_primery'],$input['id'])->get();
			if (empty($find)) {
				return ['result' => "Fail, requst not found!", "Success" => false];
			}
			$find = (array)$find[0];
			$retHeadNo = $find[$config['head_no']];
		}else{
			$find = DB::connection('omuster')->table($config['head_table'])->where($config['head_no'],$findCanc->cancelled_req_no)->get();
			if (empty($find)) {
				return ['result' => "Fail, requst not found!", "Success" => false];
			}
			$find = (array)$find[0];
			$retHeadNo = $findCanc->cancelled_no;
			if ($find[$config['head_paymethod']] == 1) {
				$migrateTariff = true;
			}
		}

		$canceledReqPrepare = static::canceledReqPrepare($input, $config, true);
		return [
			"Success" => true,
			"migrateTariff" => $migrateTariff,
			'findCanc' => $findCanc,
			'find' => $find,
			'retHeadNo' => $retHeadNo
		];
	}

	public static function undoCanclSet($input,$config,$findCanc,$findReq){
		$canclDtl = DB::connection('omuster')->table('TX_DTL_CANCELLED')->where('cancl_hdr_id',$findCanc->cancelled_id)->get();
		foreach ($canclDtl as $lcd) {
			$cndtn = [
				$config['head_forigen'] => $findReq[$config['head_primery']]
			];
			if (!empty($lcd->cancl_cont)) {
				$cndtn[$config['DTL_BL']] = $lcd->cancl_cont;
				$up = [
					$config['DTL_IS_ACTIVE'] => 'Y',
					$config['DTL_IS_CANCEL'] => 'N'
				];
				if ($config['head_table'] == "TX_HDR_STUFF" or $config['head_table'] == "TX_HDR_STRIPP"){
					static::undoPrepareDuplicateRec($findReq[$config['head_no']],$lcd->cancl_cont);
				}
			}else if (!empty($lcd->cancl_si)) {
				$cndtn[$config['DTL_BL']] = $lcd->cancl_si;
				$up = [
					$config['DTL_IS_CANCEL'] => 'Y'
				];
				$oldDtl = DB::connection('omuster')->table($config['head_tab_detil'])->where($cndtn)->first();
				$oldDtl = (array)$oldDtl;
				$undoCancQty = $oldDtl[$config['DTL_QTY_CANC']]-$lcd->cancl_qty;
				if ($undoCancQty == 0) {
					$up[$config['DTL_IS_CANCEL']] = 'N';
				}
				$up[$config['DTL_QTY_CANC']] = $undoCancQty;
			}
			DB::connection('omuster')->table($config['head_tab_detil'])->where($cndtn)->update($up);
		}
		if ($config['head_table'] == "TX_HDR_STUFF" or $config['head_table'] == "TX_HDR_STRIPP"){
			static::turnDrafReq($config,$findReq);
		}
	}
}
