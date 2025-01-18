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

// Handle form submission for answering questions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['submit_answer']) || isset($_POST['edit_answer'])) {
    try {
      $stmt = $pdo->prepare("UPDATE konsultasi SET jawaban = ? WHERE id = ? AND id_dokter = ?");
      $result = $stmt->execute([
        $_POST['jawaban'],
        $_POST['konsultasi_id'],
        $id
      ]);

      if ($result) {
        echo "<script>alert('Jawaban berhasil " . (isset($_POST['submit_answer']) ? "ditambahkan" : "diupdate") . "');</script>";
      } else {
        echo "<script>alert('Gagal " . (isset($_POST['submit_answer']) ? "menambahkan" : "mengupdate") . " jawaban');</script>";
      }
    } catch (PDOException $e) {
      echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }

    // Redirect to prevent resubmission
    echo "<meta http-equiv='refresh' content='0'>";
    exit();
  }
}

// Fetch consultations for the logged-in doctor
$query = "SELECT k.*, p.nama as nama_pasien, p.no_rm,
          DATE_FORMAT(k.tgl_konsultasi, '%d/%m/%Y %H:%i') as formatted_date
          FROM konsultasi k
          JOIN pasien p ON k.id_pasien = p.id
          WHERE k.id_dokter = ?
          ORDER BY k.tgl_konsultasi DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= getenv('APP_NAME') ?> | Konsultasi Dokter</title>

  <!-- Include your CSS files here -->
  <?php include "../../../layouts/head.php"; ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <?php include "../../../layouts/header.php" ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <!-- Content Header -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Daftar Konsultasi Pasien</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Konsultasi</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <div class="card">
            <div class="card-header bg-primary">
              <h3 class="card-title">Daftar Konsultasi</h3>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>No. RM</th>
                    <th>Nama Pasien</th>
                    <th>Subject</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $no = 0;
                  while ($row = $stmt->fetch()) {
                    $no++;
                  ?>
                    <tr>
                      <td><?= $no ?></td>
                      <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                      <td><?= htmlspecialchars($row['no_rm']) ?></td>
                      <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                      <td><?= htmlspecialchars($row['subject']) ?></td>
                      <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                      <td>
                        <?php if ($row['jawaban']): ?>
                          <?= nl2br(htmlspecialchars($row['jawaban'])) ?>
                        <?php else: ?>
                          <span class="badge badge-warning">Belum dijawab</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!$row['jawaban']): ?>
                          <button type="button" class="btn btn-primary btn-sm"
                            onclick="showAnswerModal(<?= $row['id'] ?>, '', false)">
                            Jawab
                          </button>
                        <?php else: ?>
                          <button type="button" class="btn btn-warning btn-sm"
                            onclick="showAnswerModal(<?= $row['id'] ?>, '<?= addslashes($row['jawaban']) ?>', true)">
                            Edit
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php
                  }
                  if ($no == 0) {
                    echo "<tr><td colspan='8' class='text-center'>Tidak ada data konsultasi</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Answer Modal -->
    <div class="modal fade" id="answerModal" tabindex="-1" aria-labelledby="answerModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="answerModalLabel">Berikan Jawaban</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form action="" method="POST">
            <div class="modal-body">
              <input type="hidden" name="konsultasi_id" id="konsultasi_id">
              <div class="form-group">
                <label for="jawaban">Jawaban</label>
                <textarea class="form-control" id="jawaban" name="jawaban" rows="4" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
              <button type="submit" id="submitButton" name="submit_answer" class="btn btn-primary">Kirim Jawaban</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php include "../../../layouts/footer.php"; ?>
  </div>

  <!-- Scripts -->
  <?php include "../../../layouts/pluginsexport.php"; ?>
  <?php include_once("../../../layouts/index.php"); ?>

  <script>
    function showAnswerModal(konsultasiId, jawaban = '', isEdit = false) {
      document.getElementById('konsultasi_id').value = konsultasiId;
      document.getElementById('jawaban').value = jawaban;

      // Update modal title and button based on whether it's an edit or new answer
      document.getElementById('answerModalLabel').textContent = isEdit ? 'Edit Jawaban' : 'Berikan Jawaban';
      const submitButton = document.getElementById('submitButton');
      submitButton.textContent = isEdit ? 'Update Jawaban' : 'Kirim Jawaban';
      submitButton.name = isEdit ? 'edit_answer' : 'submit_answer';
      submitButton.className = isEdit ? 'btn btn-warning' : 'btn btn-primary';

      $('#answerModal').modal('show');
    }

    // Clear form when modal is closed
    $('#answerModal').on('hidden.bs.modal', function() {
      document.getElementById('jawaban').value = '';
      document.getElementById('konsultasi_id').value = '';
      document.getElementById('submitButton').name = 'submit_answer';
      document.getElementById('submitButton').textContent = 'Kirim Jawaban';
      document.getElementById('submitButton').className = 'btn btn-primary';
    });
  </script>

</body>

</html>