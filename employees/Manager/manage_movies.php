<?php 
include '../includes/Database.php';
include 'inserts.php';

$db = (new Database())->getConnection();
$movieManager = new inserts($db);

$movieData = [];
if (isset($_GET['id'])) {
    $movieData = $movieManager->getMovieById($_GET['id']);
}

?>
<div class="container-fluid">
    <div class="col-lg-12">
        <form id="manage-movie" enctype="multipart/form-data"> <!-- Ensure the form can handle file uploads -->
            <input type="hidden" name="id" value="<?php echo isset($movieData['id']) ? $movieData['id'] : ''; ?>">
            <div class="form-group">
                <label for="title" class="control-label">Movie Title</label>
                <input type="text" id="title" name="title" required class="form-control" value="<?php echo isset($movieData['title']) ? htmlspecialchars($movieData['title']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="description" class="control-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" required><?php echo isset($movieData['description']) ? htmlspecialchars($movieData['description']) : ''; ?></textarea>
            </div>
            <div class="form-group row">
                <label for="duration" class="control-label col-md-12">Duration</label>
                <input type="number" name="duration_hour" class="form-control col-sm-2 offset-md-1" max="12" min="0" required value="<?php echo isset($movieData['duration_hour']) ? $movieData['duration_hour'] : ''; ?>" placeholder="Hours">
                <input type="number" name="duration_min" class="form-control col-sm-2" max="59" min="0" required value="<?php echo isset($movieData['duration_min']) ? $movieData['duration_min'] : ''; ?>" placeholder="Minutes">
            </div>
            <div class="form-group">
                <label for="release_date" class="control-label">Release Date</label>
                <input name="release_date" id="release_date" type="date" class="form-control" required value="<?php echo isset($movieData['release_date']) ? $movieData['release_date'] : ''; ?>">

            </div>
           
            <div class="form-group">
        <label  for="youtube_link" class="control-label">YouTube Link</label>
        <input type="url" name="youtube_link" class="form-control" value="<?php echo htmlspecialchars($movieData['youtube_link'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=XXXXX">
    </div>
            <div class="form-group">
                <label class="control-label">Cover Image</label>
                <?php if (isset($movieData['cover_img'])): ?>
                    <img src="./assets/img/<?php echo $movieData['cover_img']; ?>" alt="" id="cover_img_preview" width="100">
                <?php endif; ?>
                <input type="file" name="cover" class="form-control-file" onchange="previewCoverImage(this)">
            </div>
             <!-- Status Field -->
             <div class="form-group">
                <label for="status" class="control-label">Status</label>
                <select name="status" class="form-control" required>
                    <option value="active" <?php echo isset($movieData['status']) && $movieData['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo isset($movieData['status']) && $movieData['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Preview uploaded cover image
    function previewCoverImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const imgPreview = document.getElementById('cover_img_preview');
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // AJAX submit for the form
    $('#manage-movie').submit(function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: 'ajax.php?action=save_movie',
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.trim() !== '1') {
                    alert('Movie saved successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + response);
                }
            },
            error: function () {
                alert('An error occurred while processing the request.');
            }
        });
    });
</script>
