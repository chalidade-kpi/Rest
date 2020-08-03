<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
		body {
			width: 100%;
			margin: 0 auto;
			font-family: "Arial", Sans-serif;
		}

		@media print {
			body {
				width: 100%;
				margin: 0 auto;
				font-family: "Arial", Sans-serif;
			}
		}
	</style>
</head>

<body>
	@foreach($header as $header)
	@foreach($label as $label)
	<?php
	$noa = 0;
	$nomor = 0;
	if ($header->nota_paid == "I") { ?>
		<img src="{{ url('/other/belum_lunas.png')}}" alt="" style="position:absolute;opacity:0.3;margin-left:100px;transform: rotate(-30deg);margin-top:300px;width:80%">
	<?php } ?>
	@foreach($branch as $branch)
	<table width="100%" style="font-size:10px">
		<tr>
			<td width="13%"><img src="{{ url('/other/ipc_jambi.png') }}" height="70"></td>
			<td width="45%" style="vertical-align:top;font-size:12px">
				<div>IPC LOGISTICS (MTI) CAB JAMBI<br>Jl. Raya Pelabuhan KM. 9, Talang Duku, Jambi <div style="margin-top:3px;font-size:10px">NPWP. 03.276.305.4-093.000</div>
				</div>
			</td>
			<td colspan="4"  align="left">
				<table>
					<tr>
						<td><font size="2">No. Nota</font></td>
						<td><font size="2">: {{$header->nota_no}}</font></td>
					</tr>
					<tr>
						<td><font size="2">Tanggal</font></td>
						<td><font size="2">:
							<?php
							$originalDate = $header->nota_date;
							$newDate = date("d-M-y", strtotime($originalDate));
							echo strtoupper($newDate);
							?></font>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<!-- Nota sebagai faktur pajak berdasarkan Peraturan Dirjen Pajak Nomor PER-13/PJ/2019 tanggal 2 Juli 2019 -->
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<center style="width:100%;background-color:#ff3030;color:#fff;margin-top:20px;padding:5px;font-weight:800;text-transform:uppercase">Nota Penjualan Jasa {{$label->nota_name}}</center>

	<table width="100%" border="0" cellspacing="1" cellpadding="1" style="border-collapse:collapse; font-size:11px;margin-top:20px;margin-bottom:20px">
		<tr style="text-align:center">
			<td style="vertical-align:top;width:60%">
				<table style="border-collapse:collapse; font-size:11px;" width="100%">
					<tr>
						<td colspan="3">
							<font style="font-size:11px;text-align:left;font-weight:800"><b>Penerima Jasa</b></font><br>
						</td>
					</tr>
					<tr>
						<td width="10%">Nama</td>
						<td width="1%">: </td>
						<td>{{$header->nota_cust_name}}</td>
					</tr>
					<!-- <tr>
          <td>Nomor</td>
          <td>: </td>
          <td>{{$header->nota_cust_id}}</td>
        </tr> -->
					<tr>
						<td>Alamat</td>
						<td>: </td>
						<td>{{$header->nota_cust_address}}</td>
					</tr>
					<tr>
						<td>NPWP</td>
						<td>: </td>
						<td>{{$header->nota_cust_npwp}}</td>
					</tr>
				</table>
			</td>
			<td>
				<table style="border-collapse:collapse; font-size:11px;">
					<tr>
						<td>No. DO</td>
						<td>: </td>
						<td>-</td>
					</tr>
					<tr>
						<td>PBM</td>
						<td>: </td>
						<td>{{$header->nota_pbm_name}}</td>
					</tr>
					<tr>
						<td>Nama Kapal</td>
						<td>: </td>
						<td>{{$header->nota_vessel_name}}</td>
					</tr>
					<!-- <tr>
						<td>Periode Kunjungan </td>
						<td>: </td>
						<td>{{$header->nota_id}}</td>
					</tr> -->
				</table>
			</td>
		</tr>
	</table>

	<?php if ($label->nota_id == '21' || $label->nota_id == '22') { ?>
		<table width="100%" align="center" cellspacing="1" cellpadding="2" style="border-collapse:collapse; font-size:11px;">
			<tr style="text-align:center">
				<th width="3%" style="border-bottom:solid 1px; text-align:center">No</th>
				<th width="17%" style="border-bottom:solid 1px">Layanan</th>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<th width="15%" style="border-bottom:solid 1px">Tanggal Awal</th>
					<th width="15%" style="border-bottom:solid 1px">Tanggal Akhir</th>
				@php
					}
				@endphp

				<th width="15%" style="border-bottom:solid 1px">Kemasan</th>
				<th width="10%" style="border-bottom:solid 1px">Satuan</th>
				<th width="6%" style="border-bottom:solid 1px">Qty</th>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<th width="6%" style="border-bottom:solid 1px">Hari</th>
				@php
					}
				@endphp
				<th width="6%" style="border-bottom:solid 1px">Hz</th>
				<th width="10%" style="border-bottom:solid 1px">Tarif Dasar</th>
				<!-- <th width="5%" style="border-bottom:solid 1px"></th> -->
				<th width="20%" style="border-bottom:solid 1px">Total</th>
			</tr>
