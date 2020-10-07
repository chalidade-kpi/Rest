<?php

namespace App\Helper\Npk;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Helper\Npk\ConnectedExternalAppsNPK;
use App\Helper\Globalconfig\BillingEngine;

use App\Models\OmCargo\TxHdrUper;

class RequestBookingNPK{
	// BTN
		public static function sendRequest($input){
			$input['table'] = strtoupper($input['table']);
			$config 				= static::config($input['table']);
			$find 					= DB::connection('omcargo')->table($input['table'])->where($config['head_primery'],$input['id'])->get();
			if (empty($find)) {
				return ['Success' => false, 'result' => "Fail, requst not found!"];
			}
			$find = (array)$find[0];

			// Tambahan Chalid 07/10/2020
			if (isset($input["nota_id"]) and !empty($input["nota_id"]))
				$nota_id = $input['nota_id'];
			else
				$nota_id = $find[$config['head_nota_id']];

			$cekStatusNota 	= DB::connection('mdm')->table('TS_NOTA')->where([
				'branch_id' 	=> $find[$config['head_branch']],
				'branch_code' => $find[$config['head_branch_code']],
				'nota_id'		 	=> $nota_id
				// 'nota_id'		 	=> $input['nota_id'] //Chalid rubah 07/10/2020
			])->get();

			if (count($cekStatusNota) == 0) {
				return ['Success' => false, 'result' => "Fail, nota not available!"];
			}else if ($cekStatusNota[0]->flag_status == 'N') {
				return ['Success' => false, 'result' => "Fail, nota not active!"];
			}

			$pbmCek = 'N';
			if ($input['table'] == 'TX_HDR_BM') {
				$countPBM = DB::connection('mdm')->table('TM_PBM_INTERNAL')->where('PBM_ID',$find['bm_pbm_id'])->where('BRANCH_ID',$find['bm_branch_id'])->where('BRANCH_CODE',$find['bm_branch_code'])->count();
				if ($countPBM > 0) { $pbmCek = 'Y'; }
			}

			// Materai
			// $getBranch = DB::connection('mdm')->table('TM_BRANCH')->where([
			// 	'branch_id' 	=> $find[$config['head_branch']],
			// 	'branch_code' => $find[$config['head_branch_code']]
			// ])->first();
			//
			// $input["tipe_layanan"] 	= $cekStatusNota[0]->nota_sub_context;
			// $input["pCabang"] 			= $getBranch->branch_org_id;
			//
			// $materai = ConnectedExternalAppsNPK::checkMaterai($input);


			// build head
				$setH 										= [];
				$setH['P_NOTA_ID'] 				= $nota_id;
				$setH['P_BRANCH_ID'] 			= $find[$config['head_branch']];
				$setH['P_BRANCH_CODE'] 		= $find[$config['head_branch_code']];
				$setH['P_CUSTOMER_ID'] 		= $find[$config['head_cust']];
				$setH['P_PBM_INTERNAL'] 	= $pbmCek;
				$setH['P_BOOKING_NUMBER'] = $find[$config['head_no']];
				$setH['P_REALIZATION'] 		= 'N';
				// Tambahan
				// $setH['P_MATERAI'] = $materai;
				$setH['P_RESTITUTION'] 		= 'N';
				$setH['P_TRADE'] 					= $find[$config['head_trade']];
				$setH['P_USER_ID'] 				= $find[$config['head_by']];
			// build head

			// build detil
				$setD 		= [];
				$detil 		= DB::connection('omcargo')->table($config['head_tab_detil'])->where($config['head_forigen'], $find[$config['head_primery']])->get();
				foreach ($detil as $list) {
					$newD 										= [];
					$list 										= (array)$list;

					$newD['DTL_VIA'] 					= 'NULL';
					$newD['DTL_BL'] 					= empty($list[$config['head_tab_detil_bl']]) ? 'NULL' : $list[$config['head_tab_detil_bl']];
					$newD['DTL_PKG_ID'] 			= empty($list['dtl_pkg_id']) ? 'NULL' : $list['dtl_pkg_id'];
					$newD['DTL_CMDTY_ID'] 		= empty($list['dtl_cmdty_id']) ? 'NULL' : $list['dtl_cmdty_id'];
					$newD['DTL_CHARACTER'] 		= empty($list['dtl_character_id']) ? 'NULL' : $list['dtl_character_id'];
					$newD['DTL_CONT_SIZE'] 		= empty($list['dtl_cont_size']) ? 'NULL' : $list['dtl_cont_size'];
					$newD['DTL_CONT_TYPE'] 		= empty($list['dtl_cont_type']) ? 'NULL' : $list['dtl_cont_type'];
					$newD['DTL_CONT_STATUS'] 	= empty($list['dtl_cont_status']) ? 'NULL' : $list['dtl_cont_status'];
					$newD['DTL_UNIT_ID'] 			= empty($list['dtl_unit_id']) ? 'NULL' : $list['dtl_unit_id'];
					$newD['DTL_QTY'] 					= empty($list['dtl_qty']) ? 'NULL' : $list['dtl_qty'];

					if ($input['table'] == 'TX_HDR_CANCELLED') {
						$newD['DTL_PKG_ID'] 			= empty($list['cncl_pkg_id']) ? 'NULL' : $list['cncl_pkg_id'];
						$newD['DTL_CMDTY_ID'] 		= empty($list['cncl_cmdty_id']) ? 'NULL' : $list['cncl_cmdty_id'];
						$newD['DTL_CHARACTER'] 		= empty($list['cncl_character_id']) ? 'NULL' : $list['cncl_character_id'];
						$newD['DTL_CONT_SIZE'] 		= empty($list['cncl_cont_size']) ? 'NULL' : $list['cncl_cont_size'];
						$newD['DTL_CONT_TYPE'] 		= empty($list['cncl_cont_type']) ? 'NULL' : $list['cncl_cont_type'];
						$newD['DTL_CONT_STATUS'] 	= empty($list['cncl_cont_status']) ? 'NULL' : $list['cncl_cont_status'];
						$newD['DTL_UNIT_ID'] 			= empty($list['cncl_unit_id']) ? 'NULL' : $list['cncl_unit_id'];
						$newD['DTL_QTY'] 					= empty($list['cncl_qty']) ? 'NULL' : $list['cncl_qty'];
					}

					$getPFS = DB::connection('mdm')->table('TM_COMP_NOTA')->where('NOTA_ID', $nota_id)->where('BRANCH_ID',$find[$config['head_branch']])->where('BRANCH_CODE',$find[$config['head_branch_code']])->where('GROUP_TARIFF_ID', 15)->count();
					if ($getPFS > 0) {
						$newD['DTL_PFS'] = 'Y';
					}else{
						$newD['DTL_PFS'] = 'N';
					}

					$DTL_BM_TYPE = 'NULL';
					if ($nota_id == "13") {
						if ($input['table'] == 'TX_HDR_CANCELLED')
							$DTL_BM_TYPE = empty($list['cncl_bm_type_id']) ? 'NULL' : $list['cncl_bm_type_id'];
						else
							$DTL_BM_TYPE = empty($list['dtl_bm_type']) ? 'NULL' : $list['dtl_bm_type'];

					}
					$newD['DTL_BM_TYPE'] = $DTL_BM_TYPE;

					$DTL_STACK_AREA = 'NULL';
					if (in_array($nota_id, ["14", "15","19", 14, 15, 19])) {
						if ($input['table'] == 'TX_HDR_CANCELLED')
							$DTL_STACK_AREA = empty($list['cncl_stacking_type_id']) ? 'NULL' : $list['cncl_stacking_type_id'];
						else
							$DTL_STACK_AREA = empty($list['dtl_stacking_type_id']) ? 'NULL' : $list['dtl_stacking_type_id'];
					}
					$newD['DTL_STACK_AREA'] = $DTL_STACK_AREA;

					if ($config['head_tab_detil_tl'] != null) {
						$newD['DTL_TL'] = empty($list[$config['head_tab_detil_tl']]) ? 'NULL' : $list[$config['head_tab_detil_tl']];
					}else{
						$newD['DTL_TL'] = 'NULL';
					}

					if ($config['head_tab_detil_date_in'] != null) {
						if ($input['table'] == 'TX_HDR_REC') {
							$newD['DTL_DATE_IN'] = empty($list[$config['head_tab_detil_date_in']]) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($list[$config['head_tab_detil_date_in']])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
						}else{
							$newD['DTL_DATE_IN'] = empty($list[$config['head_tab_detil_date_in']]) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($list[$config['head_tab_detil_date_in']])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
							if (!empty($find['del_ext_from'])) {
								$gthdrId 							= DB::connection('omcargo')->table('TX_HDR_DEL')->where('del_no', $find['del_ext_from'])->get();
								$gthdrId 							= $gthdrId[0];
								$getdatein 						= DB::connection('omcargo')->table('TX_DTL_DEL')->where('hdr_del_id',$gthdrId->del_id)->where('dtl_del_bl', $list['dtl_del_bl'])->get();
								$getdatein 						= $getdatein[0];
								$getdatein 						= (array)$getdatein;
								$newD['DTL_DATE_IN'] 	= 'to_date(\''.\Carbon\Carbon::parse($getdatein[$config['head_tab_detil_date_in']])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
							}
						}
					}else{
						$newD['DTL_DATE_IN'] 		= 'NULL';
					}

					$newD['DTL_DATE_OUT'] 		= 'NULL';
					$newD['DTL_DATE_OUT_OLD'] = 'NULL';

					if ($config['head_tab_detil_date_out_old'] != null and $input['table'] == 'TX_HDR_DEL') {
						if ($find['del_ext_status'] == 'Y') {
							$newD['DTL_DATE_OUT'] 		= empty($list['dtl_out']) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($list['dtl_out'])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
							$old_req_id 							= DB::connection('omcargo')->table('TX_HDR_DEL')->where('DEL_NO',$find['del_ext_from'])->get();
							$old_req_id 							= $old_req_id[0]->del_id;
							$old_bl 									= DB::connection('omcargo')->table('TX_DTL_DEL')->where('HDR_DEL_ID',$old_req_id)->where('DTL_DEL_BL',$list['dtl_del_bl'])->get();
							$old_bl_date_out 					= $old_bl[0]->dtl_out;
							$newD['DTL_DATE_OUT_OLD'] = empty($old_bl_date_out) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($old_bl_date_out)->format('Y-m-d').'\',\'yyyy-MM-dd\')';
						}else{
							$newD['DTL_DATE_OUT'] = empty($list['dtl_out']) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($list['dtl_out'])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
							$newD['DTL_DATE_OUT_OLD'] = 'NULL';
						}
					}else{
						$newD['DTL_DATE_OUT_OLD'] = empty($find[$config['head_tab_detil_date_out_old']]) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($find[$config['head_tab_detil_date_out_old']])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
						$newD['DTL_DATE_OUT'] 		= empty($find[$config['head_tab_detil_date_out']]) ? 'NULL' : 'to_date(\''.\Carbon\Carbon::parse($find[$config['head_tab_detil_date_out']])->format('Y-m-d').'\',\'yyyy-MM-dd\')';
					}

					$setD[] = $newD;
				}
			// build detil

			// build eqpt
				$setE = [];
				$eqpt = DB::connection('omcargo')->table('TX_EQUIPMENT')->where('req_no', $find[$config['head_no']])->get();
				foreach ($eqpt as $list) {
					$newE 							= [];
					$list 							= (array)$list;
					$newE['EQ_TYPE'] 		= empty($list['eq_type_id']) ? 'NULL' : $list['eq_type_id'];
					$newE['EQ_QTY'] 		= empty($list['eq_qty']) ? 'NULL' : $list['eq_qty'];
					$newE['EQ_UNIT_ID'] = empty($list['eq_unit_id']) ? 'NULL' : $list['eq_unit_id'];
					$newE['EQ_GTRF_ID'] = empty($list['group_tariff_id']) ? 'NULL' : $list['group_tariff_id'];
					$newE['EQ_PKG_ID'] 	= empty($list['package_id']) ? 'NULL' : $list['package_id'];
					$newE['EQ_QTY_PKG'] = empty($list['unit_qty']) ? 'NULL' : $list['unit_qty'];
					$setE[] 						= $newE;
				}
			// build eqpt

			// build paysplit
				$setP = [];
				if (isset($find[$config['head_split']]) and !empty($find[$config['head_split']])) {
				if ($find[$config['head_split']] == 'Y') {
					$paysplit = DB::connection('omcargo')->table('TX_SPLIT_NOTA')->where('req_no', $find[$config['head_no']])->get();
					$paysplit = (array)$paysplit;
					foreach ($paysplit as $list) {
						$newP 							= [];
						$list 							= (array)$list;
						$newP['PS_CUST_ID'] = $list['cust_id'];
						$newP['PS_GTRF_ID'] = $list['group_tarif_id'];
						$setP[] 						= $newP;
					}
				}
			}
			// build paysplit

			// set data
				$set_data = [
					'head' => $setH,
					'detil' => $setD,
					'eqpt' => $setE,
					'paysplit' => $setP
				];
			// set data

			// return $tariffResp = BillingEngine::calculateTariff($set_data);
			$tariffResp = BillingEngine::calculateTariff($set_data);

			if ($tariffResp['result_flag'] == 'S') {
				if ($input['table'] == 'TX_HDR_CANCELLED') {
					DB::connection('omcargo')->table($input['table'])->where($config['head_primery'],$input['id'])->update([
						$config['head_status'] =>3
					]);

					$notaHdrLama 	= (array) DB::connection('omcargo')->table("TX_HDR_NOTA")->where('NOTA_NO', $find[$config['head_nota_no_reff']])->first();
					$notaDtlLama 	= (array) DB::connection('omcargo')->table("TX_DTL_NOTA")->where('NOTA_HDR_ID',  $notaHdrLama["nota_id"])->get();
					$tariffHdr 		= (array) DB::connection('eng')->table("TX_TEMP_TARIFF_HDR")->where('BOOKING_NUMBER', $find[$config['head_real_req_no']])->first();
					$sequence 		= DB::connection('omcargo')->table("DUAL")->select("SEQ_TX_HDR_NOTA.NEXTVAL")->get();
					$idHeader     = ($sequence[0]->nextval);
					$header 			= [
							"APP_ID" 								=> $notaHdrLama["app_id"],
							"NOTA_AMOUNT" 					=> $tariffHdr["total"],
							"NOTA_BRANCH_ACCOUNT" 	=> $notaHdrLama["nota_branch_account"],
							"NOTA_BRANCH_CODE" 			=> $find[$config['head_branch_code']],
							"NOTA_BRANCH_ID" 				=> $find[$config['head_branch']],
							"NOTA_CONTEXT" 					=> $tariffHdr["nota_context"],
							"NOTA_CURRENCY_CODE" 		=> $notaHdrLama["nota_currency_code"],
							"NOTA_CUST_ADDRESS" 		=> $find[$config['head_cust_addr']],
							"NOTA_CUST_ID" 					=> $find[$config['head_cust']],
							"NOTA_CUST_NAME" 				=> $find[$config['head_cust_name']],
							"NOTA_CUST_NPWP" 				=> $find[$config['head_cust_npwp']],
							"NOTA_DATE" 						=> $find[$config['head_date']],
							"NOTA_DPP" 							=> $tariffHdr["dpp"],
							"NOTA_FAKTUR_NO" 				=> $find[$config['head_nota_no']],
							"NOTA_GROUP_ID" 				=> $find[$config['head_nota_id']],
							"NOTA_ID" 							=> $idHeader,
							"NOTA_NO" 							=> $find[$config['head_nota_no']],
							"NOTA_NO_EX" 						=> $find[$config['head_nota_no_reff']],
							"NOTA_ORG_ID" 					=> $notaHdrLama["nota_org_id"],
							"NOTA_PAID" 						=> $notaHdrLama["nota_paid"],
							"NOTA_PAID_DATE" 				=> $notaHdrLama["nota_paid_date"],
							"NOTA_PPN" 							=> $tariffHdr["ppn"],
							"NOTA_PROFORMA_NO" 			=> $find[$config['head_nota_no']],
							"NOTA_REAL_NO" 					=> $find[$config['head_real_req_no']],
							"NOTA_REQ_NO" 					=> $find[$config['head_req_no']],
							"NOTA_SERVICE_CODE" 		=> $notaHdrLama["nota_service_code"],
							"NOTA_STATUS" 					=> 4,
							"NOTA_SUB_CONTEXT" 			=> $tariffHdr["nota_sub_context"],
							"NOTA_TAX_CODE" 				=> "011",
							"NOTA_TERMINAL" 				=> $find[$config['head_terminal_code']],
							"NOTA_TRADE_TYPE" 			=> $find[$config['head_trade']],
							"NOTA_UKK" 							=> $find[$config['head_ukk']],
							"NOTA_VESSEL_NAME" 			=> $find[$config['head_vessel_name']],
							"PROFORMA_DATE" 				=> $notaHdrLama["proforma_date"],
							"REST_PAYMENT" 					=> $notaHdrLama["rest_payment"]
						];

					$allDetail 	= [];
					foreach ($notaDtlLama as $listDtlNota) {
						foreach ($listDtlNota as $key => $value) {
							if ($key == "nota_hdr_id") {
								$detail[$key] = $idHeader;
							} else if ($key == "nota_dtl_id") {
								$detail[$key] = null;
							}
						}
						$allDetail[] = $detail;
					}

					$insertHdr = DB::connection('omcargo')->table('TX_HDR_NOTA')->insert([$header]);
					$insertDtl = DB::connection('omcargo')->table('TX_DTL_NOTA')->insert($allDetail);

				} else {
					DB::connection('omcargo')->table($input['table'])->where($config['head_primery'],$input['id'])->update([
						$config['head_status'] => 2
					]);
				}
			}
			return $tariffResp;
    }

