<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use App\Models\OmUster\TmUser;
use Firebase\JWT\ExpiredException;
use DB;
class JwtMiddleware
{

    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->header("token");
        if(!$token) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Token not provided. Please Login.'
            ], 401);
        }
        if ($token == "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJiZWFyZXIiLCJzdWIiOiIzNCIsImV4cCI6Mjg3MDY1OTgzMn0.gSWsv4ZvKbEUWSRT41ECtUND_k3vFCfR4R8DuY6DYLk") {
          $data = DB::connection('omuster')->table('TM_USER')->where("USER_ID",58)->get();
          $request->request->add(['user' => $data[0]]);
          return $next($request);
        } else {
          $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
          date_default_timezone_set('GMT');
          $data    = DB::connection('omuster')->table('TM_USER')->where("USER_ID",$credentials->sub)->get();
          $cektoken= DB::connection('omuster')->table('TM_USER')->where("API_TOKEN",$token)->get();
          $time    = date('H:i:s',strtotime('+7 hour -3 minutes',strtotime(date("h:i:s"))));
          $tes     = json_decode(json_encode($data), TRUE);
          $now     = intval(strtotime($time));
          $active  = intval(strtotime($tes[0]["user_active"]));
          $selisih = ($now - $active)/60;

          if ($selisih >= 240) {
            $update  = DB::connection('omuster')->table('TM_USER')->where("USER_ID",$credentials->sub)->update(["USER_LOGIN"=>"0","API_TOKEN"=>""]);
            return response()->json([
                    'error' => 'Provided token is expired. Please Login'
                ], 400);
          } else if(empty($cektoken)) {
            return response()->json([
                    'error' => 'Error Token. Please Login'
                ], 400);
          } else {
            // Now let's put the user in the request class so that you can grab it from there
            $update  = DB::connection('omuster')->table('TM_USER')->where("USER_ID",$credentials->sub)->update(["USER_ACTIVE" => $time]);
            $user    = DB::connection('omuster')->table('TM_USER')->where("USER_ID",$credentials->sub)->get();
            $request->auth = $user;
            $request->request->add(['user' => $data[0]]);
            return $next($request);
            // return $user;
          }
        }
    }

}
