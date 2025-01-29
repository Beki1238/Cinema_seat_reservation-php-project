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
$movieManager = new inserts($db);
$movies = $movieManager->getAllMovies();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie List</title>
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
<body>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-block btn-sm btn-primary btn-orangered col-sm-2" type="button" id="new_movie">
                <i class="fa fa-plus"></i> New Movie
            </button>
        </div>
    </div>
    <div class="row">
        <div class="card col-md-12 mt-3">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Cover</th>
                        <th class="text-center">Title</th>
                        <th class="text-center">Description</th>
                        <th class="text-center">Showing status</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    foreach ($movies as $movie):
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><center><img src="./assets/img/<?php echo $movie['cover_img']; ?>" alt="Cover"></center></td>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td><?php echo htmlspecialchars($movie['description']); ?></td>
                            <td><?php echo htmlspecialchars($movie['status']); ?></td>
                            <td>
                                    <center>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-orangered">Action</button>
                                            <button type="button" class="btn btn-primary dropdown-toggle btn-orangered dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="sr-only btn-orangered">Toggle Dropdown</span>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item edit_movie" href="javascript:void(0)" data-id='<?php echo $movie['id']; ?>'>Edit</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item delete_movie" href="javascript:void(0)"  data-id='<?php echo $movie['id']; ?>'>Delete</a>
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
<div class="modal fade" id="uni_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $('#new_movie').click(function () {
        uni_modal('Add New Movie', 'manage_movies.php');
    });

    $('.edit_movie').click(function () {
        uni_modal('Edit Movie', 'manage_movies.php?id=' + $(this).data('id'));
    });

    $('.delete_movie').click(function () {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this movie?')) {
            $.post('ajax.php?action=delete_movie', {id: id}, function (resp) {
                if (resp !== '1') {
                    alert('Movie successfully deleted.');
                    location.reload();
                } else {
                    alert('Failed to delete movie.');
                }
            });
        }
    });
    $('.dropdown-toggle').dropdown();
    function uni_modal(title, url) {
        $.get(url, function (response) {
            $('#uni_modal .modal-title').text(title);
            $('#uni_modal .modal-body').html(response);
            $('#uni_modal').modal('show');
        }).fail(function () {
            alert('Failed to load content.');
        });
    }
</script>
</body>
</html>
