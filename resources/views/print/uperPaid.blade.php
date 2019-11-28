<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
		 body{
			 width:100%;
			 margin:0 auto;
			 font-family: 'Courier';
		 }
		 @media print {
        body {
					width:100%;
					margin:0 auto;
					font-family: 'Courier';
				}
      }
	</style>
</head>
<body>

	@foreach($header as $header)
	@foreach($branch as $branch)
  @foreach($data as $data)
  <table width="100%" style="font-size:9px">
    <tr>
      <td width="13%"><img src="{{ url('/other/logo.jpg') }}" height="50"></td>
      <td width="55%">
        <div<b>{{$branch->branch_name}} <br>{{$branch->branch_address}} </b><div style="margin-top:5px;font-size:9px">NPWP. {{$branch->branch_npwp}}</div></div>
        </td>
      <td style="vertical-align:top;text-align:right">
      </td>
    </tr>
  </table>

<center style="width:100%;background-color:#ff3030;color:#fff;margin-top:20px;padding:5px">Uang Untuk Diperhitungkan (UPER)</center>
<table  width="100%" border="0" cellspacing="1" cellpadding="1" style="border-collapse:collapse; font-size:8px;margin-top:20px">
	<tr style="text-align:center">
		<td>
      <table style="border-collapse:collapse; font-size:9px;">
        <tr>
          <td>Sudah Terima Dari</td>
          <td>: </td>
          <td>{{$data->uper_cust_name}}</td>
        </tr>
        <tr>
          <td>Untuk Kapal / Voyage</td>
          <td>: </td>
          <td>{{$data->uper_vessel_name}}</td>
        </tr>
        <tr>
          <td>Periode Kunjungan</td>
          <td>: </td>
          <td>{{$data->periode}}</td>
        </tr>
        <tr>
          <td>Nomor Uper</td>
          <td>: </td>
          <td>{{$data->uper_no}}</td>
        </tr>
        <tr>
          <td>Untuk Pembayaran</td>
          <td>: </td>
          <td>
            <?php
            if($data->uper_trade_type == "D") {
              echo "PELAYARAN DALAM NEGERI";
            } else {
              echo "PELAYARAN LUAR NEGERI";
            }
            ?>
          </td>
        </tr>
        <tr>
          <td>Jumlah UPER</td>
          <td>: </td>
          <td>{{number_format($data->uper_amount)}}</td>
        </tr>
        <tr>
          <td>Jumlah Pembayaran</td>
          <td>: </td>
          <td>{{number_format($data->pay_amount)}}</td>
        </tr>
        <tr>
          <td><br>Cara Pembayaran</td>
          <td><br>: </td>
          <td><br>{{$data->pay_account_name}}</td>
        </tr>
        <tr>
          <td>Tanggal Pembayaran</td>
          <td>: </td>
          <td>{{$data->pay_date}}</td>
        </tr>
        <tr>
          <td>Keterangan</td>
          <td>: </td>
          <td>{{$data->pay_note}}</td>
        </tr>
      </table>
    </td>
		<td style="vertical-align:top">
      <table style="border-collapse:collapse; font-size:9px;">
        <tr>
          <td>No. Account</td>
          <td>: </td>
          <td>{{$data->pay_cust_id}}</td>
        </tr>
      </table>
</table>

<p style="font-size:9px;margin-top:80px">Terbilang : <font style="text-transform:capitalize">{{$terbilang}}</font></p>
<div style="margin-top:20px"><?php echo DNS2D::getBarcodeHTML("4445645656", "QRCODE", 4.5,4.5); ?></div>
<table style="border-collapse:collapse; font-size:8px;margin-top:60px;float:right;text-align:center">
	<tr><td>Palembang, 29 Agustus 2019</td></tr>
	<tr><td>DGM Keuangan & Administrasi</td></tr>
	<tr><td><div style="margin-top:50px"><u>Clara Primasari Henryanto</u></div></td></tr>
	<tr><td>NIPP. 287117773</td></tr>
</table>

<div style="position:absolute;bottom:20px;font-size:9px; width:100%">
	{{$branch->branch_name}} <br>{{$branch->branch_address}}
	<div style="margin-top:50px;font-size:8px">
			{{$branch->branch_npwp}}
	</div>
</div>
<p style="position:absolute;right:0px;bottom:15px;font-size:8px">Print Date : <?php echo date("d-M-Y")." | Page 1/1"; ?></p>
@endforeach
@endforeach
@endforeach
</body>
</html>
