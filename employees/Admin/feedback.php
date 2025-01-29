<?php
include '../includes/Database.php'; // Adjust the path as necessary
require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Admin') {
    echo "<h1>Access Denied</h1>";
    exit;
}

$database = new Database();
$db = $database->getConnection();

$query = $db->prepare("SELECT email, feedback, submission_date FROM feedbacks ORDER BY submission_date DESC");
$query->execute();
$result = $query->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Review</title>
    <link rel="stylesheet" href="styles/admin.css"> <!-- Your admin styles -->
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 20px;
}

h1 {
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f4f4f4;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f9f9f9;
}

tr:hover {
    background-color: #f1f1f1;
}

    </style>
</head>
<body>
    <h1>Feedback Review</h1>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>Email</th>
                <th>Feedback</th>
                <th>Submission Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['feedback']); ?></td>
                        <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No feedbacks available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
