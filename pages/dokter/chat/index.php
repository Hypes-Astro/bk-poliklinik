<?php
include_once("../../../config/conn.php");
session_start();

if (isset($_SESSION['login'])) {
  $_SESSION['login'] = true;
} else {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

$nama = $_SESSION['username'];
$akses = $_SESSION['akses'];
$id = $_SESSION['id'];

if ($akses != 'dokter') {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}
?>

<?php
$title = 'Poliklinik | Chat Pasien';
// Breadcrumb section
ob_start(); ?>
<ol class="breadcrumb float-sm-right">
  <li class="breadcrumb-item"><a href="<?= $base_dokter; ?>">Home</a></li>
  <li class="breadcrumb-item active">Forum Chat Pasien</li>
</ol>
<?php
$breadcrumb = ob_get_clean();

// Title Section
ob_start(); ?>
Chat Pasien
<?php
$main_title = ob_get_clean();

// Content section
ob_start();
?>
<div class="card">
  <div class="card-header">
    <div class="row">
      <div class="col-6">
        <h3 class="card-title">Daftar Pesan</h3>
      </div>

    </div>
  </div>
  <div class="card-body">
    <div class="row">
      <?php
      $data = $pdo->prepare("
      ");
      $data->bindParam(':id_dokter', $id, PDO::PARAM_INT);
      $data->execute();

      if ($data->rowCount() == 0) {
        echo "<div class='col-12'><p class='text-center'>Tidak ada data</p></div>";
      } else {
        while ($d = $data->fetch()) {
      ?>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div class="info">
                    <h5 class="card-title">Nama Pasien: <?= htmlspecialchars($d['nama_dokter']) ?></h5>
                    <p class="card-text">Tanggal: <?= htmlspecialchars($d['hari']) ?></p>
                  </div>
                  <p class="card-text">Status: <?= htmlspecialchars($d['aktif'] == 'Y' ? 'Aktif' : 'Tidak Aktif') ?></p>
                </div>
                <div class="info">
                  <h5 class="card-title text-bold">Pertanyaan</h5>
                  <p class="card-text">Saya suka sama rinrin, dia suka ga ya?</p>
                </div>

                <div class="info">
                  <h5 class="card-title text-bold">Jawaban</h5>
                  <p class="card-text">Iya kot</p>
                </div>

                <a href="edit.php/<?= htmlspecialchars($d['id']) ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Jawab</a>
              </div>
            </div>
          </div>
      <?php
        }
      }
      ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();

// JS Section
ob_start(); ?>
<script>
  $(document).ready(function() {
    $('.delete-button').on('click', function(e) {
      return confirm('Apakah anda yakin ingin menghapus data ini?');
    });
  });
</script>
<?php
$js = ob_get_clean();

?>

<?php include_once("../../../layouts/index.php"); ?>