<?php
if (!defined('AURACMS_admin')) {
    Header("Location: ../index.php");
    exit;
}

if (!cek_login()){
    header("location: index.php");
    exit;
} else{

$JS_SCRIPT.= <<<js
<script language="JavaScript" type="text/javascript">
$(document).ready(function() {
    $('#example').dataTable({
    "iDisplayLength":50});
} );
</script>
js;
$JS_SCRIPT.= <<<js
<script type="text/javascript">
  $(function() {
$( "#tgl" ).datepicker({ dateFormat: "yy-mm-dd" } );
  });
  </script>
js;
$script_include[] = $JS_SCRIPT;
	
//$index_hal=1;	
	$admin  .='<legend>PURCHASE ORDER (PO)</legend>';
	$admin  .= '<div class="border2">
<table  width="25%"><tr align="center">
<td>
<a href="admin.php?pilih=po&mod=yes">PURCHASE ORDER</a>&nbsp;&nbsp;
</td>
<td>
<a href="admin.php?pilih=po&mod=yes&aksi=cetak">CETAK PURCHASE ORDER</a>&nbsp;&nbsp;
</td>
</tr></table>
</div>';
$admin .='<div class="panel panel-info">';
$admin .= '<script type="text/javascript" language="javascript">
   function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>';
if ($_GET['aksi'] == ''){

if(isset($_POST['tambah'])){
$kodecari 		= $_POST['kode'];
$totalpo = $_SESSION["totalpo"];
$jumlah 		= '1';
$hasil =  $koneksi_db->sql_query( "SELECT * FROM po_produk WHERE kode='$kodecari'" );
$data = $koneksi_db->sql_fetchrow($hasil);
$id=$data['id'];
$kode=$data['kode'];
$stok=$data['stok'];
$error 	= '';
//$cekjumlahbeli = cekjumlahbeli($kode);
if (!$kode)  	$error .= "Error:  Kode Barang Tidak di Temukan<br />";
if ($error){
$admin .= '<div class="error">'.$error.'</div>';
}else{
$admin .= '<div class="sukses">Kode Barang di Temukan </div>';
$PRODUCTID = array ();
foreach ($_SESSION['product_id'] as $k=>$v){
$PRODUCTID[] = $_SESSION['product_id'][$k]['kode'];
}
if (!in_array ($kode, $PRODUCTID)){
$_SESSION['product_id'][] = array ('id' => $id,'kode' => $kode, 'jumlah' => $jumlah);
}else{
foreach ($_SESSION['product_id'] as $k=>$v){
    if($kode == $_SESSION['product_id'][$k]['kode'])
	{
$_SESSION['product_id'][$k]['jumlah'] = $_SESSION['product_id'][$k]['jumlah']+1;
    }
}
		
}
}
}

if(isset($_POST['submitpo'])){
$nopo 		= $_POST['nopo'];
$kodepr 		= $_SESSION["kodepr"];
$tgl 		= $_POST['tgl'];
$kodesupplier 		= $_SESSION["kodesupplier"];
$carabayar 		= $_POST['carabayar'];
$termin 		= $_POST['termin'];
$total 		= $_POST['total'];
$discount 		= $_POST['discount'];
$netto = $_POST['bayar'];
$user 		= $_POST['user'];
if (!$_SESSION["kodesupplier"])  	$error .= "Error:  Kode Supplier harus ada <br />";
if (!$_SESSION["product_id"])  	$error .= "Error:  Kode Barang harus ada <br />";
if ($koneksi_db->sql_numrows($koneksi_db->sql_query("SELECT nopo FROM po_po WHERE nopo='$nopo'")) > 0) $error .= "Error: Nomor PO ".$nopo." sudah terdaftar<br />";
if ($koneksi_db->sql_numrows($koneksi_db->sql_query("SELECT nopr FROM po_po WHERE nopr='$kodepr'")) > 0) $error .= "Error: Nomor PR ".$kodepr." sudah terdaftar<br />";
if ($error){
$admin .= '<div class="error">'.$error.'</div>';
}else{
$hasil  = mysql_query( "INSERT INTO `po_po` VALUES ('','$nopo','$kodepr','$tgl','$kodesupplier','$carabayar','$termin','$total','$discount','$netto','$user')" );
$idpo = mysql_insert_id();
foreach ($_SESSION["product_id"] as $cart_itm)
{
$kode = $cart_itm["kode"];
$jumlah = $cart_itm["jumlah"];
$harga = $cart_itm["harga"];
$subdiscount = $cart_itm["subdiscount"];
$subtotal = $cart_itm["subtotal"];
$hasil  = mysql_query( "INSERT INTO `po_podetail` VALUES ('','$nopo','$kode','$jumlah','$harga','$subdiscount','$subtotal')" );
//updatestokbeli($kode,$jumlah);
}
if($hasil){
$admin .= '<div class="sukses"><b>Berhasil Menambah PO.</b></div>';
pocetak($nopo);
porefresh();
$style_include[] ='<meta http-equiv="refresh" content="2; url=admin.php?pilih=po&mod=yes" />';
}else{
$admin .= '<div class="error"><b>Gagal Menambah PO.</b></div>';
		}		
}	
}

if(isset($_POST['tambahsupplier'])){
$_SESSION['kodesupplier'] = $_POST['kodesupplier'];
}

if(isset($_POST['deletesupplier'])){
porefresh();
}

if(isset($_POST['hapusbarang'])){
$kode 		= $_POST['kode'];
foreach ($_SESSION['product_id'] as $k=>$v){
    if($kode == $_SESSION['product_id'][$k]['kode'])
	{
unset($_SESSION['product_id'][$k]);
    }
}
}

/*
if(isset($_POST['editjumlah'])){
$kode 		= $_POST['kode'];
$harga 		= $_POST['harga'];
$jumlahpo = $_POST['jumlahpo'];
$subdiscount = $_POST['subdiscount'];
foreach ($_SESSION['product_id'] as $k=>$v){
    if($kode == $_SESSION['product_id'][$k]['kode'])
	{
$_SESSION['product_id'][$k]['subdiscount']=$subdiscount;
$_SESSION['product_id'][$k]['jumlah']=$jumlahpo;
$_SESSION['product_id'][$k]['harga']=$harga;
$nilaidiscount=cekdiscount($subdiscount,$harga);
$_SESSION['product_id'][$k]['subtotal'] = $jumlahpo*($harga-$nilaidiscount);
		}
}
}
*/

if(isset($_POST['simpandetail'])){
foreach ($_SESSION['product_id'] as $k=>$v){
$_SESSION['product_id'][$k]['subdiscount']=$_POST['subdiscount'][$k];
$_SESSION['product_id'][$k]['jumlah']=$_POST['jumlahpo'][$k];
$_SESSION['product_id'][$k]['harga']=$_POST['harga'][$k];
$nilaidiscount=cekdiscount($_SESSION['product_id'][$k]['subdiscount'],$_SESSION['product_id'][$k]['harga']);
$_SESSION['product_id'][$k]['subtotal'] =$_SESSION['product_id'][$k]['jumlah']*($_SESSION['product_id'][$k]['harga']-$nilaidiscount);
}
$style_include[] ='<meta http-equiv="refresh" content="1; url=admin.php?pilih=po&mod=yes" />';
}

/*
if(isset($_POST['tambahbarang'])){
	$_SESSION['kodesupplier'] = $_POST['kodesupplier'];	
$kodebarang 		= $_POST['kodebarang'];
$jumlah 		= '1';
$hasil =  $koneksi_db->sql_query( "SELECT * FROM po_produk WHERE kode='$kodebarang'" );
$data = $koneksi_db->sql_fetchrow($hasil);
$id=$data['id'];
$kode=$data['kode'];
$stok=$data['jumlah'];
$harga=$data['hargabeli'];
$jenjang=$data['jenjang'];
$error 	= '';
if (!$kode)  	$error .= "Error:  Kode Barang Tidak di Temukan<br />";
if ($error){
$admin .= '<div class="error">'.$error.'</div>';
}else{

$PRODUCTID = array ();
foreach ($_SESSION['product_id'] as $k=>$v){
$PRODUCTID[] = $_SESSION['product_id'][$k]['kode'];
}
if (!in_array ($kode, $PRODUCTID)){
$subdiscount="0";
$subtotal=$harga;
$_SESSION['product_id'][] = array ('id' => $id,'kode' => $kode, 'jumlah' => $jumlah, 'harga' => $harga, 'jenjang' => $jenjang, 'subdiscount' => $subdiscount, 'subtotal' => $subtotal, 'stok' => $stok);
}else{
foreach ($_SESSION['product_id'] as $k=>$v){
    if($kode == $_SESSION['product_id'][$k]['kode'])
	{
	$subdiscount="0";
$_SESSION['product_id'][$k]['jumlah'] = $_SESSION['product_id'][$k]['jumlah']+1;
$_SESSION['product_id'][$k]['subtotal'] = $_SESSION['product_id'][$k]['jumlah']*$_SESSION['product_id'][$k]['harga'];
    }
}
		
}
}
}
*/

if(isset($_POST['tambahpr'])){
$_SESSION['product_id']='';
$_SESSION['totalpo']='';
$_SESSION['kodesupplier'] = $_POST['kodesupplier'];
$_SESSION['kodepr'] = $_POST['kodepr'];
$carabayar = $_POST['carabayar'];
$termin = $_POST['termin'];
if (!$_POST['kodesupplier'])  	$error .= "Error:  Kode Supplier harus Di isi<br />";
if (!$_POST['kodepr'])  	$error .= "Error:  Kode PR harus Di isi<br />";
if ($error){
$admin .= '<div class="error">'.$error.'</div>';
}else{
$hasil3 =  $koneksi_db->sql_query("SELECT * FROM po_pr WHERE nopr = '$_SESSION[kodepr]'");
$data3 = $koneksi_db->sql_fetchrow($hasil3);
$_SESSION['namapr'] = $data3['namapr'];	  
$_SESSION['departemenpr'] = $data3['departemenpr'];	  
$_SESSION['tujuanpr'] = $data3['tujuanpr'];	  
$_SESSION['kategorianggaran'] = $data3['kategorianggaran'];	  
$hasil =  $koneksi_db->sql_query( "SELECT * FROM po_prdetail WHERE nopr='$_SESSION[kodepr]'" );
while ($data = $koneksi_db->sql_fetchrow($hasil)) { 
$kode=$data['kodebarang'];
$jumlah=$data['jumlah'];
$spesifikasi=$data['spesifikasi'];
$PRODUCTID = array ();
foreach ($_SESSION['product_id'] as $k=>$v){
$PRODUCTID[] = $_SESSION['product_id'][$k]['kode'];
}
if (!in_array ($kode, $PRODUCTID)){
$_SESSION['product_id'][] = array ('id' => $id,'kode' => $kode, 'jumlah' => $jumlah, 'spesifikasi' => $spesifikasi, 'harga' => '0', 'subdiscount' =>'0', 'subtotal' => '0', 'nilaidiscount' => '0');
}
}
}
}
if($_SESSION["kodesupplier"]!=''){
$supplier = '
		<td>Nama Supplier</td>
		<td>:</td>
		<td>'.getnamasupplier($_SESSION['kodesupplier']).'</td>';
}else{
$supplier = '
		<td></td>
		<td></td>
		<td></td>';	
	
}

if($_SESSION["kodepr"]!=''){
$datapr = '
		<tr>
		<td>Departemen</td>
		<td>:</td>
		<td>'.getdepartemen($_SESSION['departemenpr']).'</td>
		<td></td>
		<td></td>
		<td></td>
		</tr>
		';
}else{
$datapr = '';	
	
}

if(isset($_POST['batalpo'])){
porefresh();
$style_include[] ='<meta http-equiv="refresh" content="1; url=admin.php?pilih=po&mod=yes" />';
}

$user = $_SESSION['UserName'];
$tglnow = date("Y-m-d");
$nopo = generatepo();
$tgl 		= !isset($tgl) ? $tglnow : $tgl;
$kodesupplier 		= !isset($kodesupplier) ? $_SESSION['kodesupplier'] : $kodesupplier;
$kodepr 		= !isset($kodepr) ? $_SESSION['kodepr'] : $kodepr;
$discount 		= !isset($discount) ? '0' : $discount; 
$harga 		= !isset($harga) ? '0' : $harga; 
$carabayar = getcarabayar($kodesupplier);
$termin = gettermin($kodesupplier);
$termin 		= !isset($termin) ? '0' : $termin;
$sel2 = '<select name="carabayar" class="form-control">';
$arr2 = array ('Tunai','Hutang');
foreach ($arr2 as $kk=>$vv){
	if ($carabayar == $vv){
	$sel2 .= '<option value="'.$vv.'" selected="selected">'.$vv.'</option>';
	}else {
	$sel2 .= '<option value="'.$vv.'">'.$vv.'</option>';	
}
}

$sel2 .= '</select>'; 
 
$admin .= '
<div class="panel-heading"><b>Transaksi PO</b></div>';	
$admin .= '
<form method="post" action="" class="form-inline"id="posts">
<table class="table table-striped table-hover">';
$admin .= '
	<tr>
		<td>Nomor PO</td>
		<td>:</td>
		<td><input type="text" name="nopo" value="'.$nopo.'" class="form-control"></td>
'.$supplier.'
	</tr>';
$admin .= '
	<tr>
		<td>Tanggal</td>
		<td>:</td>
		<td><input type="text" id="tgl" name="tgl" value="'.$tgl.'" class="form-control">&nbsp;</td>
<td></td>
		<td></td>
		<td></td>
	</tr>';
	$admin .= '
	<tr>
		<td>Supplier</td>
		<td>:</td>
		<td><select class="form-select" name="kodesupplier"id="combobox">';
$hasil = $koneksi_db->sql_query( "SELECT * FROM po_supplier" );
while ($data = $koneksi_db->sql_fetchrow($hasil)) { 
$pilihan = ($data['kode']==$kodesupplier)?"selected":'';
	$admin .= '
			<option value="'.$data['kode'].'"'.$pilihan.'>'.$data['nama'].'</option>';
}
	$admin .= '</select>
			</td>
		<td>Cara Pembayaran</td>
		<td>:</td>
		<td>'.$sel2.'</td>
		</tr>
		</tr>';
	$admin .= '
	<tr>
		<td>Supplier</td>
		<td>:</td>
		<td><select class="form-select" name="kodepr"id="combobox2">';
$hasil = $koneksi_db->sql_query( "SELECT pr.nopr,pr.tgl,pr.namapr,pr.tujuanpr FROM po_pr pr ORDER BY pr.id desc" );
while ($data = $koneksi_db->sql_fetchrow($hasil)) { 
$pilihan = ($data['nopr']==$kodepr)?"selected":'';
	$admin .= '
			<option value="'.$data['nopr'].'"'.$pilihan.'>'.$data['nopr'].' ~ '.$data['namapr'].'</option>';
}
	$admin .= '</select>&nbsp;<input type="submit" value="Tambah PR" name="tambahpr"class="btn btn-success" >&nbsp;
			</td>		
		<td>Termin</td>
		<td>:</td>
		<td><input type="text" name="termin" value="'.$termin.'" class="form-control"> Hari</td>
		</tr>
				';
$admin .= $datapr;

$admin .= '	
</table></div>';	
if(($_SESSION["product_id"])!=""){
$no=1;
$admin .='<div class="panel panel-info">';
$admin .= '
<div class="panel-heading"><b>Detail Pemesanan</b></div>';	
$admin .= '
<table class="table table-striped table-hover">';
$admin .= '	
	<tr>
			<th><b>No</b></</th>
		<th><b>Kode</b></</th>
		<th><b>Nama</b></td>
		<th><b>Jumlah</b></</td>
		<th><b>Harga</b></</th>
<th><b>Discount</b></</th>
<th><b>SubDiscount</b></</th>
<th><b>Subtotal</b></</th>
		<th><b>Aksi</b></</th>
	</tr>';
	if ($_GET['editdetail']){
foreach ($_SESSION["product_id"] as $cart_itm)
        {
$array =$no-1;
$nilaidiscount=cekdiscount($cart_itm["subdiscount"],$cart_itm["harga"]);
$nilaidiscountsub=$nilaidiscount*$cart_itm["jumlah"];
$admin .= '
<form method="post" action="" class="form-inline"id="posts">';
$admin .= '	
	<tr>
			<td>'.$no.'</td>
			<td>'.$cart_itm["kode"].'</td>
		<td>'.getnamabarang($cart_itm["kode"]).'</td>
		<td><input align="right" type="text" name="jumlahpo['.$array.']" value="'.$cart_itm["jumlah"].'"class="form-control"></td>
		<td><input align="right" type="text" name="harga['.$array.']" value="'.$cart_itm["harga"].'"class="form-control"></td>
		<td><input align="right" type="text" name="subdiscount['.$array.']" value="'.$cart_itm["subdiscount"].'"class="form-control"></td>
	<td>'.$nilaidiscount.'</td>
		<td>'.$cart_itm["subtotal"].'</td>
		<td>
		<input type="hidden" name="kode" value="'.$cart_itm["kode"].'">
		<input type="submit" value="HAPUS" name="hapusbarang"class="btn btn-danger"></td>
	</tr>';
	$total +=$cart_itm["subtotal"];
	$no++;
		}
$admin .= '	
	<tr>
		<td colspan="8" ></td>
		<td ><input type="submit" value="SIMPAN" name="simpandetail"class="btn btn-warning" ></td>
	</tr>';
	$admin .= '
</form>';
	}else{
foreach ($_SESSION["product_id"] as $cart_itm)
        {
$nilaidiscount=cekdiscount($cart_itm["subdiscount"],$cart_itm["harga"]);
$admin .= '	
	<tr>
			<td>'.$no.'</td>
			<td>'.$cart_itm["kode"].'</td>
		<td>'.getnamabarang($cart_itm["kode"]).'</td>
		<td>'.$cart_itm["jumlah"].'</td>
		<td>'.$cart_itm["harga"].'</td>
		<td>'.$cart_itm["subdiscount"].'</td>
	<td>'.$nilaidiscount.'</td>
		<td>'.$cart_itm["subtotal"].'</td>
		<td>
		
		<input type="hidden" name="kode" value="'.$cart_itm["kode"].'"></td>
	</tr>';
	$total +=$cart_itm["subtotal"];
	$no++;
		}		
$admin .= '	
	<tr>
		<td colspan="8" ></td>
		<td ><a href="./admin.php?pilih=po&mod=yes&editdetail=ok" class="btn btn-warning">Edit Detail</a></td>
	</tr>';	
		
	}
$admin .= '	
	<tr>
		<td></td>
		<td></td>		
		<td colspan="6" align="right"><b>Total</b></td>
		<td ><input type="text" name="total" id="total"   class="form-control"  value="'.$total.'"/></td>
		<td></td>
	</tr>';
$admin .= '	
	<tr>
		<td></td>
		<td></td>		
		<td colspan="6" align="right"><b>Discount</b></td>
		<td ><input type="text" name="discount" id="discount"  required  class="form-control"  value="'.$discount.'"/></td>
		<td></td>
	</tr>';
$nilaidiscount2=cekdiscount($discount,$total);
$_SESSION['totalpo']=$total-$nilaidiscount2;
$admin .= '	
	<tr>';
$admin .= '<td colspan="7"></td>';
$admin .= '<td align="right"><b>Netto</b></td>
		<td ><input type="text" id="bayar"  name="bayar" value="'.$_SESSION['totalpo'].'"class="form-control" ></td>
		<td></td>
	</tr>
	';
	if ($_GET['editdetail']){
$admin .= '
<tr><td colspan="5"></td>
<td></td></tr>';
	}else{
$admin .= '<tr><td colspan="7"></td><td align="right"></td>
		<td><input type="hidden" name="user" value="'.$user.'">
		<input type="submit" value="Batal" name="batalpo"class="btn btn-danger" >
		<input type="submit" value="Simpan" name="submitpo"class="btn btn-success" >
		</td>
		<td></td></tr>';
		}
$admin .= '</table>';	
	}
$admin .= '</form></div>';	
}

if ($_GET['aksi'] == 'cetak'){
$kodepo     = $_POST['kodepo'];  
if(isset($_POST['batalcetak'])){
$style_include[] ='<meta http-equiv="refresh" content="1; url=admin.php?pilih=po&mod=yes&aksi=cetak" />';
}
$admin .= '
<div class="panel-heading"><b>Cetak Nota Purchase Order</b></div>';	
$admin .= '
<form method="post" action="" class="form-inline"id="posts">
<table class="table table-striped table-hover">';
	$admin .= '
	<tr>
		<td>Kode PO</td>
		<td>:</td>
		<td><select class="form-select" name="kodepo"id="combobox">';
$hasil = $koneksi_db->sql_query( "SELECT * FROM po_po ORDER BY id desc" );
while ($data = $koneksi_db->sql_fetchrow($hasil)) { 
$pilihan = ($data['nopo']==$kodepo)?"selected":'';
	$admin .= '
			<option value="'.$data['nopo'].'"'.$pilihan.'>'.$data['nopo'].' ~ '.getnamasupplier($data['kodesupplier']).'</option>';
}
	$admin .= '</select>&nbsp;<input type="submit" value="Lihat PO" name="lihatpo"class="btn btn-success" >&nbsp;<input type="submit" value="Batal" name="batalcetak"class="btn btn-danger" >&nbsp;
			</td>
		<td></td>
		<td></td>
		<td></td>
		</tr>';
$admin .= '</form></table></div>';	

if(isset($_POST['lihatpo'])){

$no=1;
$query 		= mysql_query ("SELECT * FROM `po_po` WHERE `nopo` like '$kodepo'");
$data 		= mysql_fetch_array($query);
$nopo  			= $data['nopo'];
$nopr  			= $data['nopr'];
$tgl  			= $data['tgl'];
$kodesupplier  			= $data['kodesupplier'];
$total  			= $data['total'];
$discount  			= $data['discount'];
$netto  			= $data['netto'];
$carabayar  			= $data['carabayar'];
$termin  			= $data['termin'];
	$error 	= '';
		if (!$nopo) $error .= "Error: kode PO tidak terdaftar , silahkan ulangi.<br />";
	if ($error){
		$admin .= '<div class="error">'.$error.'</div>';}else{
$admin .= '<div class="panel panel-info">
<div class="panel-heading"><b>Purchase Order</b></div>';
$admin .= '
		<form method="post" action="cetak_notapo.php" class="form-inline"id="posts"target="_blank">
<table class="table table-striped table-hover">';
$admin .= '
	<tr>
		<td>Nomor PO</td>
		<td>:</td>
		<td>'.$nopo.'</td>
		<td><input type="hidden" name="kode" value="'.$nopo.'">
		<input type="submit" value="Cetak Nota" name="cetak_notapo"class="btn btn-warning" >

		</td>
	</tr>';
$admin .= '
	<tr>
		<td>Nomor PR</td>
		<td>:</td>
		<td>'.$nopr.'</td>
		<td>
		</td>
	</tr>';
$admin .= '
	<tr>
		<td>Departemen</td>
		<td>:</td>
		<td>'.getdepartemendaripr($nopr).'</td>
		<td>
		</td>
	</tr>';
$admin .= '
	<tr>
		<td>Tanggal</td>
		<td>:</td>
		<td>'.tanggalindo($tgl).'</td>
		<td></td>
		</tr>';
$admin .= '
	<tr>
		<td>Supplier</td>
		<td>:</td>
		<td>'.getnamasupplier($kodesupplier).'</td>
			<td></td>
	</tr>';	
$admin .= '
	<tr>
		<td>Cara Bayar</td>
		<td>:</td>
		<td>'.($carabayar).'</td>
			<td></td>
	</tr>';	
$admin .= '
	<tr>
		<td>Termin</td>
		<td>:</td>
		<td>'.($termin).' Hari</td>
			<td></td>
	</tr>';	
$admin .= '</table>		</form></div>';	
$admin .='<div class="panel panel-info">';
$admin .= '
<div class="panel-heading"><b>Detail PO</b></div>';	
$admin .= '
<table class="table table-striped table-hover">';
$admin .= '	
	<tr>
			<th><b>No</b></</th>
		<th><b>Kode</b></</th>
		<th><b>Nama</b></td>
		<th><b>Jumlah</b></</td>
		<th><b>Harga</b></</th>
<th><b>Discount</b></</th>
<th><b>Subtotal</b></</th>
	</tr>';
$hasild = $koneksi_db->sql_query("SELECT * FROM `po_podetail` WHERE `nopo` like '$kodepo'");
while ($datad =  $koneksi_db->sql_fetchrow ($hasild)){
$admin .= '	
	<tr>
		<td>'.$no.'</td>
		<td>'.$datad["kodebarang"].'</td>
		<td>'.getnamabarang($datad["kodebarang"]).'</td>
		<td>'.$datad["jumlah"].'</td>
		<td>'.rupiah_format($datad["harga"]).'</td>
		<td>'.cekdiscountpersen($datad["subdiscount"]).'</td>
		<td>'.rupiah_format($datad["subtotal"]).'</td>
	</tr>';
	$no++;
		}
$admin .= '	
	<tr>		
		<td colspan="6" align="right"><b>Total</b></td>
		<td >'.rupiah_format($total).'</td>
	</tr>';
$admin .= '	
	<tr>	
		<td colspan="6" align="right"><b>Discount</b></td>
		<td >'.cekdiscountpersen($discount).'</td>
	</tr>';
$admin .= '	<tr>	
		<td colspan="6" align="right"><b>Grand Total</b></td>
		<td >'.rupiah_format($netto).'</td>
	</tr>
	';
$admin .= '</table></div>';	
		}
	}

	}

}
echo $admin;
?>
