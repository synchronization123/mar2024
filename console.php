<?php
// Function to fetch data from a table
function fetchDataFromTable($tableName, $conn) {
    // Fetch data from the specified table
    $sql = "SELECT * FROM `$tableName`"; // Enclose table name in backticks
    $result = $conn->query($sql);

    // If data is found, return it
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return array();
    }
}

// Function to fetch data from DefectDojo API
function fetchEngagementData($engagementId) {
    // DefectDojo API URL
    $url = "https://demo.defectdojo.org/api/v2/engagements/$engagementId/";

    // Headers
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Token 548afd6fab3bea9794a41b31da0e9404f733e222'
    );

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $response = curl_exec($curl);

    // Close cURL session
    curl_close($curl);

    // Decode JSON response
    $data = json_decode($response, true);

    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $tableNames = $_POST["table_names"];
    $year = $_POST["year"];
    $month = $_POST["month"];

    // Get table names from the form
    $tables = explode(",", $tableNames);
    $combinedData = array();

    // Assuming you have a database connection established
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "vapt";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch data from each table and combine them
    foreach ($tables as $tableName) {
        // Add the "view_" prefix
        $viewTableName = "view_" . trim($tableName);
        $data = fetchDataFromTable($viewTableName, $conn);
        $combinedData = array_merge($combinedData, $data);
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engagement Data</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Enter Details</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="year">Year:</label><br>
        <select id="year" name="year" required>
            <?php
            // Generating years for the dropdown (from current year - 2 to current year + 2)
            $currentYear = date("Y");
            for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                echo "<option value='$i'>$i</option>";
            }
            ?>
        </select><br><br>
        <label for="month">Month:</label><br>
        <select id="month" name="month" required>
            <option value="January">January</option>
            <option value="February">February</option>
            <option value="March">March</option>
            <option value="April">April</option>
            <option value="May">May</option>
            <option value="June">June</option>
            <option value="July">July</option>
            <option value="August">August</option>
            <option value="September">September</option>
            <option value="October">October</option>
            <option value="November">November</option>
            <option value="December">December</option>
        </select><br><br>
        <label for="table_names">Enter Table Names (comma-separated):</label><br>
        <input type="text" id="table_names" name="table_names" required><br><br>
        <input type="submit" value="Fetch Data">
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <?php if (!empty($combinedData)): ?>
            <h2>Combined Data for <?php echo $_POST["month"] . " " . $_POST["year"]; ?></h2>
            <table>
                <tr>
                    <th>Engagement ID</th>
                    <th>Name</th>
                    <th>Lead</th>
                    <th>Target Start</th>
                    <th>Target End</th>
                </tr>
                <?php foreach ($combinedData as $row): ?>
                    <tr>
                        <?php
                        // Fetch engagement data from DefectDojo API
                        $engagementData = fetchEngagementData($row['engagement_id']);
                        ?>
                        <td><?php echo $row['engagement_id']; ?></td>
                        <td><?php echo $engagementData['name']; ?></td>
                        <td><?php echo $engagementData['lead']; ?></td>
                        <td><?php echo $engagementData['target_start']; ?></td>
                        <td><?php echo $engagementData['target_end']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No data found</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