<!-- 											$detail->dateinout = $detail->date_in_out;
								list($awal,$akhir)=explode(' s/d ',$detail->dateinout);

								$datetime1 = date_create($awal);
								$datetime2 = date_create($akhir);
								$interval = date_diff($datetime1, $datetime2);
								$hari = ($interval->days)+1; -->
			@foreach($detail as $detail)
					@php
						if ($detail->group_tariff_name == 'PENUMPUKAN') {

								$awal = $detail->date_in_penumpukan;
								$akhir = $detail->date_out_penumpukan;
								$hari = $detail->day_period;
						}else{
							$awal = '';
							$akhir = '';
							$hari = '';
						}
						if ($detail->group_tariff_name == 'ADMINISTRASI') {
								$detail->cont_size = '';
								$detail->cont_type = '';
								$detail->cont_status = '';
								$detail->qty = '';
								$hari = '';
								$detail->hz = '';
								$awal = '';
								$akhir = '';
						}
						if ($detail->group_tariff_name == 'PASS TRUCK') {
								$detail->cont_size = '';
								$detail->cont_type = '';
								$detail->cont_status = '';
								$hari = '';
								$detail->hz = '';
								$awal = '';
								$akhir = '';
								$detail->package_name = '';
								$detail->unit_name = '';
						}


					@endphp
			<tr>
				<td style="text-align:center"><?php $nomor++;
												echo $nomor; ?>.</td>
				<td style="text-align:left">{{$detail->group_tariff_name_real}}</td>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<td style="text-align:center">{{$awal}}</td>
					<td style="text-align:center">{{$akhir}}</td>
				@php
					}
				@endphp
				<td style="text-align:center">{{$detail->package_name}}</td>
				<td style="text-align:center">{{$detail->unit_name}}</td>
				<td style="text-align:center">{{$detail->qty}}</td>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<td style="text-align:center">{{$hari}}</td>
				@php
					}
				@endphp
				<td style="text-align:center">{{$detail->hz}}</td>
				<td style="text-align:right">{{number_format($detail->tariff)}} IDR</td>
				<!-- <td>IDR</td> -->
				<td style="text-align:right">{{number_format($detail->dpp)}}</td>
			</tr>
			@endforeach
		</table>
	<?php
	} else {
	?>
		<table width="100%" align="center" border="0" cellspacing="1" cellpadding="2" style="border-collapse:collapse; font-size:11px;">
			<tr style="text-transform:uppercase;font-weight:800">
				<th width="5%" style="border-bottom:solid 1px;text-align:center">No</th>
				<th width="15%" style="border-bottom:solid 1px">Layanan</th>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<th width="20%" style="border-bottom:solid 1px">Tanggal Awal</th>
					<th width="20%" style="border-bottom:solid 1px">Tanggal Akhir</th>
				@php
					}
				@endphp
				<th width="20%" style="border-bottom:solid 1px">Container</th>
				<th width="1%" style="border-bottom:solid 1px;text-align:center">Qty</th>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<th width="10%" style="border-bottom:solid 1px;text-align:center">Hari</th>
				@php
					}
				@endphp
				<th width="10%" style="border-bottom:solid 1px;text-align:center">Hz</th>
				<th width="15%" style="border-bottom:solid 1px;text-align:center">Tarif Dasar</th>
				<!-- <th width="5%" style="border-bottom:solid 1px"></th> -->
				<th width="15%" style="border-bottom:solid 1px">Jumlah</th>
			</tr>
			<!-- $hari = ($interval->days)+1; -->
