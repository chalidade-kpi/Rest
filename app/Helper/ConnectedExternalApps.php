<?php

namespace App\Helper;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\OmCargo\TxHdrBm;
use Illuminate\Support\Facades\DB;

class ConnectedExternalApps{

  public static function vessel_index($input) {
    $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/trackingVessel";
    $string_json = '{
      "trackingVesselRequest": {
        "esbHeader": {
          "externalId": "5275682735",
          "timestamp": "YYYYMMDD HH:Mi:SS"
          },
          "esbBody": {
            "vesselName": "'.$input['query'].'",
            "ibisTerminalCode": "'.$input['ibis_terminal_code'].'"
          }
        }
      }';

      $username="npk_billing";
      $password ="npk_billing";
      $client = new Client();
      $options= array(
        'auth' => [
          $username,
          $password
        ],
        'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
        'body' => $string_json,
        "debug" => false
      );
      try {
        $res = $client->post($endpoint_url, $options);
      } catch (ClientException $e) {
        echo $e->getRequest() . "\n";
        if ($e->hasResponse()) {
          echo $e->getResponse() . "\n";
        }
      }

      $results = json_decode($res->getBody()->getContents());
      $data = $results->esbBody->results;

      $array_map = array_map(function($query) {
        return [
          'vessel' => $query->vessel,
          'voyageIn' => $query->voyageIn,
          'voyageOut' => $query->voyageOut,
          'ata' => ($query->ata == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->ata)->format('Y-m-d H:i'),
          'atd' => ($query->atd == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->atd)->format('Y-m-d H:i'),
          'atb' => ($query->atb == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->atb)->format('Y-m-d H:i'),
          'eta' => \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->eta)->format('Y-m-d H:i'),
          'etd' => \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->etd)->format('Y-m-d H:i'),
          'etb' => ($query->etb == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->etb)->format('Y-m-d H:i'),
          'openStack' => ($query->openStack == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->openStack)->format('Y-m-d H:i'),
          'closingTime' => ($query->closingTime == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->closingTime)->format('Y-m-d H:i'),
          'closingTimeDoc' => ($query->closingTimeDoc == null) ? null : \Carbon\Carbon::createFromFormat("d-m-Y H:i", $query->closingTimeDoc)->format('Y-m-d H:i'),
          'voyage' => $query->voyage,
          'idKade' => $query->idKade,
          'terminalCode' => $query->terminalCode,
          'ibisTerminalCode' => $query->ibisTerminalCode,
          'active' => $query->active,
          'idVsbVoyage' => $query->idVsbVoyage,
          'vesselCode'=> $query->vesselCode
        ];
      }, (array) $data);

      return ["result"=>$array_map, "count"=>count($array_map)];
  }

  public static function peb_index($input) {
    $date = \Carbon\Carbon::createFromFormat("Ymd", str_replace('-','',$input['date_peb']))->format('dmY');
    $endpoint_url="http://10.88.48.57:5555/restv2/tpsOnline/searchPEB";
    $string_json = '{
      "searchPEBRequest": {
        "esbHeader": {
          "externalId": "5275682735",
          "timestamp": "YYYYMMDD HH:Mi:SS"
          },
          "esbBody": {
            "username": "PLDB",
            "password": "PLDB12345",
            "noPEB": "'.$input['no_peb'].'",
            "tglPEB": "'.$date.'",
            "npwp": "'.$input['npwp'].'"
          }
        }
    }';

    $username="npk_billing";
    $password ="npk_billing";
    $client = new Client();
    $options= array(
      'auth' => [
        $username,
        $password
      ],
      'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
      'body' => $string_json,
      "debug" => false
    );
    try {
      $res = $client->post($endpoint_url, $options);
    } catch (ClientException $e) {
      echo $e->getRequest() . "\n";
      if ($e->hasResponse()) {
        echo $e->getResponse() . "\n";
      }
    }

    $body = json_decode($res->getBody()->getContents());

    return ['pebListResponse' => $body->searchPEBInterfaceResponse];
  }

	public static function realTos($input){
		$count = DB::connection('omcargo')->table('TX_HDR_REALISASI')->where('REAL_REQ_NO', $input['req_no'])->count();
    if ($count > 0) {
      return ['result' => "Fail, realisation has been created!", "Success" => false];
    }
    $req = TxHdrBm::where('BM_NO', $input['req_no'])->first();
    if (empty($req)) {
      return ['result' => "Fail, request not found!", "Success" => false];
    }
    $ckp = DB::connection('omcargo')->table('TX_DTL_BM')->where('hdr_bm_id', $req->bm_id)->where('dtl_pkg_id', 4)->get();

    if (count($ckp) > 0) {
      foreach ($ckp as $list) {
        DB::connection('omcargo')->table('TX_REAL_TOS')->where('idvsb', $req->bm_vvd_id)->where('bl_no', $list->dtl_bm_bl)->delete();
        $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/searchRealisasi";
        $string_json = '{
          "searchRealisasiRequest": {
            "esbHeader": { },
              "esbBody": {
                "vvd": "'.$req->bm_vvd_id.'",
                "noblss": "'.$list->dtl_bm_bl.'"
              }
            }
          }';

        $username="npk_billing";
        $password ="npk_billing";
        $client = new Client();
        $options= array(
          'auth' => [
            $username,
            $password
          ],
          'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
          'body' => $string_json,
          "debug" => false
        );
        try {
          $res = $client->post($endpoint_url, $options);
        } catch (ClientException $e) {
          return $e->getResponse();
        }

        $response = json_decode(json_encode($res->getBody()->getContents()));
        $response = json_decode($response, true);
        $response = $response['esbBody']['results'][0];

        if (!empty($response['idVsbVoyage'])) {
          $newreal = $response['esbBody']['results'][0];
          DB::connection('omcargo')->table('TX_REAL_TOS')->insert([
             'idvsb'=> $newreal['idVsbVoyage'],
             'bl_no'=> $newreal['blNumber'],
             'package'=> $newreal['packageName'],
             'is_hz'=> $newreal['hz'],
             'is_disturb'=> $newreal['disturb'],
             'ei'=> $newreal['ei'],
             'tl'=> $newreal['tl'],
             'total_ton'=> $newreal['ttlTon'],
             'total_cubic'=> $newreal['ttlCubic'],
             'oi'=> $newreal['oi'],
             'rpact'=> $newreal['rpact'],
             'omcargoid'=> $newreal['omCargoid']
          ]);
        }
      }
    }

    return [
      'req_header' => $req,
      'req_detil' => DB::connection('omcargo')->select(DB::raw("select * from TX_DTL_BM A left join TX_REAL_TOS B on B.BL_NO = A.DTL_BM_BL where A.HDR_BM_ID = ".$req->bm_id)),
      'result' => "Success, get data real from tos!"
    ];
	}

  public static function sendRequestBooking($input){
    $header = TxHdrBm::where('bm_no',$input)->first();
    $detil = DB::connection('omcargo')->table('TX_DTL_BM')->where('hdr_bm_id',$header->bm_id)->get();
    static::sendRealBM($head, $detil);
  }

  private static function sendRealBM($head, $detil){
    $consignee = '';
    $oi = '';
    $podpol = '';
    $movetype = '';
    $startenddate = '';
    $blno = '';
    $bldate = '';
    $vParam = $head->bm_no.'^'.$head->bm_cust_name.'^'.$head->bm_cust_id.'^'.$head->bm_cust_npwp.'^'.$head->bm_vessel_name.'^'.$head->bm_eta.'^'.$head->bm_etd.'^'.$head->bm_open_stack.'^'.$head->bm_voyin.'^'.$head->bm_voyout.'^'.$head->bm_closing_time.'^BONGKAR MUAT^'.$consignee.'^'.$consignee.'^'.$oi.'^'.$podpol.'^'.$podpol.'^'.$movetype.'^'.$startenddate.'^'.$startenddate.'^'.$blno.'^0^'.$bldate.'^'.$head->bm_trade_type.'^'.$head->bm_vvd_id.'^';
    $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/createBookingHeader";
    $string_json = '{
      "createBookingHeaderInterfaceRequest": {
        "esbHeader": {
          "externalId": "2",
          "timestamp": "2"
        },
        "esbBody": {
          "vParam": "'.$vParam.'",
          "vId": "-",
          "vReqNo": "-",
          "vBlNo": "-"
        }
      }
    }';
    $username="npk_billing";
    $password ="npk_billing";
    $client = new Client();
    $options= array(
      'auth' => [
        $username,
        $password
      ],
      'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
      'body' => $string_json,
      "debug" => false
    );
    try {
      $res = $client->post($endpoint_url, $options);
    } catch (ClientException $e) {
      // return $e->getResponse();
    }
    foreach ($detil as $list) {
      $merk = '';
      $model = '';
      $hz = '';
      $distrub = '';
      $wight = '';
      $vParam = $list->dtl_cmdty_name.'^'.$list->dtl_cont_type.'^'.$merk.'^'.$model.'^'.$hz.'^'.$distrub.'^'.$wight.'^'.$list->dtl_qty.'^0^';
      $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/createBookingDetail";
      $string_json = '{
        "createBookingDetailInterfaceRequest": {
          "esbHeader": {
            "externalId": "2",
            "timestamp": "2"
            },
            "esbBody": {
              "vParam": "'.$vParam.'",
              "vId": "",
              "vIdHeader": ""
            }
          }
        }';
        $username="npk_billing";
        $password ="npk_billing";
        $client = new Client();
        $options= array(
          'auth' => [
            $username,
            $password
          ],
          'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
          'body' => $string_json,
          "debug" => false
        );
        try {
          $res = $client->post($endpoint_url, $options);
        } catch (ClientException $e) {
      // return $e->getResponse();
        }
    }
  }

  public static function truckRegistration($input){
    $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/truckRegistration";

    $string_json = '{
          "terminalInsertUpdateRequest": {
              "esbHeader": {
                  "externalId": "5275682735",
                  "timestamp": "YYYYMMDD HH:Mi:SS"
              },
              "esbBody": {
                  "vTruckId": "'.$input['truck_plat_no'].'",
                  "vTruckNumber": "'.$input['truck_plat_no'].'",
                  "vRfidCode": "'.$input['truck_rfid_code'].'",
                  "vCustomerName": "'.$input['customer_name'].'",
                  "vAddress": "'.$input['customer_address'].'",
                  "vCustomerId": "'.$input['cdm_customer_id'].'",
                  "vKend": "'.$input['truck_type'].'",
                  "vTgl": "'.$input['date'].'",
                  "vTerminalCode": "201"
              }
          }
    }';

    $username="npk_billing";
    $password ="npk_billing";
    $client = new Client();
    $options= array(
      'auth' => [
        $username,
        $password
      ],
      'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
      'body' => $string_json,
      "debug" => false
    );
    try {
      $res = $client->post($endpoint_url, $options);
    } catch (ClientException $e) {
      echo $e->getRequest() . "\n";
      if ($e->hasResponse()) {
        echo $e->getResponse() . "\n";
      }
    }
    return [json_decode($res->getBody()->getContents())];
  }

  public static function updateTid($input){
    $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/updateTid";

    $string_json = '{
          "terminalInsertUpdateRequest": {
              "esbHeader": {
                  "externalId": "5275682735",
                  "timestamp": "YYYYMMDD HH:Mi:SS"
              },
              "esbBody": {
                  "vTruckId": "'.$input['truck_plat_no'].'",
                  "vTruckNumber": "'.$input['truck_plat_no'].'",
                  "vRfidCode": "'.$input['truck_rfid_code'].'",
                  "vCustomerName": "'.$input['customer_name'].'",
                  "vAddress": "'.$input['customer_address'].'",
                  "vCustomerId": "'.$input['cdm_customer_id'].'",
                  "vKend": "'.$input['truck_type'].'",
                  "vTgl": "'.$input['date'].'",
                  "vTerminalCode": "201"
              }
          }
    }';

    $username="npk_billing";
    $password ="npk_billing";
    $client = new Client();
    $options= array(
      'auth' => [
        $username,
        $password
      ],
      'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
      'body' => $string_json,
      "debug" => false
    );
    try {
      $res = $client->post($endpoint_url, $options);
    } catch (ClientException $e) {
      echo $e->getRequest() . "\n";
      if ($e->hasResponse()) {
        echo $e->getResponse() . "\n";
      }
    }
    return [json_decode($res->getBody()->getContents())];
  }

  public static function createTCA($input){
    $endpoint_url="http://10.88.48.57:5555/restv2/npkBilling/createTCA";

    $detail = '';
    foreach ($input['detail'] as $list) {
      $detail .= '{
                  "vNoRequest": "'.$list['vNoRequest'].'",
                  "vTruckId": "'.$list['vTruckId'].'",
                  "vTruckNumber": "'.$list['vTruckNumber'].'",
                  "vBlNumber": "'.$list['vBlNumber'].'",
                  "vTcaCompany": "'.$list['vTcaCompany'].'",
                  "vEi": "'.$list['vEi'].'",
                  "vRfidCode": "'.$list['vRfidCode'].'",
                  "vIdServiceType": "'.$list['vIdServiceType'].'",
                  "vServiceType": "'.$list['vServiceType'].'",
                  "vIdTruck": "'.$list['vIdTruck'].'",
                  "vIdVvd": "'.$list['vIdVvd'].'",
                  "vIdTerminal": "'.$list['vIdTerminal'].'"
                },';
    }
    $detail = substr($detail, 0, -1);

    $string_json = '{
     "createTCARequest": {
      "esbHeader": {
       "internalId": "",
       "externalId": "",
       "timestamp": "",
       "responseTimestamp": "",
       "responseCode": "",
       "responseMessage": ""
       },
       "esbBody": {
        "vVessel": "'.$input['vVessel'].'",
         "vVin": "'.$input['vVin'].'",
         "vVout": "'.$input['vVout'].'",
         "vNoRequest": "'.$input['vNoRequest'].'",
         "vCustomerName": "'.$input['vCustomerName'].'",
         "vCustomerId": "'.$input['vCustomerId'].'",
         "vPkgName": "'.$input['vPkgName'].'",
         "vQty": "'.$input['vQty'].'",
         "vTon": "'.$input['vTon'].'",
         "vBlNumber": "'.$input['vBlNumber'].'",
         "vBlDate": "'.$input['vBlDate'].'",
         "vEi": "'.$input['vEi'].'",
         "vHsCode": "'.$input['vHsCode'].'",
         "vIdServicetype": "'.$input['vIdServicetype'].'",
         "vServiceType": "'.$input['vServiceType'].'",
         "vIdVvd": "'.$input['vIdVvd'].'",
         "vIdTerminal": "'.$input['vIdTerminal'].'",
         "document": [],
         "detail":['.$detail.']
        }
      }
    }';

    $username="npk_billing";
    $password ="npk_billing";
    $client = new Client();
    $options= array(
      'auth' => [
        $username,
        $password
      ],
      'headers'  => ['content-type' => 'application/json', 'Accept' => 'application/json'],
      'body' => $string_json,
      "debug" => false
    );
    try {
      $res = $client->post($endpoint_url, $options);
    } catch (ClientException $e) {
      $error = $e->getRequest() . "\n";
      if ($e->hasResponse()) {
        $error .= $e->getResponse() . "\n";
      }
      return ["Success"=>false, "result" => $error];
    }
    return ["Success"=>true, "result" => json_decode($res->getBody()->getContents(), true)];
  }

  public static function sendNotaProforma($input){
    // buat funct send proforma nota ke invoice
  }
}
