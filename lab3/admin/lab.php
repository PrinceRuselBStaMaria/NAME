<?php
// Database connection
include '../connection.php';

// Query to select all data from the table
$sql = "SELECT * FROM appointments";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab Data</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h2>Lab Data</h2>
    <?php if ($result->num_rows > 0) : ?>
        <table>
            <thead>
                <tr>
                    <?php
                    // Get column names from the first row
                    $fields = $result->fetch_fields();
                    foreach ($fields as $field) {
                        echo "<th>" . $field->name . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <?php foreach ($row as $value) : ?>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No data found in the table.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>