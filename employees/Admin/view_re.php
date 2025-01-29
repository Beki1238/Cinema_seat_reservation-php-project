<?php

require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Admin') {
    echo "<h1>Access Denied</h1>";
    exit;
}

$reports_dir = '../Front_desk_Officer/reports/';

// Check if the directory exists
if (!is_dir($reports_dir)) {
    echo "<p>No reports available at the moment.</p>";
    exit;
}

// Get the list of PDF files in the reports directory
$report_files = array_diff(scandir($reports_dir), array('..', '.')); // Remove '.' and '..' from the list

// If there are no reports
if (empty($report_files)) {
    echo "<p>No reports available for viewing at the moment.</p>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Reports</title>
    <style>
        /* General Body Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Main Container */
        .container {
            width: 80%;
            margin: 0 auto;
            padding-top: 50px;
        }

        h2 {
            text-align: center;
            color: #ff6347;
            margin-bottom: 30px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #ff6347;
            color: white;
            font-weight: bold;
        }

        table td a {
            color: #4caf50;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        table td a:hover {
            background-color: #4caf50;
            color: white;
        }

        /* No Reports Message */
        .no-reports {
            text-align: center;
            color: #f44336;
            font-size: 18px;
        }
        
        .action-buttons a {
            margin: 0 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Available Booking Reports</h2>

    <?php if (empty($report_files)): ?>
        <p class="no-reports">No reports available for viewing at the moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Report Name</th>
                    <th>Generated On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_files as $report): ?>
                    <?php
                    // Get the full path of the report file
                    $report_path = $reports_dir . $report;
                    $report_time = date("Y-m-d H:i:s", filemtime($report_path)); // Get the file's last modified date
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report); ?></td>
                        <td><?php echo $report_time; ?></td>
                        <td class="action-buttons">
                            <a href="<?php echo $report_path; ?>" target="_blank">View Report</a> | 
                            <a href="<?php echo $report_path; ?>" download>Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