<!-- 								$detail->dateinout = $detail->date_in_out;
								list($awal,$akhir)=explode(' s/d ',$detail->dateinout);

								$datetime1 = date_create($awal);
								$datetime2 = date_create($akhir);
								$interval = date_diff($datetime1, $datetime2); -->
			@foreach($detail as $detail)
					@php
						if ($detail->group_tariff_name == 'PENUMPUKAN') {
								$awal = $detail->date_in_penumpukan;
								$akhir = $detail->date_out_penumpukan;
								$hari = $detail->day_period;
						}else{
							$awal = '';
							$akhir = '';
							$hari = '';
						}
						if ($detail->group_tariff_name == 'ADMINISTRASI') {
								$detail->cont_size = '';
								$detail->cont_type = '';
								$detail->cont_status = '';
								$detail->qty = '';
								$hari = '';
								$detail->hz = '';
								$awal = '';
								$akhir = '';
						}
						if ($detail->group_tariff_name == 'PASS TRUCK') {
								$detail->cont_size = '';
								$detail->cont_type = '';
								$detail->cont_status = '';
								$hari = '';
								$detail->hz = '';
								$awal = '';
								$akhir = '';
								$detail->package_name = '';
								$detail->unit_name = '';
						}


					@endphp
			<tr>
				<td style="text-align:center"><?php $nomor++;
												echo $nomor; ?>.</td>
				<td style="text-align:left">{{$detail->group_tariff_name_real}}</td>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<td style="text-align:center">{{$awal}}</td>
					<td style="text-align:center">{{$akhir}}</td>
				@php
					}
				@endphp
				<td style="text-align:left">{{$detail->cont_size}}  {{$detail->cont_type}}  {{$detail->cont_status}}</td>
				<td style="text-align:center">{{$detail->qty}}</td>
				@php
					if($label->nota_name!='RECEIVING'){
				@endphp
					<td style="text-align:center">{{$hari}}</td>
				@php
					}
				@endphp
				<td style="text-align:center">{{$detail->hz}}</td>
				<td style="text-align:right">{{number_format($detail->tariff)}} IDR</td>
				<!-- <td>IDR</td> -->
				<td style="text-align:right">{{number_format($detail->dpp)}}</td>
			</tr>
			@endforeach
		</table>
	<?php } ?>

	<table width="100%" border="0" cellspacing="1" cellpadding="1" style="border-collapse:collapse; font-size:11px;margin-top:20px">
		<tr>
			<td colspan="7">DASAR PENGENAAN PAJAK</td>
			<td style="text-align:right;padding-right:9px">IDR</td>
			<td style="text-align:right">{{number_format($header->nota_dpp)}}</td>
		</tr>
		<tr>
			<td colspan="7">PPN 10%</td>
			<td style="text-align:right;padding-right:9px">IDR</td>
			<td style="text-align:right;">{{number_format($header->nota_ppn)}}</td>
		</tr>
		<tr>
			<td colspan="7">MATERAI</td>
			<td style="text-align:right;padding-right:9px">IDR</td>
			<td style="text-align:right;border-bottom:solid 1px">{{number_format($e_materai)}}</td>
		</tr>
		<tr>
			<td style="" colspan="7">Jumlah Tagihan</td>
			<td style="text-align:right;padding-right:9px">IDR</td>
			<td style="text-align:right;">{{number_format($notaAmount)}}</td>
		</tr>
		<tr>
			<td style="" colspan="7">Uang Pembayaran</td>
			<td style="text-align:right;padding-right:9px">IDR</td>
			<td style="text-align:right">{{number_format($notaAmount)}}</td>
		</tr>
		<!-- <tr>
			<td style="" colspan="7">
				<b>Piutang</b>
			</td>
			<td style="text-align:right;padding-right:9px"><b>IDR</b></td>
			<td style="text-align:right"><b>{{number_format($total)}}</b></td>
		</tr> -->
	</table>
	<p style="font-size:11px;margin-top:50px">Terbilang : <font style="text-transform:capitalize">{{$terbilang}} Rupiah</font>
	</p>
	<table style="width:100%">
		<tr>
			<td>
				<div><?php echo DNS2D::getBarcodeHTML($qrcode, "QRCODE", 2, 2); ?></div>
			</td>
			<td style="vertical-align:top">
				<table style="border-collapse:collapse; font-size:11px;float:right;text-align:center">
					<tr>
						<td>Jambi,
							<?php
							$originalDate = $header->nota_date;
							$newDate = date("d-M-y", strtotime($originalDate));
							echo strtoupper($newDate);
							?></td>
					</tr>
					<tr>
						<td>MENGETAHUI<br>HEAD PROJECT JAMBI</td>
					</tr>
					<tr>
						<td>
							<div style="margin-top:50px"><u>WAHYU TRIDJAYA KUSUMA</u></div>
						</td>
					</tr>
					<tr>
						<td>NIPP. 269070070</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<div style="position:absolute;bottom:20px;font-size:11px; width:100%">
		{{$branch->branch_name}} <br>{{$branch->branch_address}}
		<div style="margin-top:50px;font-size:8px">
			{{$header->nota_no}}
		</div>
	</div>
	<p style="position:absolute;right:0px;bottom:15px;font-size:8px">Print Date : <?php echo date("d-M-Y H:s:i") . " | Page 1/1"; ?></p>
	@endforeach
	@endforeach
	@endforeach

</body>

</html>



</body>

</html>
