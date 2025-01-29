<?php
declare(strict_types=1);

include '../includes/Database.php';
include 'inserts.php';

$db = (new Database())->getConnection();
$cinema = new inserts($db);
 
$meta = isset($_GET['id']) ? $cinema->getRoomById($_GET['id']) : [];
?>
<div class="container-fluid">
    <div class="col-lg-12">
        <form id="manage-theater">
            <div class="form-group">
            <input type="hidden" name="id" value="<?php echo $meta['id'] ?? '' ?>">
<label for="room-name" class="control-label">Room Name</label>
<input type="text" name="name" id="room-name" class="form-control" value="<?php echo $meta['name'] ?? '' ?>" required>

            <div class="form-group">
            <label for="room-type" class="control-label">Room Type</label>
                <select id="room-type" name="room_type" required class="form-control" required>
                <option value="">Select</option>
                    <option value="VIP" <?php echo isset($meta['room_type']) && $meta['room_type'] == 'VIP' ? 'selected' : '' ?>>VIP</option>
                    <option value="Regular" <?php echo isset($meta['room_type']) && $meta['room_type'] == 'Regular' ? 'selected' : '' ?>>Regular</option>
                    <option value="Premium" <?php echo isset($meta['room_type']) && $meta['room_type'] == 'Premium' ? 'selected' : '' ?>>Premium</option>
                </select>

            </div>
            <div class="form-group">
    <label for="capacity" class="control-label">Capacity</label>
    <input type="number" name="capacity" id="capacity" class="form-control" value="<?php echo $meta['capacity'] ?? '' ?>" required readonly>
</div>

            <div class="form-group">
                <label for="base-price" class="control-label">Base Price</label>
                <input type="number" name="base_price" id="base-price" required class="form-control" value="<?php echo $meta['base_price'] ?? '' ?>">
            </div>
        </form>
    </div>
</div>

<script>
$('#manage-theater').submit(function(e) {
    e.preventDefault();
    const form = this;
    if (!form.checkValidity()) {
        form.reportValidity(); // This will show the validation messages
        return;
    }
 document.getElementById('room-name').addEventListener('input', function() {
    // Restrict to letters only (A-Z, a-z) and spaces
    this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); // Allow only letters and spaces
});


        // Allow only numeric characters (no special characters or letters)
        function allowOnlyNumbers(input) {
            input.value = input.value.replace(/[^0-9]/g, ''); // Keep only numbers
        }
    $.ajax({
        url: 'ajax.php?action=save_theater',
        method: 'POST',
        data: $(this).serialize(),
        success: function(resp) {
            if (resp == 1) {
                alert('Cinema Room saved successfully!');
                location.reload();
            } else {
                alert('An error occurred while saving all forms not filled or not used required formate.');
            }
        }
    });
});
</script>