	    public static function approvalRequest($input){
	    	$input['table'] = strtoupper($input['table']);
			$config = static::config($input['table']);
			$find = DB::connection('omcargo')->table($input['table'])->where($config['head_primery'],$input['id'])->get();
			if (empty($find)) {
				return ['result' => "Fail, requst not found!", "Success" => false];
			}
			$find = (array)$find[0];
			if ($find[$config['head_status']] == 3 and $input['approved'] == 'true') {
				return ['result' => "Fail, requst already approved!", "Success" => false];
			}
			$uper = DB::connection('omcargo')->table('TX_HDR_UPER')->where('uper_req_no',$find[$config['head_no']])->get();
			if (count($uper) > 0) {
				return ['result' => "Fail, request already exist on uper!", "Success" => false];
			}
			if ($input['approved'] == 'false') {
				DB::connection('omcargo')->table($input['table'])->where($config['head_primery'],$input['id'])->update([
					$config['head_status'] => 4,
					$config['head_mark'] => $input['msg']
				]);
				return ['result' => "Success, rejected requst", 'no_req' => $find[$config['head_no']]];
			}

			$datenow    = Carbon::now()->format('Y-m-d');
			$query = "SELECT * FROM V_PAY_SPLIT WHERE booking_number= '".$find[$config['head_no']]."'";
			$upers = DB::connection('eng')->select(DB::raw($query));
			if (count($upers) == 0) {
				return ['result' => "Fail, uper and tariff not found!", "Success" => false];
			}
			$upPercent = DB::connection('eng')->table('TS_UPER')->where('UPER_NOTA', $config['head_nota_id'])->where('BRANCH_ID', $find[$config['head_branch']])->where('UPER_CUST_ID', $find[$config['head_cust']])->get();
			if (count($upPercent) == 0) {
				$migrateTariff = false;
				$upPercent = DB::connection('eng')->table('TS_UPER')->where('UPER_NOTA', $config['head_nota_id'])->where('BRANCH_ID', $find[$config['head_branch']])->whereNull('UPER_CUST_ID')->get();
				if (count($upPercent) == 0){
					$migrateTariff = false;
				}else{
					$upPercent = $upPercent[0];
					if ($upPercent->uper_presentase == 0) {
						$migrateTariff = false;
					}else{
						$migrateTariff = true;
					}
				}
			}else{
				$upPercent = $upPercent[0];
				if ($upPercent->uper_presentase == 0) {
					$migrateTariff = false;
				}else{
					$migrateTariff = true;
				}
			}
			if ($migrateTariff == true) {
				foreach ($upers as $uper) {
					$uper = (array)$uper;

					$createdUperNo = '';
					// store head
						$headU = new TxHdrUper;
						// $headU->uper_no // dari triger
						$headU->uper_org_id = $uper['branch_org_id'];
						$headU->uper_cust_id = $uper['customer_id'];
						$headU->uper_cust_name = $uper['alt_name'];
						$headU->uper_cust_npwp = $uper['npwp'];
						$headU->uper_cust_address = $uper['address'];
						$headU->uper_date = \DB::raw("TO_DATE('".$datenow."', 'YYYY-MM-DD')");
						$headU->uper_amount = $uper['total_uper'];
						$headU->uper_currency_code = $uper['currency'];
						$headU->uper_status = 'P'; // ? blm fix
						// Tambahan Mas Adi
						$headU->uper_service_code = $uper['nota_service_code'];
						$headU->uper_branch_account = $uper['branch_account'];
						$headU->uper_context = $uper['nota_context'];
						$headU->uper_sub_context = $uper['nota_sub_context'];
						$headU->uper_terminal_code = $find[$config['head_terminal_code']];
						$headU->uper_branch_id = $uper['branch_id'];
						$headU->uper_branch_code = $uper['branch_code'];
						$headU->uper_vessel_name = $find[$config['head_vessel_name']];
						// $headU->uper_faktur_no = ''; // ? dari triger bf i
						$headU->uper_trade_type = $uper['trade_type'];
						$headU->uper_trade_name = $uper['trade_type'] == 'D' ? 'Domestik' : 'Internasional';
						$headU->uper_req_no = $uper['booking_number'];
						$headU->uper_ppn = $uper['ppn_uper'];
						// $headU->uper_paid // ? pasti null
						// $headU->uper_paid_date // ? pasti null
						$headU->uper_percent = $uper['percent_uper'];
						$headU->uper_dpp = $uper['dpp_uper'];
						if ($config['head_pbm_id'] != null) {
							$headU->uper_pbm_id = $find[$config['head_pbm_id']];
						}
						if ($config['head_pbm_name'] != null) {
							$headU->uper_pbm_name = $find[$config['head_pbm_name']];
						}
						if ($config['head_shipping_agent_id'] != null) {
							$headU->uper_shipping_agent_id = $find[$config['head_shipping_agent_id']];
						}
						if ($config['head_shipping_agent_name'] != null) {
							$headU->uper_shipping_agent_name = $find[$config['head_shipping_agent_name']];
						}
						$headU->uper_req_date = $find[$config['head_date']];
						if ($config['head_terminal_name'] != null) {
							$headU->uper_terminal_name = $find[$config['head_terminal_name']];
						}
						$headU->uper_nota_id = $uper['nota_id'];
						$headU->app_id =$find['app_id'];
						$headU->save();

						$headU = TxHdrUper::find($headU->uper_id);
						$createdUperNo .= $headU->uper_no.', ';
					// store head

					$queryAgain = "SELECT * FROM TX_TEMP_TARIFF_SPLIT WHERE TEMP_HDR_ID = '".$uper['temp_hdr_id']."' AND CUSTOMER_ID = '".$uper['customer_id']."'";
					$group_tariff = DB::connection('eng')->select(DB::raw($queryAgain));

					$countLine = 0;
					foreach ($group_tariff as $grpTrf) {
						$grpTrf = (array)$grpTrf;
						$uperD = DB::connection('eng')->table('TX_TEMP_TARIFF_DTL')->where('TEMP_HDR_ID',$uper['temp_hdr_id'])->where('group_tariff_id',$grpTrf['group_tariff_id'])->get();

						foreach ($uperD as $list) {
							$countLine++;
							$list = (array)$list;
							$set_data = [
								"uper_hdr_id" => $headU->uper_id,
								"dtl_line" => $countLine,
								"dtl_line_desc" => $list['memoline'],
								// "dtl_line_context" => , // perlu konfimasi
								"dtl_service_type" => $list['group_tariff_name'],
								"dtl_amount" => $list['total_uper'],
								"dtl_ppn" => $list["ppn_uper"],
								"dtl_masa" => $list["day_period"],
								// "dtl_masa1" => , // cooming soon
								// "dtl_masa12" => , // cooming soon
								// "dtl_masa2" => , // cooming soon
								"dtl_masa_reff" => $list["stack_combine"],
								"dtl_total_tariff" => $list["tariff_uper"],
								"dtl_tariff" => $list["tariff"],
								"dtl_package" => $list["package_name"],
								"dtl_qty" => $list["qty"],
								"dtl_eq_qty" => $list["eq_qty"],
								"dtl_unit" => $list["unit_id"],
								"dtl_unit_qty" => $list["unit_qty"],
								"dtl_unit_name" => $list["unit_name"],
								"dtl_group_tariff_id" => $list["group_tariff_id"],
								"dtl_group_tariff_name" => $list["group_tariff_name"],
								"dtl_bl" => $list["no_bl"],
								"dtl_dpp" => $list["tariff_cal_uper"],
								"dtl_commodity" => $list["commodity_name"],
								"dtl_equipment" => $list["equipment_name"],
								"dtl_sub_tariff" => $list["sub_tariff"],
								"dtl_create_date" => \DB::raw("TO_DATE('".$datenow."', 'YYYY-MM-DD')")
							];
							DB::connection('omcargo')->table('TX_DTL_UPER')->insert($set_data);
						}
					}
				}
			}

			DB::connection('omcargo')->table($input['table'])->where($config['head_primery'],$input['id'])->update([
				$config['head_status'] => 3,
				$config['head_mark'] => $input['msg']
			]);

			$sendRequestBooking = '';
			if ($migrateTariff == true) {
				$pesan = "Created Uper No : ".$createdUperNo;
			}else if($migrateTariff == false) {
				$sendRequestBooking = ConnectedExternalAppsNPK::sendRequestBooking(['req_no' => $find[$config['head_no']], 'paid_date' => null ]);
				$pesan = "Uper Not created, uper percent for this request is 0%";
			}

			return [
				'result' => "Success, approved request! ".$pesan,
				"note" => $pesan,
				'no_req' => $find[$config['head_no']],
				'sendRequestBooking' => $sendRequestBooking
			];
	    }

