<?php
include '../includes/Database.php';
include 'inserts.php';
require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Manager') {
    echo "<h1>Access Denied</h1>";
    exit;
}
$db = (new Database())->getConnection();
$showManager = new inserts($db);

$showtimes = $showManager->getAllShows();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>showtime List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        td img {
            width: 50px;
            height: 75px;
            margin: auto;
        }
        .btn-orangered {
            background-color: orangered;
            color: white;
        }
        .btn-orangered:hover {
            background-color: #ff6347;
        }
    </style>
</head>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-block btn-sm btn-primary btn-orangered col-sm-2" type="button" id="new_showtime">
                <i class="fa fa-plus"></i> New Showtime
            </button>
        </div>
    </div>
    <div class="row mt-3">
        <div class="card col-md-12">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Movie</th>
                            <th class="text-center">Room</th>
                            <th class="text-center">Start Time</th>
                            <th class="text-center">Additional showprice</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($showtimes as $index => $showtime): ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td><?= htmlspecialchars($showtime['movie']); ?></td>
                                <td><?= htmlspecialchars($showtime['room']); ?></td>
                                <td><?= htmlspecialchars($showtime['start_time']); ?></td>
                                <td><?= htmlspecialchars($showtime['price']); ?></td>
                                <td>
    <center>
        <div class="btn-group">
            <button type="button" class="btn btn-primary btn-orangered">Action</button>
            <button type="button" class="btn btn-primary dropdown-toggle btn-orangered dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only btn-orangered">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item edit_showtime" href="javascript:void(0)" data-id="<?= $showtime['id']; ?>">Edit</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item delete_showtime" href="javascript:void(0)" data-id="<?= $showtime['id']; ?>">Delete</a>
            </div>
        </div>
    </center>
</td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="showtimeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showtimeModalTitle">Manage Showtime</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="save_showtime">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Open New Showtime Modal
    $('#new_showtime').click(function () {
        loadModalContent('New Showtime', 'manage_Showtime.php');
    });

    // Open Edit Showtime Modal
    $('.edit_showtime').click(function () {
        const id = $(this).data('id');
        loadModalContent('Edit Showtime', 'manage_Showtime.php?id=' + id);
    });

    // Delete Showtime
    $('.delete_showtime').click(function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this showtime?')) {
            $.post('ajax.php?action=delete_showtime', { id: id }, function (response) {
                if (response.trim() == '1') {
                    alert('Showtime successfully deleted.');
                    location.reload();
                } else {
                    alert('Showtime successfully deleted');
                     location.reload();
                }
            });
        }
    });

    // Load Modal Content
    function loadModalContent(title, url) {
        $('#showtimeModal .modal-title').text(title);
        $('#showtimeModal .modal-body').load(url, function () {
            $('#showtimeModal').modal('show');
        });
    }
});
</script>
