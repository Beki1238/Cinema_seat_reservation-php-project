<?php
include '../includes/Database.php';
include 'inserts.php';

require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Admin') {
    echo "<h1>Access Denied</h1>";
    exit;
}

$db = (new Database())->getConnection();
$employee = new inserts($db);
$employees = $employee->getAllEmployees();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> <!-- Include CSS and JS -->
  <style>
      .btn-orangered {
            background-color: orangered;
            color: white;
        }
        .btn-orangered:hover,btn-orangered:active {
            background-color: #ff6347;
        }
  </style>
</head>
<body>
<button type="button" class="btn btn-block btn-sm btn-orangered col-sm-2" id="addEmployeeButton">
    Add Employee
  </button>

  <!-- Employee Table -->
  <div class="container-fluid">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 >Employee Profile</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
    
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Start Date</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>
                <tr>
                <td><?= $emp['emp_id']; ?></td>
                    <td><?= $emp['full_Name']; ?></td>
                    <td><?= $emp['email']; ?></td>
                    <td><?= $emp['phone']; ?></td>
                     <td><?= $emp['role']; ?></td>
                    <td><?= $emp['created_at']; ?></td>
                    <td>
    <!-- Action button with dropdown menu -->
<center>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-orangered">Action</button>
                                            <button type="button" class="btn btn-primary dropdown-toggle btn-orangered dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="sr-only btn-orangered">Toggle Dropdown</span>
                                            </button>
                                            <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" onclick="editEmployee('<?= $emp['emp_id']; ?>')">Edit</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="deleteEmployee('<?= $emp['emp_id']; ?>')">Delete</a>
                                            </div>
                                        </div>
                                    </center>
                  </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div id="modalPlaceholder"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
         // to Manually initialize the dropdown to ensure it's functioning
    $('.dropdown-toggle').dropdown();
        $('#addEmployeeButton').on('click', () => {
            $('#modalPlaceholder').load('manage_employee.php', () => {
                $('#addEmployeeModal').modal('show');
            });
        });

        function editEmployee(id) {
            $('#modalPlaceholder').load(`manage_employee.php?id=${id}`, () => {
                $('#addEmployeeModal').modal('show');
            });
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure?')) {
                $.post('ajax.php?action=delete_employee', { emp_id: id }, response => {
                    alert(response);
                    location.reload();
                });
            }
        }
     

    </script>
</body>
</html>
