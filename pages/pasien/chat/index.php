<?php
include_once("../../../config/conn.php");
session_start();

if (isset($_SESSION['signup']) || isset($_SESSION['login'])) {
  $_SESSION['signup'] = true;
  $_SESSION['login'] = true;
} else {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}
$id_pasien = $_SESSION['id'];
$no_rm = $_SESSION['no_rm'];
$nama = $_SESSION['username'];
$akses = $_SESSION['akses'];

if ($akses != 'pasien') {
  echo "<meta http-equiv='refresh' content='0; url=..'>";
  die();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['submit'])) {
    // Handle new consultation submission
    try {
      $stmt = $pdo->prepare("INSERT INTO konsultasi (id_pasien, id_dokter, subject, pertanyaan, tgl_konsultasi) 
                            VALUES (?, ?, ?, ?, NOW())");
      $result = $stmt->execute([
        $_POST['id_pasien'],
        $_POST['id_dokter'],
        $_POST['Subject'],
        $_POST['Pertanyaan']
      ]);

      if ($result) {
        echo "<script>alert('Pertanyaan berhasil ditambahkan');</script>";
      } else {
        echo "<script>alert('Gagal menambahkan pertanyaan');</script>";
      }
    } catch (PDOException $e) {
      echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
  } elseif (isset($_POST['update'])) {
    // Handle update consultation
    try {
      $stmt = $pdo->prepare("UPDATE konsultasi 
                            SET subject = ?, pertanyaan = ? , id_dokter = ? ,
                            WHERE id = ? AND id_pasien = ?");
      $result = $stmt->execute([
        $_POST['id_dokter'],
        $_POST['Subject'],
        $_POST['Pertanyaan'],
        $_POST['konsultasi_id'],
        $_SESSION['id']
      ]);

      if ($result) {
        echo "<script>alert('Pertanyaan berhasil diupdate');</script>";
      } else {
        echo "<script>alert('Gagal mengupdate pertanyaan');</script>";
      }
    } catch (PDOException $e) {
      echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
  } elseif (isset($_POST['delete'])) {
    // Handle delete consultation
    try {
      $stmt = $pdo->prepare("DELETE FROM konsultasi WHERE id = ? AND id_pasien = ?");
      $result = $stmt->execute([
        $_POST['konsultasi_id'],
        $_SESSION['id']
      ]);

      if ($result) {
        echo "<script>alert('Pertanyaan berhasil dihapus');</script>";
      } else {
        echo "<script>alert('Gagal menghapus pertanyaan');</script>";
      }
    } catch (PDOException $e) {
      echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
  }

  // Redirect after any form submission to prevent resubmission
  echo "<meta http-equiv='refresh' content='0'>";
  exit();
}


$query = "SELECT k.*, d.nama as dokter_nama, d.id_poli,
          DATE_FORMAT(k.tgl_konsultasi, '%d/%m/%Y %H:%i') as formatted_date
          FROM konsultasi k
          LEFT JOIN dokter d ON k.id_dokter = d.id
          WHERE k.id_pasien = ?
          ORDER BY k.tgl_konsultasi DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$id_pasien]);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= getenv('APP_NAME') ?> | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../../dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="http://<?= $_SERVER['HTTP_HOST'] ?>/bk-poliklinik/plugins/summernote/summernote-bs4.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">


    <?php include "../../../layouts/header.php" ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Daftar Pertanyaan</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Daftar Poli</li>

              </ol>
            </div><!-- /.col -->
            <button class="btn btn-primary mt-2 ml-2">tambah pertanyaan</button>
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">

          <div class="">

            <!-- tambah pertanyaan bikin pop up modal -->


            <div class="">
              <!-- Registration poli history -->
              <div class="card">
                <h5 class="card-header bg-primary">Riwayat daftar poli</h5>
                <div class="card-body">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Tanggal Konsultasi</th>
                        <th scope="col">Dokter</th>
                        <th scope="col">Subject</th>
                        <th scope="col">Pertanyaan</th>
                        <th scope="col">Jawaban</th>
                        <th scope="col">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $no = 0;
                      if ($stmt->rowCount() == 0) {
                        echo "<tr><td colspan='7' align='center'>Tidak ada data</td></tr>";
                      } else {
                        while ($row = $stmt->fetch()) {
                          $no++;
                      ?>
                          <tr>
                            <th scope="row"><?= $no ?></th>
                            <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                            <td><?= htmlspecialchars($row['dokter_nama']) ?></td>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td><?= htmlspecialchars($row['pertanyaan']) ?></td>
                            <td>
                              <?php if ($row['jawaban']): ?>
                                <?= htmlspecialchars($row['jawaban']) ?>
                              <?php else: ?>
                                <span class="badge bg-warning">Menunggu jawaban</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <?php if (!$row['jawaban']): ?>
                                <button class="btn btn-warning btn-sm"
                                  onclick="editKonsultasi(
    <?= $row['id'] ?>, 
    '<?= addslashes($row['subject']) ?>', 
    '<?= addslashes($row['pertanyaan']) ?>', 
    '<?= $row['id_poli'] ?>', 
    '<?= $row['id_dokter'] ?>'
  )">
                                  Edit
                                </button>
                              <?php endif; ?>
                              <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="konsultasi_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?')">Delete</button>
                              </form>
                            </td>

                          </tr>
                      <?php
                        }
                      }
                      ?>
                    </tbody>
                  </table>

                </div>
              </div>
              <!-- End registration poli history -->
            </div>
          </div>

        </div><!-- /.container-fluid -->
      </section>
      <!-- /.content -->
    </div>

    <!-- Add this modal markup just before the closing body tag -->
    <div class="modal fade" id="tambahPertanyaanModal" tabindex="-1" aria-labelledby="tambahPertanyaanModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="tambahPertanyaanModalLabel">Daftar Poli</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="POST">
              <input type="hidden" value="<?= $id_pasien ?>" name="id_pasien">

              <div class="mb-3">
                <label for="inputPoli" class="form-label">Pilih Poli</label>
                <select id="inputPoli" class="form-control">
                  <option>Open this select menu</option>
                  <?php
                  $data = $pdo->prepare("SELECT * FROM poli");
                  $data->execute();
                  if ($data->rowCount() == 0) {
                    echo "<option>Tidak ada poli</option>";
                  } else {
                    while ($d = $data->fetch()) {
                  ?>
                      <option value="<?= $d['id'] ?>"><?= $d['nama_poli'] ?></option>
                  <?php
                    }
                  }
                  ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="inputDokter" class="form-label">Pilih Dokter</label>
                <select id="inputDokter" name="id_dokter" class="form-control">
                  <option value="">Pilih Dokter</option>
                  <?php
                  $dataDokter = $pdo->prepare("SELECT dokter.id AS id_dokter, dokter.nama, poli.id AS id_poli 
                           FROM dokter 
                           JOIN poli ON dokter.id_poli = poli.id");
                  $dataDokter->execute();
                  while ($dokter = $dataDokter->fetch()) {
                    echo "<option value='" . $dokter['id_dokter'] . "' data-poli='" . $dokter['id_poli'] . "'>" . htmlspecialchars($dokter['nama']) . "</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="Subject" class="form-label">Subject</label>
                <textarea class="form-control" id="Subject" rows="3" name="Subject"></textarea>
              </div>

              <div class="mb-3">
                <label for="Pertanyaan" class="form-label">Pertanyaan</label>
                <textarea class="form-control" id="Pertanyaan" rows="3" name="Pertanyaan"></textarea>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" name="submit" class="btn btn-primary">Daftar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>


    <!-- EDIT -->

    <div class="modal fade" id="editPertanyaanModal" tabindex="-1" aria-labelledby="editPertanyaanModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-warning text-white">
            <h5 class="modal-title" id="editPertanyaanModalLabel">Edit Pertanyaan</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="POST" id="editForm">
              <input type="hidden" name="konsultasi_id" id="edit_konsultasi_id">
              <input type="hidden" value="<?= $id_pasien ?>" name="id_pasien">

              <div class="mb-3">
                <label for="editPoli" class="form-label">Pilih Poli</label>
                <select id="editPoli" class="form-control">
                  <option value="">Pilih Poli</option>
                  <?php
                  $data = $pdo->prepare("SELECT * FROM poli");
                  $data->execute();
                  while ($d = $data->fetch()) {
                    echo "<option value='" . $d['id'] . "'>" . htmlspecialchars($d['nama_poli']) . "</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="editDokter" class="form-label">Pilih Dokter</label>
                <!-- Di modal edit -->
                <select id="editDokter" name="id_dokter" class="form-control" required>
                  <option value="">Pilih Dokter</option>
                  <?php
                  $dataDokter = $pdo->prepare("SELECT dokter.id AS id_dokter, dokter.nama, poli.id AS id_poli 
                               FROM dokter 
                               JOIN poli ON dokter.id_poli = poli.id");
                  $dataDokter->execute();
                  while ($dokter = $dataDokter->fetch()) {
                    echo "<option value='" . $dokter['id_dokter'] . "' data-poli='" . $dokter['id_poli'] . "'>" .
                      htmlspecialchars($dokter['nama']) . "</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="mb-3">
                <label for="editSubject" class="form-label">Subject</label>
                <textarea class="form-control" id="editSubject" rows="3" name="Subject"></textarea>
              </div>

              <div class="mb-3">
                <label for="editPertanyaan" class="form-label">Pertanyaan</label>
                <textarea class="form-control" id="editPertanyaan" rows="3" name="Pertanyaan"></textarea>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" name="update" class="btn btn-warning">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Update the JavaScript code -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const editPoliSelect = document.getElementById('editPoli');
        const editDokterSelect = document.getElementById('editDokter');

        // Function to hide all doctor options
        function hideAllDokterOptions(dokterSelect) {
          Array.from(dokterSelect.options).forEach(option => {
            if (option.value !== '') { // Skip the placeholder option
              option.style.display = 'none';
            }
          });
        }

        // Show doctors based on selected poli



        editPoliSelect.addEventListener('change', function() {
          const selectedPoliId = this.value;
          const editDokterSelect = document.getElementById('editDokter');

          // Show all options first
          Array.from(editDokterSelect.options).forEach(option => {
            option.style.display = '';
          });

          // Hide irrelevant doctors
          Array.from(editDokterSelect.options).forEach(option => {
            if (option.dataset.poli !== selectedPoliId && option.value !== '') {
              option.style.display = 'none';
            }
          });
        });

        // Function to handle editing existing consultation
        window.editKonsultasi = function(id, subject, pertanyaan, poliId, dokterId) {
          // Debug
          console.log("Edit values:", {
            id,
            subject,
            pertanyaan,
            poliId,
            dokterId
          });

          // Fill form with existing data
          document.getElementById("edit_konsultasi_id").value = id;
          document.getElementById("editSubject").value = subject;
          document.getElementById("editPertanyaan").value = pertanyaan;

          // Set poli and show relevant doctors
          const editPoliSelect = document.getElementById("editPoli");
          const editDokterSelect = document.getElementById("editDokter");

          // Set poli value
          editPoliSelect.value = poliId;

          // Show all doctor options first
          Array.from(editDokterSelect.options).forEach(option => {
            option.style.display = '';
          });

          // Set dokter value
          editDokterSelect.value = dokterId;

          // Filter doctors based on selected poli
          Array.from(editDokterSelect.options).forEach(option => {
            if (option.dataset.poli !== poliId && option.value !== '') {
              option.style.display = 'none';
            }
          });

          // Debug
          console.log("Selected dokter value:", editDokterSelect.value);

          // Show the modal
          $('#editPertanyaanModal').modal('show');
        };




        // Add form validation for edit form
        document.getElementById('editForm').addEventListener('submit', function(e) {
          const dokter = document.getElementById('editDokter').value;
          console.log("Selected doctor value on submit:", dokter); // Debug

          if (!dokter) {
            e.preventDefault();
            alert('Mohon pilih dokter');
            return false;
          }

          // Debug: Log semua data form
          const formData = new FormData(this);
          console.log("Form data being submitted:");
          for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
          }
        });


        document.getElementById('editDokter').addEventListener('change', function() {
          document.getElementById('selected_dokter').value = this.value;
        });

        // Reset edit form on modal close
        $('#editPertanyaanModal').on('hidden.bs.modal', function() {
          document.getElementById('editForm').reset();
          hideAllDokterOptions(editDokterSelect);
        });
      });
    </script>

    <script>
      // Wait for document to be ready
      document.addEventListener('DOMContentLoaded', function() {
        // Get the required elements
        const poliSelect = document.getElementById('inputPoli');
        const dokterSelect = document.getElementById('inputDokter');
        let isEditMode = false;
        let currentKonsultasiId = null;

        // Hide all doctor options initially
        function hideAllDokterOptions() {
          Array.from(dokterSelect.options).forEach(option => {
            if (option.value !== '') { // Skip the placeholder option
              option.style.display = 'none';
            }
          });
        }

        // Show doctors based on selected poli
        poliSelect.addEventListener('change', function() {
          const selectedPoliId = this.value;
          hideAllDokterOptions();

          // Reset dokter selection
          dokterSelect.value = '';

          // Show only doctors from selected poli
          Array.from(dokterSelect.options).forEach(option => {
            if (option.dataset.poli === selectedPoliId) {
              option.style.display = '';
            }
          });
        });

        // Function to handle modal opening for new consultation
        document.querySelector('.btn-primary.mt-2').addEventListener('click', function() {
          isEditMode = false;
          currentKonsultasiId = null;

          // Reset form
          document.querySelector('form').reset();
          hideAllDokterOptions();

          // Update modal title
          document.getElementById('tambahPertanyaanModalLabel').textContent = 'Tambah Pertanyaan';

          // Show the modal
          $('#tambahPertanyaanModal').modal('show');
        });


        $('#tambahPertanyaanModal').on('hidden.bs.modal', function() {
          const form = document.querySelector("form");
          const submitButton = form.querySelector('button[type="submit"]');

          form.reset();
          submitButton.name = "submit";
          submitButton.textContent = "Daftar";

          // Remove konsultasi_id input if it exists
          const hiddenInput = document.querySelector('input[name="konsultasi_id"]');
          if (hiddenInput) {
            hiddenInput.remove();
          }

          // Reset doctor selection visibility
          hideAllDokterOptions();
        });

        // Add form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
          const subject = document.getElementById('Subject').value.trim();
          const pertanyaan = document.getElementById('Pertanyaan').value.trim();
          const dokter = document.getElementById('inputDokter').value;

          if (!subject || !pertanyaan || !dokter) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang diperlukan');
            return false;
          }
        });
      });
    </script>

    <?php include "../../../layouts/footer.php"; ?>
  </div>
  <!-- ./wrapper -->
  <?php include "../../../layouts/pluginsexport.php"; ?>

</body>

</html>