<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Billing\TxProfileTariffHdr;
use App\Models\Billing\TsTariff;
use Carbon\Carbon;
use App\Helper\BillingEngine;

class ViewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function api(Request $request) {
      $input  = $this->request->all();
      $action = $input["action"];
      return $this->$action($input, $request);
    }

    public function view($input) {
      $table  = $input["table"];
      $data   = \DB::connection('order')->table($table)->get();
      return response()->json($data);
    }

    function viewProfileTariff($input, $request)
    {
      return BillingEngine::viewProfileTariff($input);
    }

}
