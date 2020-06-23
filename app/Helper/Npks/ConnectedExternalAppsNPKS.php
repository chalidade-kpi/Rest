<?php

namespace App\Helper\Npks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\OmUster\TxHdrNota;
use App\Helper\Npks\FunctTOS;

class ConnectedExternalAppsNPKS{
		public static function sendRequestToExtJsonMet($arr){
	        $client = new Client();
	        $options= array(
	          'auth' => [
	            $arr['user'],
	            $arr['pass']
	          ],
	          'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
	          'body' => $arr['json'],
	          "debug" => false
	        );
			try {
	          $res = $client->post($arr['target'], $options);
	        } catch (ClientException $e) {
	          $error = $e->getRequest() . "\n";
	          if ($e->hasResponse()) {
	            $error .= $e->getResponse() . "\n";
	          }
	          return ["Success"=>false, "request" => $arr, "response" => $error];
	        }
	        $res = json_decode($res->getBody()->getContents(), true);
	        return ["Success"=>true, "request" => $arr, "response" => $res];
		}

		public static function getVesselNpks($input){
			$json = '
			{
				"getVesselNpksRequest": {
					"esbHeader": {
						"internalId": "",
			        	"externalId": "",
			        	"timestamp": "",
			        	"responseTimestamp": "",
			        	"responseCode": "",
			        	"responseMessage": ""
						},
						"esbBody":   {
							"vessel":"'.$input['query'].'",
							"branchId":"'.$input['branch_id'].'",
							"branchCode":"'.$input['branch_code'].'"
							},
						"esbSecurity": {
							"orgId":"",
							"batchSourceId":"",
							"lastUpdateLogin":"",
							"userId":"",
							"respId":"",
							"ledgerId":"",
							"respApplId":"",
							"batchSourceName":"",
							"category":""
						}
					}
			}';

			$json = json_encode(json_decode($json,true));
			$res = static::sendRequestToExtJsonMet([
	        	"user" => config('endpoint.esbGetVesselNpks.user'),
	        	"pass" => config('endpoint.esbGetVesselNpks.pass'),
	        	"target" => config('endpoint.esbGetVesselNpks.target'),
	        	"json" => $json
	        ]);
			$vesel = $res['response']['getVesselNpksResponse']['esbBody']['results'];

			$result = [];
			foreach ($vesel as $query) {
				$query = (object)$query;
				$result[] = [
					'vessel' => $query->vessel,
					'voyageIn' => $query->voyageIn,
					'voyageOut' => $query->voyageOut,
					'ata' => (empty($query->ata)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->ata)->format('Y-m-d H:i'),
					'atd' => (empty($query->atd)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->atd)->format('Y-m-d H:i'),
					'atb' => (empty($query->atb)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->atb)->format('Y-m-d H:i'),
					'eta' => (empty($query->eta)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->eta)->format('Y-m-d H:i'),
					'etd' => (empty($query->etd)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->etd)->format('Y-m-d H:i'),
					'etb' => (empty($query->etb)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->etb)->format('Y-m-d H:i'),
					'openStack' => (empty($query->openStack)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->openStack)->format('Y-m-d H:i'),
					'closingTime' => (empty($query->closingTime)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->closingTime)->format('Y-m-d H:i'),
					'closingTimeDoc' => (empty($query->closingTimeDoc)) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->closingTimeDoc)->format('Y-m-d H:i'),
					'voyage' => $query->voyage,
					'idUkkSimop' => (empty($query->idUkkSimop)) ? null : $query->idUkkSimop,
					'idKade' => (empty($query->idKade)) ? null : $query->idKade,
					'kadeName' => (empty($query->kadeName)) ? null : $query->kadeName,
					'terminalCode' => (empty($query->terminalCode)) ? null : $query->idKade,
					'ibisTerminalCode' => (empty($query->ibisTerminalCode)) ? null : $query->idKade,
					'active' => (empty($query->active)) ? null : $query->idKade,
					'idVsbVoyage' => $query->idVsbVoyage,
					'vesselCode'=> $query->vesselCode
				];
			}
			return ["result"=>$result, "count"=>count($result)];
		}

		public static function getUpdateRename() {
			$json = '
		  {
		    "action" : "generateRename"
		  }';

		  $json = base64_encode(json_encode(json_decode($json,true)));
		  $json = '
		    {
		        "repoGetRequest": {
		            "esbHeader": {
		                "internalId": "",
		                "externalId": "",
		                "timestamp": "",
		                "responseTimestamp": "",
		                "responseCode": "",
		                "responseMessage": ""
		            },
		            "esbBody": {
		                "request": "'.$json.'"
		            },
		            "esbSecurity": {
		                "orgId": "",
		                "batchSourceId": "",
		                "lastUpdateLogin": "",
		                "userId": "",
		                "respId": "",
		                "ledgerId": "",
		                "respAppId": "",
		                "batchSourceName": ""
		            }
		        }
		    }
		      ';
		  $json = json_encode(json_decode($json,true));
		  $arr = [
		          "user"		 	=> config('endpoint.tosGetPLG.user'),
		          "pass" 		 	=> config('endpoint.tosGetPLG.pass'),
		          "target" 	 	=> config('endpoint.tosGetPLG.target'),
		          "json" 		 	=> $json
		        ];
		  $res 							 	= static::sendRequestToExtJsonMet($arr);
		  $res				 			 	= FunctTOS::decodeResultAftrSendToTosNPKS($res, 'repoGet');

			if (empty($res["result"]["result"])) {
				return "TX_RENAME Up to Date";
			}

			// return $res["result"]["result"];

			foreach ($res["result"]["result"] as $result) {
					if (empty($result["RENAMED_CONT"])) {
						$error[] = $result["RENAMED_CONT_OLD"];
					} else {
					// For TS_CONTAINER
					$tsContainer 	 		= DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT"])->first();
					// Rename Not Exist
					if (empty($tsContainer->cont_no)) {
						$updateData 		= [
							"CONT_NO" 		=> $result["RENAMED_CONT"],
						];
						$updateContOld 	= DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT_OLD"])->update($updateData);
						$updateHistOld  = DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where('NO_CONTAINER', $result["RENAMED_CONT_OLD"])->update(["NO_CONTAINER"=>$result["RENAMED_CONT"]]);
					} else {
						$tsContainerOld = DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT_OLD"])->first();
						$counter 				= $tsContainer->cont_counter + 1;
						$contLocOld 		= $tsContainerOld->cont_location;
						$dataUpdate 		= [
							"CONT_COUNTER"  => $counter,
							"CONT_LOCATON" 	=> $contLocOld,
						];
						$dataUpdateHist = [
							"COUNTER"  		 => $counter,
							"NO_CONTAINER" => $result["RENAMED_CONT"]
						];
						$updateTsCont 	= DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT"])->update($dataUpdate);
						$updateHist		  = DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where('NO_CONTAINER', $result["RENAMED_CONT_OLD"])->update($dataUpdateHist);

					}

					// FOR REQUEST
					$request = [
						"TX_DTL_REC" 		=> "REC_DTL_CONT",
						"TX_DTL_STUFF" 	=> "STUFF_DTL_CONT",
						"TX_DTL_PLUG" 	=> "PLUG_DTL_CONT",
						"TX_DTL_FUMI" 	=> "FUMI_DTL_CONT",
						"TX_DTL_STRIPP" => "STRIPP_DTL_CONT",
						"TX_DTL_TL"  		=> "TL_DTL_CONT"
					];

					foreach ($request as $key => $value) {
					  $field = strtolower($value);
						$getRequest 		= DB::connection('omuster')->table($key)->where($value, $result["RENAMED_CONT_OLD"])->first();
						if (!empty($getRequest->$field)) {
							$updateReq 			 = [
								"$value" => $result["RENAMED_CONT"]
							];
							$updateReqDtlCont = DB::connection('omuster')->table($key)->where($value, $result["RENAMED_CONT_OLD"])->update($updateReq);
						}
					}

					//Update Repo
					$json = '
				  {
				    "action" : "getUpateRename",
						"header" : {
						 "BRANCH_ID" : "'.$result["RENAMED_BRANCH_ID"].'",
						 "CONT_NO" : "'.$result["RENAMED_CONT_OLD"].'"
						}
				  }';
					$json = base64_encode(json_encode(json_decode($json,true)));
	        $json = '
					{
					    "repoPostRequest": {
					        "esbHeader": {
					            "internalId": "",
					            "externalId": "",
					            "timestamp": "",
					            "responseTimestamp": "",
					            "responseCode": "",
					            "responseMessage": ""
					        },
					        "esbBody": {
					            "request": "'.$json.'"
					        },
					        "esbSecurity": {
					            "orgId": "",
					            "batchSourceId": "",
					            "lastUpdateLogin": "",
					            "userId": "",
					            "respId": "",
					            "ledgerId": "",
					            "respAppId": "",
					            "batchSourceName": ""
					        }
					    }
					}
	        ';
	        $opt = [
	        	"user" => config('endpoint.tosPostPLG.user'),
	        	"pass" => config('endpoint.tosPostPLG.pass'),
	        	"target" => config('endpoint.tosPostPLG.target'),
	        	"json" => json_encode(json_decode($json,true))
	        ];
	        $res = static::sendRequestToExtJsonMet($opt);
	        $res = FunctTOS::decodeResultAftrSendToTosNPKS($res, 'repoPost');
					$containerUpdate[] = $res["result"]["CONT_NO"];
				}
			}

			return ["Success" => $containerUpdate, "Rename Not Found"=>$error];
		}

		public static function getUpdatePlacement(){
		  $all 						 = [];
		  $det 						 = DB::connection('omuster')->table('TX_DTL_REC')->where('REC_FL_REAL', "2")->get();
		  foreach ($det as $lista) {
		    $newDt 				 = [];
		    foreach ($lista as $key => $value) {
		      $newDt[$key] = $value;
		    }

		    $hdr 		 			 = DB::connection('omuster')->table('TX_HDR_REC')->where('REC_ID', $lista->rec_hdr_id)->get();
		    foreach ($hdr as $listS) {
		      foreach ($listS as $key => $value) {
		        $newDt[$key] = $value;
		      }
		    }

		      $all[] 				= $newDt;
		    }

		  $dtl 							= '';
		  $arrdtl 					= [];

		  foreach ($all as $list) {
		    $dtl .= '
		    {
		      "NO_CONTAINER"	: "'.$list["rec_dtl_cont"].'",
		      "NO_REQUEST"		: "'.$list["rec_no"].'",
		      "BRANCH_ID"			: "'.$list["rec_branch_id"].'"
		    },';
		    $arrdtlset = [
		      "NO_CONTAINER" 	=> $list["rec_dtl_cont"],
		      "NO_REQUEST"	  => $list["rec_no"],
		      "BRANCH_ID" 		=> $list["rec_branch_id"]
		    ];
		    $arrdtl[]  = $arrdtlset;
		  }

		  $head = [
		    "action" 	=> "generatePlacement",
		    "data" 		=> $arrdtl
		  ];

		  $dtl 	= substr($dtl, 0,-1);
		  $json = '
		  {
		    "action" : "generatePlacement",
		    "data": ['.$dtl.']
		  }';

		  $json = base64_encode(json_encode(json_decode($json,true)));
		  $json = '
		    {
		        "repoGetRequest": {
		            "esbHeader": {
		                "internalId": "",
		                "externalId": "",
		                "timestamp": "",
		                "responseTimestamp": "",
		                "responseCode": "",
		                "responseMessage": ""
		            },
		            "esbBody": {
		                "request": "'.$json.'"
		            },
		            "esbSecurity": {
		                "orgId": "",
		                "batchSourceId": "",
		                "lastUpdateLogin": "",
		                "userId": "",
		                "respId": "",
		                "ledgerId": "",
		                "respAppId": "",
		                "batchSourceName": ""
		            }
		        }
		    }
		      ';
		  $json = json_encode(json_decode($json,true));
		  $arr = [
		          "user"		 	=> config('endpoint.tosGetPLG.user'),
		          "pass" 		 	=> config('endpoint.tosGetPLG.pass'),
		          "target" 	 	=> config('endpoint.tosGetPLG.target'),
		          "json" 		 	=> $json
		        ];
		  $res 							 	= static::sendRequestToExtJsonMet($arr);
		  $res				 			 	= FunctTOS::decodeResultAftrSendToTosNPKS($res, 'repoGet');

			if (empty($res["result"]["result"])) {
				return "Placement is uptodate";
			}
			static::storeTxServices($json,json_decode($json,true)["repoGetRequest"]["esbBody"]["request"],$res["result"]["result"]);
			// return $res["result"]["result"];

		  foreach ($res["result"]["result"] as $listR) {
		    $findCont 				= [
		      "CONT_NO" 			=> $listR["NO_CONTAINER"],
		      "CONT_LOCATION" => "GATI",
					"BRANCH_ID"			=> $listR["BRANCH_ID"]
		    ];


		   $findPlacement 		= [
		      "NO_REQUEST" 		=> $listR["NO_REQUEST"],
		      "NO_CONTAINER" 	=> $listR["NO_CONTAINER"]
		    ];

				$findHistory 			= [
		      "NO_REQUEST" 		=> $listR["NO_REQUEST"],
		      "NO_CONTAINER" 	=> $listR["NO_CONTAINER"],
					"KEGIATAN"			=> "12"
		    ];

		    $tsContainer 		 	= DB::connection('omuster')->table('TS_CONTAINER')->where($findCont)->get();

				// return $tsContainer;
		                        DB::connection('omuster')->table('TS_CONTAINER')->where($findCont)->update(['CONT_LOCATION'=>"IN_YARD", 'CONT_ISACTIVE' => "N"]);
		    $placementID 			= DB::connection('omuster')->table('DUAL')->select('SEQ_TX_PLACEMENT.NEXTVAL')->get();

				if (empty($tsContainer)) {
					$container[] =  $listR["NO_CONTAINER"]." Already In Yard";
				} else {
					$container[] =  $listR["NO_CONTAINER"]." Change To In Yard";
					$storePlacement  	= [
						"PLACEMENT_ID"	=> $placementID[0]->nextval,
						"NO_REQUEST"		=> $listR["NO_REQUEST"],
						"NO_CONTAINER"	=> $listR["NO_CONTAINER"],
						"YBC_SLOT"			=> $listR["YBC_SLOT"],
						"YBC_ROW"				=> $listR["YBC_ROW"],
						"YBC_BLOCK_ID"	=> $listR["YBC_BLOCK_ID"],
						"TIER"					=> $listR["TIER"],
						"ID_YARD"				=> $listR["ID_YARD"],
						"ID_USER"				=> $listR["ID_USER"],
						"CONT_STATUS"		=> $listR["CONT_STATUS"],
						"TGL_PLACEMENT"	=> date('Y-m-d h:i:s', strtotime($listR['PLACEMENT_DATE'])),
						"BRANCH_ID"			=> $listR["BRANCH_ID"],
						"CONT_COUNTER"	=> $tsContainer[0]->cont_counter
					];

					$storeHistory 		= [
						"NO_CONTAINER" 	=> $listR["NO_CONTAINER"],
						"NO_REQUEST"		=> $listR["NO_REQUEST"],
						"KEGIATAN"			=> "12",
						"HISTORY_DATE"		=> date('Y-m-d h:i:s', strtotime($listR['PLACEMENT_DATE'])),
						"ID_USER"				=> $listR["ID_USER"],
						"ID_YARD"				=> $listR["ID_YARD"],
						"STATUS_CONT"		=> $listR["CONT_STATUS"],
						"VVD_ID"				=> "",
						"COUNTER"				=> $tsContainer[0]->cont_counter,
						"SUB_COUNTER"		=> "",
						"WHY"						=> ""
					];

					$headerID 				= DB::connection('omuster')->table('TX_HDR_REC')->where('REC_NO', $listR["NO_REQUEST"])->first();

					$updateFlReal 		= DB::connection('omuster')
					->table("TX_DTL_REC")
					->where('REC_DTL_CONT', $listR["NO_CONTAINER"])
					->where('REC_HDR_ID', $headerID->rec_id)
					->update(["rec_dtl_real_date"=>date('Y-m-d h:i:s', strtotime($listR['PLACEMENT_DATE'])), "rec_fl_real"=>"3"]);

					DB::connection('omuster')->table('TX_PLACEMENT')->insert($storePlacement);

					$cekHistory 			= DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where($findHistory)->first();
					if (empty($cekHistory)) {
						DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->insert($storeHistory);
					} else {
						DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where($findHistory)->update($storeHistory);
					}
				}
		  }
			return $container;
		}

		public static function storeTxServices($json, $jsonRequest, $jsonResponse) {
			$request 									= json_decode($json,true);
			$service["request"] 			= base64_decode($jsonRequest);
			$service["response"]			= json_encode($jsonResponse);

			$storeService 						= [
				"SERVICES_ID"						=> "",
				"SERVICES_BRANCH_ID"		=> "4",
				"SERVICES_TIME"					=> date('Y-m-d H:i'),
				"SERVICES_REQUEST"			=> $service["request"],
				"SERVICES_RESPONSE"			=> $service["response"]
			];

			$insert 									= DB::connection('omuster')->table('TX_SERVICES')->insert($storeService);
		}

		public static function flagRealisationRequest(){
			$res = [];
			$nota = DB::connection('mdm')->table('TS_NOTA')->where('FLAG_STATUS','Y')->whereNotNull('API_SET')->orderBy('nota_id', 'asc')->get();
			$nota_id_old = 0;
			foreach ($nota as $notaData) {
				if ($nota_id_old != $notaData->nota_id) {
					$config = json_decode($notaData->api_set, true);
					$hdr = DB::connection('omuster')->table($config['head_table'])->whereIn($config['head_status'], [3,10])->get();
					foreach ($hdr as $list) {
						$list = (array)$list;
						$cekNota = DB::connection('omuster')->table('TX_HDR_NOTA')->where('nota_req_no',$list[$config['head_no']])->first();
						if (
							( // jika cash maka cek nota harus ada dan dibayarkan
								!empty($cekNota)
								and $cekNota->nota_status == 3
								and $cekNota->nota_paid == 'Y'
								and  $list[$config['head_paymethod']] == 1
							) or ( // jika pihutang maka tidak wajib ada NOTA
								empty($cekNota)
								and  $list[$config['head_paymethod']] == 2
							)
						) {
							 $input = [
								"sceduler"=>true,
								"nota_id"=>$notaData->nota_id,
								"id"=>$list[$config['head_primery']]
							];
							$response = FunctTOS::getRealNPKS($input);

							$storeHistory = [
								"create_date" => \DB::raw("TO_DATE('".Carbon::now()->format('Y-m-d H:i:s')."', 'YYYY-MM-DD HH24:mi:ss')"),
								"action" => 'flagRealisationDtlRequest',
								"branch_id" => 4,
								"branch_code" => 'PLG',
								"json_request" => json_encode($input),
								"json_response" => json_encode($response),
								"create_name" => 'sceduler'
							];
							$res[] = $storeHistory;
							// static::storeHistory($storeHistory);
						}
						if ($list[$config['head_paymethod']] == 1) { // hanya utk cash
							$condition = [];
							$condition[$config['head_forigen']] = $list[$config['head_primery']];
							if (!empty($config['DTL_IS_ACTIVE'])) {
								$condition[$config['DTL_IS_ACTIVE']] = 'Y';
							}
							$dtl = DB::connection('omuster')->table($config['head_tab_detil'])->where($condition)->whereIn($config['DTL_FL_REAL'], $config['DTL_FL_REAL_S'])->get();
							if (count($dtl) == 0) {
								if ($list[$config['head_status']] == 3) {
									$upStHead = 5;
								}else if ($list[$config['head_status']] == 10){
									$upStHead = 11;
								}
								DB::connection('omuster')->table($config['head_table'])->where($config['head_primery'],$list[$config['head_primery']])->update([$config['head_status']=>$upStHead]);
								if ($config['DTL_IS_ACTIVE'] != null) {
									DB::connection('omuster')->table($config['head_tab_detil'])->where($config['head_forigen'],$list[$config['head_primery']])->update([
										$config['DTL_IS_ACTIVE'] => 'N'
									]);
								}
								$trackInpt = [
									"tab"=>$config['head_table'],
									"id"=>$list[$config['head_primery']],
									"update"=>[$config['head_status']=>$upStHead]
								];
								$res[] = $storeHistory;
								$storeHistory = [
									"create_date" => \DB::raw("TO_DATE('".Carbon::now()->format('Y-m-d H:i:s')."', 'YYYY-MM-DD HH24:mi:ss')"),
									"action" => 'flagRealisationHdrRequest',
									"branch_id" => 4,
									"branch_code" => 'PLG',
									"json_request" => json_encode($trackInpt),
									"json_response" => json_encode($trackInpt),
									"create_name" => 'sceduler'
								];
								// static::storeHistory($storeHistory);
							}
						}
					}
				}
				$nota_id_old = $notaData->nota_id;
			}
			return $res;
		}

		public static function realisationByHit($input,$request){
			$branch_id 	 = $input["branch_id"];
			$noRequest 	 = $input["no_req"];
			$activity 	 = [
				"rec" 			=> "1",
				"del" 			=> "2",
				"stuff" 		=> "3",
				"strip" 		=> "4",
				"fumi" 			=> "5",
				"plug" 			=> "6",
				"tl"				=> "20",
				"rec_cargo" => "21",
				"del_cargo" => "22"
			];

			if ($input["branch_id"] == "4") {
				$activity["stuff"] = "7";
				$activity["strip"] = "10";
			}

			$nota_id 			= $activity[$input["activity"]];
			$nota_cond 		= [
				"FLAG_STATUS" => 'Y',
				"NOTA_ID" => $nota_id,
				"BRANCH_ID" => $input["branch_id"]
				// "BRANCH_CODE" => $input["branch_code"]
			];

			$res = [];
			$nota = DB::connection('mdm')->table('TS_NOTA')->where($nota_cond)->whereNotNull('API_SET')->orderBy('nota_id', 'asc')->get();

			// Tambahan Branch Code
			$notaConfig = DB::connection('mdm')->table('TS_NOTA')->where($nota_cond)->whereNotNull('API_SET')->orderBy('nota_id', 'asc')->first();
			$configNota = json_decode($notaConfig->api_set, true);
			$getHdr = DB::connection('omuster')->table($configNota["head_table"])->where($configNota["head_no"], $input["no_req"])->first();
			$getHdr = json_decode(json_encode($getHdr),TRUE);
			$branch_code = $getHdr[$configNota['head_branch_code']];
			//

			$nota_id_old = 0;
			foreach ($nota as $notaData) {
				if ($nota_id_old != $notaData->nota_id) {
					$config = json_decode($notaData->api_set, true);
					$hdr = DB::connection('omuster')->table($config['head_table'])->where($config['head_no'], $input["no_req"])->get();
					foreach ($hdr as $list) {
						$list = (array)$list;
						$cekNota = DB::connection('omuster')->table('TX_HDR_NOTA')->where('nota_req_no',$list[$config['head_no']])->first();
						if (
							( // jika cash maka cek nota harus ada dan dibayarkan
								!empty($cekNota)
								and $cekNota->nota_status == 3
								and $cekNota->nota_paid == 'Y'
								and  $list[$config['head_paymethod']] == 1
							) or ( // jika pihutang maka tidak wajib ada NOTA
								empty($cekNota)
								and  $list[$config['head_paymethod']] == 2
							)
						) {
							 $input = [
								"sceduler"=>true,
								"nota_id"=>$notaData->nota_id,
								"no_cont" => $input["no_cont"],
								"id"=>$list[$config['head_primery']]
							];

							$response = FunctTOS::getRealNPKS($input);

							$storeHistory = [
								"create_date" => \DB::raw("TO_DATE('".Carbon::now()->format('Y-m-d H:i:s')."', 'YYYY-MM-DD HH24:mi:ss')"),
								"action" => 'flagRealisationDtlRequest',
								"branch_id" => 4,
								"branch_code" => 'PLG',
								"json_request" => json_encode($input),
								"json_response" => json_encode($response),
								"create_name" => 'sceduler'
							];
							$res[] = $storeHistory;
							// static::storeHistory($storeHistory);
						}
						if ($list[$config['head_paymethod']] == 1) { // hanya utk cash
							$condition = [];
							$condition[$config['DTL_BL']] = $input["no_cont"];
							$condition[$config['head_forigen']] = $list[$config['head_primery']];
							if (!empty($config['DTL_IS_ACTIVE'])) {
								$condition[$config['DTL_IS_ACTIVE']] = 'Y';
							}
							$dtl = DB::connection('omuster')->table($config['head_tab_detil'])->where($condition)->whereIn($config['DTL_FL_REAL'], $config['DTL_FL_REAL_S'])->get();
							if (count($dtl) == 0) {
								if ($list[$config['head_status']] == 3) {
									$upStHead = 5;
								}else if ($list[$config['head_status']] == 10){
									$upStHead = 11;
								} else {
									$upStHead = $list[$config['head_status']];
								}

								DB::connection('omuster')->table($config['head_table'])->where($config['head_primery'],$list[$config['head_primery']])->update([$config['head_status']=>$upStHead]);
								if ($config['DTL_IS_ACTIVE'] != null) {
									DB::connection('omuster')->table($config['head_tab_detil'])->where($config['head_forigen'],$list[$config['head_primery']])->update([
										$config['DTL_IS_ACTIVE'] => 'N'
									]);
								}
								$trackInpt = [
									"tab"=>$config['head_table'],
									"id"=>$list[$config['head_primery']],
									"update"=>[$config['head_status']=>$upStHead]
								];
								$res[] = $storeHistory;
								$storeHistory = [
									"create_date" => \DB::raw("TO_DATE('".Carbon::now()->format('Y-m-d H:i:s')."', 'YYYY-MM-DD HH24:mi:ss')"),
									"action" => 'flagRealisationHdrRequest',
									"branch_id" => 4,
									"branch_code" => 'PLG',
									"json_request" => json_encode($trackInpt),
									"json_response" => json_encode($trackInpt),
									"create_name" => 'sceduler'
								];
								// static::storeHistory($storeHistory);
							}
						}
					}
				}
				$nota_id_old = $notaData->nota_id;
			}

			// Update CONT_ISACTIVE to N After Reject
				$reqCont 	 = $input["no_cont"];
				$cekTsCont = DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $reqCont)->first();
				if (in_array($nota_id,[1,20,10,7])) {
					if (isset($cekTsCont->cont_location) AND $cekTsCont->cont_location != "GATI" || isset($cekTsCont->cont_location) AND  $cekTsCont->cont_location == "IN_YARD") {
						DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $reqCont)->update(["CONT_ISACTIVE"=>"N"]);
					}
				} else if (in_array($nota_id,[2,3,4,5,6])) {
					if ($cekTsCont->cont_location == "IN_YARD") {
						DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $reqCont)->update(["CONT_ISACTIVE"=>"N"]);
					}
				}
			// End Check container

			$config 		= json_decode($nota[0]->api_set,TRUE);
			if (count($config["kegiatan_real"]) > 1) {
				$kegiatan = $config["kegiatan_real"][1];
			} else {
				$kegiatan = $config["kegiatan_real"];
			}

			$kegiatan 	= DB::connection('omuster')->table('TM_REFF')->where('REFF_ID', $kegiatan)->where('REFF_TR_ID', '12')->first();
			$activity 	= $kegiatan->reff_name;

			$data = [
				'NO_CONTAINER' => $reqCont,
				'NO_REQUEST' => $noRequest,
				'ACTIVITY' => $activity
			];

			$data = json_encode($data);
			$url = 'http://10.88.48.33:4001/updateRealiasi?branch='.$branch_id.'&branch_code='.$branch_code.'&data='.$data;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			return $res;
		}

		public static function placementByHit($input){
		   $dtl = '
		    {
		      "NO_CONTAINER"	: "'.$input["no_cont"].'",
		      "NO_REQUEST"		: "'.$input["no_req"].'",
		      "BRANCH_ID"			: "'.$input["branch_id"].'"
		    }';

		  $json = '
		  {
		    "action" : "generatePlacement",
		    "data": ['.$dtl.']
		  }';

		  $json = base64_encode(json_encode(json_decode($json,true)));
		  $json = '
		    {
		        "repoGetRequest": {
		            "esbHeader": {
		                "internalId": "",
		                "externalId": "",
		                "timestamp": "",
		                "responseTimestamp": "",
		                "responseCode": "",
		                "responseMessage": ""
		            },
		            "esbBody": {
		                "request": "'.$json.'"
		            },
		            "esbSecurity": {
		                "orgId": "",
		                "batchSourceId": "",
		                "lastUpdateLogin": "",
		                "userId": "",
		                "respId": "",
		                "ledgerId": "",
		                "respAppId": "",
		                "batchSourceName": ""
		            }
		        }
		    }
		      ';
		  $json = json_encode(json_decode($json,true));
		  $arr = [
		          "user"		 	=> config('endpoint.tosGetPLG.user'),
		          "pass" 		 	=> config('endpoint.tosGetPLG.pass'),
		          "target" 	 	=> config('endpoint.tosGetPLG.target'),
		          "json" 		 	=> $json
		        ];
		  $res 							 	= static::sendRequestToExtJsonMet($arr);
		  $res				 			 	= FunctTOS::decodeResultAftrSendToTosNPKS($res, 'repoGet');

			if (empty($res["result"]["result"])) {
				return "Placement is uptodate";
			}
			static::storeTxServices($json,json_decode($json,true)["repoGetRequest"]["esbBody"]["request"],$res["result"]["result"]);
			// return $res["result"]["result"];

		  foreach ($res["result"]["result"] as $listR) {
		    $findCont 				= [
		      "CONT_NO" 			=> $listR["NO_CONTAINER"],
		      "CONT_LOCATION" => "GATI",
					"BRANCH_ID"			=> $listR["BRANCH_ID"]
		    ];


		   $findPlacement 		= [
		      "NO_REQUEST" 		=> $listR["NO_REQUEST"],
		      "NO_CONTAINER" 	=> $listR["NO_CONTAINER"]
		    ];

				$findHistory 			= [
		      "NO_REQUEST" 		=> $listR["NO_REQUEST"],
		      "NO_CONTAINER" 	=> $listR["NO_CONTAINER"],
					"KEGIATAN"			=> "12"
		    ];

		    $tsContainer 		 	= DB::connection('omuster')->table('TS_CONTAINER')->where($findCont)->get();

				// return $tsContainer;
				$cekRequest = substr($listR["NO_REQUEST"],0,3);
				if ($cekRequest == "REC") {
					DB::connection('omuster')->table('TS_CONTAINER')->where($findCont)->update(['CONT_LOCATION'=>"IN_YARD", 'CONT_ISACTIVE' => "N"]);
				}

		    $placementID 			= DB::connection('omuster')->table('DUAL')->select('SEQ_TX_PLACEMENT.NEXTVAL')->get();

				if (empty($tsContainer)) {
					$container[] =  $listR["NO_CONTAINER"]." Already In Yard";
				} else {
					$container[] =  $listR["NO_CONTAINER"]." Change To In Yard";
					$storePlacement  	= [
						"PLACEMENT_ID"	=> $placementID[0]->nextval,
						"NO_REQUEST"		=> $listR["NO_REQUEST"],
						"NO_CONTAINER"	=> $listR["NO_CONTAINER"],
						"YBC_SLOT"			=> $listR["YBC_SLOT"],
						"YBC_ROW"				=> $listR["YBC_ROW"],
						"YBC_BLOCK_ID"	=> $listR["YBC_BLOCK_ID"],
						"TIER"					=> $listR["TIER"],
						"ID_YARD"				=> $listR["ID_YARD"],
						"ID_USER"				=> $listR["ID_USER"],
						"CONT_STATUS"		=> $listR["CONT_STATUS"],
						"TGL_PLACEMENT"	=> date('Y-m-d h:i:s', strtotime($listR['PLACEMENT_DATE'])),
						"BRANCH_ID"			=> $listR["BRANCH_ID"],
						"CONT_COUNTER"	=> $tsContainer[0]->cont_counter
					];

					$storeHistory 		= [
						"NO_CONTAINER" 	=> $listR["NO_CONTAINER"],
						"NO_REQUEST"		=> $listR["NO_REQUEST"],
						"KEGIATAN"			=> "12",
						"HISTORY_DATE"		=> date('Y-m-d h:i:s', strtotime($listR['PLACEMENT_DATE'])),
						"ID_USER"				=> $listR["ID_USER"],
						"ID_YARD"				=> $listR["ID_YARD"],
						"STATUS_CONT"		=> $listR["CONT_STATUS"],
						"VVD_ID"				=> "",
						"COUNTER"				=> $tsContainer[0]->cont_counter,
						"SUB_COUNTER"		=> "",
						"WHY"						=> ""
					];

					$headerID 				= DB::connection('omuster')->table('TX_HDR_REC')->where('REC_NO', $listR["NO_REQUEST"])->first();

					$updateFlReal 		= DB::connection('omuster')
					->table("TX_DTL_REC")
					->where('REC_DTL_CONT', $listR["NO_CONTAINER"])
					->where('REC_HDR_ID', $headerID->rec_id)
					->update(["rec_dtl_real_date"=>date('Y-m-d h:i:s', strtotime($listR['PLACEMENT_DATE'])), "rec_fl_real"=>"3"]);

					DB::connection('omuster')->table('TX_PLACEMENT')->insert($storePlacement);

					$cekHistory 			= DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where($findHistory)->first();
					if (empty($cekHistory)) {
						DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->insert($storeHistory);
					} else {
						DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where($findHistory)->update($storeHistory);
					}
				}
		  }
			return $container;
		}

		public static function renameByHit($input) {
			$json = '
		  {
		    "action" 		: "generateRename",
				"no_cont" 	: "'.$input["no_cont"].'",
				"no_req" 		: "'.$input["no_req"].'",
				"branch_id" : "'.$input["branch_id"].'"
		  }';

		  $json = base64_encode(json_encode(json_decode($json,true)));
		  $json = '
		    {
		        "repoGetRequest": {
		            "esbHeader": {
		                "internalId": "",
		                "externalId": "",
		                "timestamp": "",
		                "responseTimestamp": "",
		                "responseCode": "",
		                "responseMessage": ""
		            },
		            "esbBody": {
		                "request": "'.$json.'"
		            },
		            "esbSecurity": {
		                "orgId": "",
		                "batchSourceId": "",
		                "lastUpdateLogin": "",
		                "userId": "",
		                "respId": "",
		                "ledgerId": "",
		                "respAppId": "",
		                "batchSourceName": ""
		            }
		        }
		    }
		      ';
		  $json = json_encode(json_decode($json,true));
		  $arr = [
		          "user"		 	=> config('endpoint.tosGetPLG.user'),
		          "pass" 		 	=> config('endpoint.tosGetPLG.pass'),
		          "target" 	 	=> config('endpoint.tosGetPLG.target'),
		          "json" 		 	=> $json
		        ];
		  $res 							 	= static::sendRequestToExtJsonMet($arr);
		  $res				 			 	= FunctTOS::decodeResultAftrSendToTosNPKS($res, 'repoGet');

			if (empty($res["result"]["result"])) {
				return "TX_RENAME Up to Date";
			}

			// return $res["result"]["result"];

			foreach ($res["result"]["result"] as $result) {
					if (empty($result["RENAMED_CONT"])) {
						$error[] = $result["RENAMED_CONT_OLD"];
					} else {
					// For TS_CONTAINER
					$tsContainer 	 		= DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT"])->first();
					// Rename Not Exist
					if (empty($tsContainer->cont_no)) {
						$updateData 		= [
							"CONT_NO" 		=> $result["RENAMED_CONT"],
						];
						$updateContOld 	= DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT_OLD"])->update($updateData);
						$updateHistOld  = DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where('NO_CONTAINER', $result["RENAMED_CONT_OLD"])->update(["NO_CONTAINER"=>$result["RENAMED_CONT"]]);
					} else {
						$tsContainerOld = DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT_OLD"])->first();
						$counter 				= $tsContainer->cont_counter + 1;
						$contLocOld 		= $tsContainerOld->cont_location;
						$dataUpdate 		= [
							"CONT_COUNTER"  => $counter,
							"CONT_LOCATON" 	=> $contLocOld,
						];
						$dataUpdateHist = [
							"COUNTER"  		 => $counter,
							"NO_CONTAINER" => $result["RENAMED_CONT"]
						];
						$updateTsCont 	= DB::connection('omuster')->table('TS_CONTAINER')->where('CONT_NO', $result["RENAMED_CONT"])->update($dataUpdate);
						$updateHist		  = DB::connection('omuster')->table('TX_HISTORY_CONTAINER')->where('NO_CONTAINER', $result["RENAMED_CONT_OLD"])->update($dataUpdateHist);

					}

					// FOR REQUEST
					$request = [
						"TX_DTL_REC" 		=> "REC_DTL_CONT",
						"TX_DTL_STUFF" 	=> "STUFF_DTL_CONT",
						"TX_DTL_PLUG" 	=> "PLUG_DTL_CONT",
						"TX_DTL_FUMI" 	=> "FUMI_DTL_CONT",
						"TX_DTL_STRIPP" => "STRIPP_DTL_CONT",
						"TX_DTL_TL"  		=> "TL_DTL_CONT"
					];

					foreach ($request as $key => $value) {
					  $field = strtolower($value);
						$getRequest 		= DB::connection('omuster')->table($key)->where($value, $result["RENAMED_CONT_OLD"])->first();
						if (!empty($getRequest->$field)) {
							$updateReq 			 = [
								"$value" => $result["RENAMED_CONT"]
							];
							$updateReqDtlCont = DB::connection('omuster')->table($key)->where($value, $result["RENAMED_CONT_OLD"])->update($updateReq);
						}
					}

					//Update Repo
					$json = '
				  {
				    "action" : "getUpateRename",
						"header" : {
						 "BRANCH_ID" : "'.$result["RENAMED_BRANCH_ID"].'",
						 "CONT_NO" : "'.$result["RENAMED_CONT_OLD"].'"
						}
				  }';
					$json = base64_encode(json_encode(json_decode($json,true)));
	        $json = '
					{
					    "repoPostRequest": {
					        "esbHeader": {
					            "internalId": "",
					            "externalId": "",
					            "timestamp": "",
					            "responseTimestamp": "",
					            "responseCode": "",
					            "responseMessage": ""
					        },
					        "esbBody": {
					            "request": "'.$json.'"
					        },
					        "esbSecurity": {
					            "orgId": "",
					            "batchSourceId": "",
					            "lastUpdateLogin": "",
					            "userId": "",
					            "respId": "",
					            "ledgerId": "",
					            "respAppId": "",
					            "batchSourceName": ""
					        }
					    }
					}
	        ';
	        $opt = [
	        	"user" => config('endpoint.tosPostPLG.user'),
	        	"pass" => config('endpoint.tosPostPLG.pass'),
	        	"target" => config('endpoint.tosPostPLG.target'),
	        	"json" => json_encode(json_decode($json,true))
	        ];
	        $res = static::sendRequestToExtJsonMet($opt);
	        $res = FunctTOS::decodeResultAftrSendToTosNPKS($res, 'repoPost');
					$containerUpdate[] = $res["result"]["CONT_NO"];
				}
			}

			return ["Success" => $containerUpdate, "Rename Not Found"=>$error];
		}

		public static function storeHistory($inp){
			DB::connection('omuster')->table('TH_LOGS_API_STORE')->insert($inp);
		}

		public static function clearScheduler() {
	    $database    = DB::connection('omuster')->table('TM_USER')->where("USER_LOGIN","1")->get();
	    foreach ($database as $data) {
	      $time    = date('H:i:s',strtotime('+7 hour'));
	      $active  = intval(strtotime($data->user_active));
	      $now     = intval(strtotime($time));
	      $selisih = ($now - $active)/60;
	      if ($selisih >= 960) { // ditambahin awalnya 240
	        $user[] = [$data->user_name, $selisih];
	         DB::connection('omuster')->table('TM_USER')->where('USER_ID', $data->user_id)->update(["USER_LOGIN" => "", "USER_ACTIVE" => "", "API_TOKEN" => ""]);
	      }
	    }
	  }
}
