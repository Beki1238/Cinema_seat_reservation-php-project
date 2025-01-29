<?php 

declare(strict_types=1);
include '../includes/Database.php';
include 'inserts.php';

$db = (new Database())->getConnection();
$employee = new inserts($db);

$emp_data = [];
if (isset($_GET['id'])) {
    $emp_data = $employee->getEmployee($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <title>Manage Employee</title>
  <style>
   
        .btn-orangered {
            background-color: orangered;
            color: white;
        }
        .btn-orangered:hover {
            background-color: #ff6347;
        }
    </style>
  </style>
</head>
<body>
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addEmployeeModalLabel">
          <?php echo isset($_GET['id']) ? 'Edit Employee' : 'Add New Employee'; ?>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="employeeForm" method="POST" action="ajax.php?action=save_employee">
        <div class="modal-body">
          <div class="form-group">
            <label>Employee ID</label>
            <input type="text" name="emp_id" id="emp_id" class="form-control"  required
                   value="<?php echo isset($emp_data['emp_id']) ? $emp_data['emp_id'] : ''; ?>"
                   <?php echo isset($_GET['id']) ?  : ''; ?>>
          </div>
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" name="fullName" id="fullName" class="form-control" 
                   value="<?php echo isset($emp_data['full_Name']) ? $emp_data['full_Name'] : ''; ?>" required>
          </div>
          <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" name="phone" id="phone" class="form-control" 
                   value="<?php echo isset($emp_data['phone']) ? $emp_data['phone'] : ''; ?>" 
                   pattern="^(09|07)[0-9]{8}$" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" 
                   value="<?php echo isset($emp_data['email']) ? $emp_data['email'] : ''; ?>" required>
          </div>
          <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" class="form-control" required>
              <option value="">Select</option>
              <option value="Manager" <?php echo (isset($emp_data['role']) && $emp_data['role'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
              <option value="Front_deskofficer" <?php echo (isset($emp_data['role']) && $emp_data['role'] == 'Front_deskofficer') ? 'selected' : ''; ?>>Front Desk Officer</option>
            </select>
          </div>
          <div class="form-group">
          <div class="form-group">
    <?php if (!isset($_GET['id'])): // Show only if no ID is present (adding a new employee) ?>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" class="form-control" 
               required minlength="8">
    <?php endif; ?>
</div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary btn-sm btn-orangered" id="saveEmployeeButton">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
  $(document).ready(function() {
    $('#addEmployeeModal').modal('show');

    $('#employeeForm').on('submit', function(event) {
    event.preventDefault();
    const formData = $(this).serialize();
    console.log('Form Data Sent:', formData); // Logs the data
    $.post($(this).attr('action'), formData, function(response) {
        console.log('Server Response:', response); // Logs server response
       if (response === '1') {
                    alert("Employee saved successfully!"); // Success alert
                    $('#addEmployeeModal').modal('hide'); // Hide modal
                    location.reload(); // Reload page to update employee list
                }
    });
});
  });
</script>
</body>
</html>