	    public static function config($input){
	    	$requst_config = [
					"TX_HDR_CANCELLED" => [
						"head_eta" 										=> "cancelled_eta",
						"head_etd" 										=> "cancelled_etd",
						"head_etb" 										=> "cancelled_etb",
						"head_ata" 										=> "cancelled_ata",
						"head_atd" 										=> "cancelled_atd",
						"head_kade" 									=> "cancelled_kade",
						"head_real_req_no" 						=> "cancelled_real_req_no",
						"head_real_req_date" 					=> "cancelled_real_req_date",
						"head_req_no" 								=> "cancelled_req_no",
						"head_nota_no"								=> "cancelled_no",
						"head_nota_no_reff"						=> "cancelled_no_reff",
						"head_req_date" 							=> "cancelled_req_date",
						"head_no_reff" 								=> "cancelled_no_reff",
						"head_create_date" 						=> "cancelled_create_date",
						"head_open_stack" 						=> "cancelled_open_stack",
						"head_closing_time" 					=> null,
						"head_cust_id" 								=> "cancelled_cust_id",
						"head_cust_name" 							=> "cancelled_cust_name",
						"head_cust_addr" 							=> "cancelled_cust_address",
						"head_cust_npwp" 							=> "cancelled_cust_npwp",
						"head_voyin" 									=> "cancelled_voyin",
						"head_voyout" 								=> "cancelled_voyout",
						"head_vvd_id" 								=> "cancelled_vvd_id",
						"head_nota_id" 								=> "cancelled_type",
						"head_tab" 										=> "TX_HDR_CANCELLED",
						"head_mark" 									=> "cancelled_remark",
						"head_tab_detil" 							=> "TX_DTL_CANCELLED",
						"head_tab_detil_id" 					=> "cncl_dtl_id",
						"head_ukk" 										=> "cancelled_ukk",
						"head_tab_detil_bl" 					=> "cncl_bl",
						"head_tab_detil_tl" 					=> "cncl_tl",
						"head_tab_detil_date_in" 			=> "cncl_date_in",
						"head_tab_detil_date_out" 		=> "cncl_date_out",
						"head_tab_detil_date_out_old" => null,
						"head_status" 								=> "cancelled_status",
						"head_primery" 								=> "cancelled_id",
						"head_forigen" 								=> "cncl_hdr_id",
						"head_no" 										=> "cancelled_real_req_no",
						"head_split" 									=> null,
						"head_by" 										=> "cancelled_create_by",
						"head_date" 									=> "cancelled_date",
						"head_branch" 								=> "cancelled_branch_id",
						"head_branch_code" 						=> "cancelled_branch_code",
						"head_cust" 									=> "cancelled_cust_id",
						"head_trade" 									=> "cancelled_trade_type",
						"head_trade_name"							=> "cancelled_trade_name",
						"head_terminal_code" 					=> "cancelled_terminal_code",
						"head_terminal_name" 					=> "cancelled_terminal_name",
						"head_pbm_id" 								=> "cancelled_pbm_id",
						"head_pbm_name" 							=> "cancelled_pbm_name",
						"head_shipping_agent_id" 			=> "cancelled_shipping_agent_id",
						"head_shipping_agent_name" 		=> "cancelled_shipping_agent_name",
						"head_vessel_code" 						=> "cancelled_vessel_code",
						"head_vessel_name" 						=> "cancelled_vessel_name"
					],
	        	"TX_HDR_BM" => [
	        		"head_eta" => "bm_eta",
	        		"head_etd" => "bm_etd",
	        		"head_open_stack" => "bm_open_stack",
	        		"head_closing_time" => "bm_closing_time",
	        		"head_cust_id" => "bm_cust_id",
	        		"head_cust_name" => "bm_cust_name",
	        		"head_cust_addr" => "bm_cust_address",
	        		"head_cust_npwp" => "bm_cust_npwp",
	        		"head_voyin" => "bm_voyin",
	        		"head_voyout" => "bm_voyout",
	        		"head_vvd_id" => "bm_vvd_id",
	        		"head_nota_id" => "13",
	        		"head_tab" => "TX_HDR_BM",
	        		"head_mark" => "bm_mark",
	        		"head_tab_detil" => "TX_DTL_BM",
	        		"head_tab_detil_id" => "dtl_bm_id",
	        		"head_tab_detil_bl" => "dtl_bm_bl",
	        		"head_tab_detil_tl" => "dtl_bm_tl",
	        		"head_tab_detil_date_in" => null,
	        		"head_tab_detil_date_out" => null,
	        		"head_tab_detil_date_out_old" => null,
	        		"head_status" => "bm_status",
	        		"head_primery" => "bm_id",
	        		"head_forigen" => "hdr_bm_id",
	        		"head_no" => "bm_no",
	        		"head_split" => "bm_split",
	        		"head_by" => "bm_create_by",
	        		"head_date" => "bm_date",
	        		"head_branch" => "bm_branch_id",
	        		"head_branch_code" => "bm_branch_code",
	        		"head_cust" => "bm_cust_id",
	        		"head_trade" => "bm_trade_type",
	        		"head_terminal_code" => "bm_terminal_code",
	        		"head_terminal_name" => "bm_terminal_name",
	        		"head_pbm_id" => "bm_pbm_id",
	        		"head_pbm_name" => "bm_pbm_name",
	        		"head_shipping_agent_id" => "bm_shipping_agent_id",
	        		"head_shipping_agent_name" => "bm_shipping_agent_name",
	        		"head_vessel_code" => "bm_vessel_code",
	        		"head_vessel_name" => "bm_vessel_name"
	        	],
	        	"TX_HDR_REC" => [
	        		"head_eta" => "rec_eta",
	        		"head_etd" => "rec_etd",
	        		"head_open_stack" => "rec_open_stack",
	        		"head_closing_time" => "rec_closing_time",
	        		"head_cust_id" => "rec_cust_id",
	        		"head_cust_name" => "rec_cust_name",
	        		"head_cust_addr" => "rec_cust_address",
	        		"head_cust_npwp" => "rec_cust_npwp",
	        		"head_voyin" => "rec_voyin",
	        		"head_voyout" => "rec_voyout",
	        		"head_vvd_id" => "rec_vvd_id",
	        		"head_nota_id" => "14",
	        		"head_tab" => "TX_HDR_REC",
	        		"head_mark" => "rec_mark",
	        		"head_tab_detil" => "TX_DTL_REC",
	        		"head_tab_detil_id" => "dtl_rec_id",
	        		"head_tab_detil_bl" => "dtl_rec_bl",
	        		"head_tab_detil_tl" => null,
	        		"head_tab_detil_date_in" => 'dtl_in',
	        		"head_tab_detil_date_out" => 'rec_etd',
	        		"head_tab_detil_date_out_old" => 'rec_extend_from',
	        		"head_status" => "rec_status",
	        		"head_primery" => "rec_id",
	        		"head_forigen" => "hdr_rec_id",
	        		"head_no" => "rec_no",
	        		"head_split" => "rec_split",
	        		"head_by" => "rec_create_by",
	        		"head_date" => "rec_date",
	        		"head_branch" => "rec_branch_id",
	        		"head_branch_code" => "rec_branch_code",
	        		"head_cust" => "rec_cust_id",
	        		"head_trade" => "rec_trade_type",
	        		"head_terminal_code" => "rec_terminal_code",
	        		"head_terminal_name" => "rec_terminal_name",
	        		"head_pbm_id" => null,
	        		"head_pbm_name" => null,
	        		"head_shipping_agent_id" => null,
	        		"head_shipping_agent_name" => null,
	        		"head_vessel_code" => "rec_vessel_code",
	        		"head_vessel_name" => "rec_vessel_name"
	        	],
	        	"TX_HDR_DEL" => [
	        		"head_eta" => "del_eta",
	        		"head_etd" => "del_etd",
	        		"head_open_stack" => "del_open_stack",
	        		"head_closing_time" => "del_closing_time",
	        		"head_cust_id" => "del_cust_id",
	        		"head_cust_name" => "del_cust_name",
	        		"head_cust_addr" => "del_cust_address",
	        		"head_cust_npwp" => "del_cust_npwp",
	        		"head_voyin" => "del_voyin",
	        		"head_voyout" => "del_voyout",
	        		"head_vvd_id" => "del_vvd_id",
	        		"head_nota_id" => "15",
	        		"head_tab" => "TX_HDR_DEL",
	        		"head_mark" => "del_mark",
	        		"head_tab_detil" => "TX_DTL_DEL",
	        		"head_tab_detil_id" => "dtl_del_id",
	        		"head_tab_detil_bl" => "dtl_del_bl",
	        		"head_tab_detil_tl" => null,
	        		"head_tab_detil_date_in" => 'dtl_in',
	        		"head_tab_detil_date_out" => 'del_ext_from_date',
	        		"head_tab_detil_date_out_old" => 'dtl_out',
	        		"head_status" => "del_status",
	        		"head_primery" => "del_id",
	        		"head_forigen" => "hdr_del_id",
	        		"head_no" => "del_no",
	        		"head_split" => "del_split",
	        		"head_by" => "del_create_by",
	        		"head_date" => "del_date",
	        		"head_branch" => "del_branch_id",
	        		"head_branch_code" => "del_branch_code",
	        		"head_cust" => "del_cust_id",
	        		"head_trade" => "del_trade_type",
	        		"head_terminal_code" => "del_terminal_code",
	        		"head_terminal_name" => "del_terminal_name",
	        		"head_pbm_id" => null,
	        		"head_pbm_name" => null,
	        		"head_shipping_agent_id" => null,
	        		"head_shipping_agent_name" => null,
	        		"head_vessel_code" => "del_vessel_code",
	        		"head_vessel_name" => "del_vessel_name"
	        	]
	        ];

	        return $requst_config[$input];
	    }
	// BTN
}
