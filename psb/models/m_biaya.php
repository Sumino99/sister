<?php
	session_start();
	require_once '../../lib/dbcon.php';
	require_once '../../lib/func.php';
	require_once '../../lib/pagination_class.php';
	require_once '../../lib/tglindo.php';
	$mnu = 'biaya';
	$tb  = 'psb_'.$mnu;

	if(!isset($_POST['aksi'])){
		$out=json_encode(array('status'=>'invalid_no_post'));		
	}else{
		switch ($_POST['aksi']) {
			// -----------------------------------------------------------------
			case 'tampil':
				$departemen      = isset($_POST['departemenS'])?$_POST['departemenS']:'';
				$detailgelombang = isset($_POST['detailgelombangS'])?$_POST['detailgelombangS']:'';
				$nGol            = getNumRows('golongan');
				// $nTing           = getNumRows2('tingkat');
				
				// checkSetBiaya($kelompok);
				$sql ='SELECT
							t.replid,	
							t.tingkat
						FROM
							aka_kelas k
							JOIN aka_subtingkat s on s.replid = k.subtingkat
							JOIN aka_tingkat t on t.replid = s.tingkat
						where 
							k.departemen = '.$departemen.'
						GROUP BY 
							t.replid';
				// vd($sql);
				
				// jumlah tingkat
				$eTing = mysql_query($sql);
				$nTing = mysql_num_rows($eTing);
				// vd($nGol);

				if(isset($_POST['starting'])){
					$starting=$_POST['starting'];
				}else{
					$starting=0;
				}

				$recpage = ($nGol*$nTing);//jumlah data per halaman
				$aksi    = 'tampil';
				$subaksi = '';
				$obj     = new pagination_class($sql,$starting,$recpage,$aksi,$subaksi);
				$result  =$obj->result;

				$jum = mysql_num_rows($result);
				$out ='';
				if($jum!=0){	
					$nox 	= $starting+1;
					while($r1 = mysql_fetch_assoc($result)){	
						$out.= '<tr>
									<td valign="middle" rowspan="'.($nGol+1).'">
										'.$nox.'. '.$r1['tingkat'].'
									</td>';
									// g.replid,
						$s2 ='	SELECT
									b.replid,
									b.spp,
									b.formulir,
									b.joiningf,
									b.dpp,
									g.golongan,
									t.tingkat,
									g.keterangan
								FROM
									psb_golongan g
									JOIN psb_biaya b ON b.golongan = g.replid
									JOIN aka_tingkat t ON t.replid = b.tingkat
								WHERE
									b.tingkat = '.$r1['replid'].'
									AND b.detailgelombang = '.$detailgelombang;
								// vd($s2);
						// print_r($s2);exit();
						$e2  = mysql_query($s2);
						while ($r2=mysql_fetch_assoc($e2)) {
							$out.= '<tr>
										<td>'.$r2['golongan'].'<br> <sup class="fg-orange">('.$r2['keterangan'].')</sup> <input name="golongan[]" value="'.$r2['replid'].'" type="hidden"></td> 
										<td align="right">'.(!isAksi('biaya','u')?setuang($r2['formulir']):'<div class="input-control text" ><input data-hint="Formulir" class="text-right" value="Rp. '.number_format($r2['formulir']).'"    onclick="inputuang(this);" onfocus="inputuang(this);" type="text" name="formulirTB_'.$r2['replid'].'"></div>').'</td> 
										<td align="right">'.(!isAksi('biaya','u')?setuang($r2['dpp']):'<div class="input-control text" ><input data-hint="dpp" class="text-right" value="Rp. '.number_format($r2['dpp']).'"    onclick="inputuang(this);" onfocus="inputuang(this);" type="text" name="dppTB_'.$r2['replid'].'"></div>').'</td> 
										<td align="right">'.(!isAksi('biaya','u')?setuang($r2['joiningf']):'<div class="input-control text" ><input data-hint="joiningf" class="text-right" value="Rp. '.number_format($r2['joiningf']).'"    onclick="inputuang(this);" onfocus="inputuang(this);" type="text" name="joiningfTB_'.$r2['replid'].'"></div>').'</td> 
										<td align="right">'.(!isAksi('biaya','u')?setuang($r2['spp']):'<div class="input-control text" ><input data-hint="spp" class="text-right" value="Rp. '.number_format($r2['spp']).'"    onclick="inputuang(this);" onfocus="inputuang(this);" type="text" name="sppTB_'.$r2['replid'].'"></div>').'</td> 
									</tr>';
						}
						$out.= '</tr>';
						$nox++;
					}
				}else{ #kosong
					$out.= '<tr align="center">
							<td  colspan=9 ><span style="color:red;text-align:center;">
							... data tidak ditemukan...</span></td></tr>';
				}
				#link paging
				$out.= '<tr class="info"><td colspan=9>'.$obj->anchors.'</td></tr>';
				$out.='<tr class="info"><td colspan=9>'.$obj->total.'</td></tr>';
			break; 
			// view -----------------------------------------------------------------

			// add / edit -----------------------------------------------------------------
			case 'simpan':
				$stat2= true;
				foreach ($_POST['golongan'] as $i => $v) {
					$s = 'UPDATE '.$tb.' set 	dpp      = '.filter(getuang($_POST['dppTB_'.$v])).',
												spp      = '.filter(getuang($_POST['sppTB_'.$v])).',
												joiningf = '.filter(getuang($_POST['joiningfTB_'.$v])).',
												formulir = '.filter(getuang($_POST['formulirTB_'.$v])).'
										WHERE 	replid 	 = '.$v;
					// print_r($s);exit();
					$e     = mysql_query($s);
					$stat2 = $e?true:false;
				}$stat = $stat2?'sukses':'gagal';
				$out = json_encode(array('status'=>$stat));
			break;
			// add / edit -----------------------------------------------------------------
			
			// delete -----------------------------------------------------------------
			case 'hapus':
				$d    = mysql_fetch_assoc(mysql_query('SELECT * from '.$tb.' where replid='.$_POST['replid']));
				$s    = 'DELETE from '.$tb.' WHERE replid='.$_POST['replid'];
				$e    = mysql_query($s);
				$stat = ($e)?'sukses':'gagal';
				$out  = json_encode(array('status'=>$stat,'terhapus'=>$d[$mnu]));
			break;
			// delete -----------------------------------------------------------------

			// ambiledit -----------------------------------------------------------------
			case 'ambiledit':
				$s 		= ' SELECT *
							from '.$tb.'
							WHERE 
								replid='.$_POST['replid'];
				$e 		= mysql_query($s);
				$r 		= mysql_fetch_assoc($e);
				$stat 	= ($e)?'sukses':'gagal';
				$out 	= json_encode(array(
							'status'     =>$stat,
							'kelas'      =>$r['kelas'],
							'wali'       =>$r['wali'],
							'kapasitas'  =>$r['kapasitas'],
							'keterangan' =>$r['keterangan'],
						));
			break;
			// ambiledit -----------------------------------------------------------------

			// aktifkan -----------------------------------------------------------------
			case 'aktifkan':
				$e1   = mysql_query('UPDATE  '.$tb.' set aktif="0" where departemen = '.$_POST['departemen']);
				if(!$e1){
					$stat='gagal menonaktifkan';
				}else{
					$s2 = 'UPDATE  '.$tb.' set aktif="1" where replid = '.$_POST['replid'];
					$e2 = mysql_query($s2);
					if(!$e2){
						$stat='gagal mengaktifkan';
					}else{
						$stat='sukses';
					}
				}$out  = json_encode(array('status'=>$stat));
				//var_dump($stat);exit();
			break;
			// aktifkan -----------------------------------------------------------------

		}
	}echo $out;

	// ---------------------- //
	// -- created by epiii -- //
	// ---------------------- //
?>