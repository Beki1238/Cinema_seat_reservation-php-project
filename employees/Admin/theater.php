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
$cinema = new inserts($db);
$cinemaRooms = $cinema->getAllRooms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theater Management</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .btn-orangered {
            background-color: orangered;
            color: white;
        }
        .btn-orangered:hover {
            background-color: #ff6347;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-block btn-sm btn-orangered col-sm-2" type="button" id="new_theater">
                <i class="fa fa-plus"></i> New Cinema Room
            </button>
        </div>
    </div>
    <div class="row">
        <div class="card col-md-8 mt-3">
            <div class="card-header">
                <large>Cinema Rooms</large>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Capacity</th>
                            <th class="text-center">Room Type</th>
                            <th class="text-center">Base Price</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($cinemaRooms as $room) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $room['name']; ?></td>
                                <td><?php echo $room['capacity']; ?></td>
                                <td><?php echo $room['room_type']; ?></td>
                                <td><?php echo $room['base_price']; ?></td>
                                <td>
                                    <center>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-sm btn-orangered">Action</button>
                                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle btn-orangered dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item edit_theater" href="javascript:void(0)" data-id="<?php echo $room['id']; ?>">Edit</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item delete_theater" href="javascript:void(0)" data-id="<?php echo $room['id']; ?>">Delete</a>
                                            </div>
                                        </div>
                                    </center>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="uni_modal" role='dialog'>
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm btn-orangered" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function uni_modal(title, url) {
    $.ajax({
        url: url,
        success: function(response) {
            $('#uni_modal .modal-title').html(title);
            $('#uni_modal .modal-body').html(response);
            $('#uni_modal').modal('show');
        },
        error: function() {
            alert("An error occurred while loading the modal.");
        }
    });
}
$('.dropdown-toggle').dropdown();
$('#new_theater').click(function() {
    uni_modal('New Cinema Room', 'manage_theater.php');
});

$('.edit_theater').click(function() {
    uni_modal('Edit Cinema Room', 'manage_theater.php?id=' + $(this).attr('data-id'));
});

$('.delete_theater').click(function() {
    if (confirm('Are you sure you want to delete this cinema room?')) {
        $.ajax({
            url: 'ajax.php?action=delete_theater',
            method: 'POST',
            data: { id: $(this).attr('data-id') },
            success: function(resp) {
                if (resp == 1) {
                    alert("Cinema Room successfully deleted");
                    location.reload();
                } else {
                    alert("An error occurred");
                }
            }
        });
    }
});
</script>
</body>
</html>
