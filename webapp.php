<?php

// Function to fetch user details by ID
function getUserDetails($userId, $token) {
    // API endpoint URL
    $url = "https://demo.defectdojo.org/api/v2/users/{$userId}/";

    // Headers
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Token ' . $token
    );

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if(curl_errno($ch)){
        echo 'Error: ' . curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);

    // Decode JSON response
    $data = json_decode($response, true);

    return $data;
}

// Get the engagement ID from the URL
$currentPage = basename($_SERVER['PHP_SELF']);
$engagementId = pathinfo($currentPage, PATHINFO_FILENAME);

// API endpoint URL
$url = "https://demo.defectdojo.org/api/v2/engagements/{$engagementId}/";

// Headers
$headers = array(
    'Content-Type: application/json',
    'Authorization: Token 856d771a223de97e8b1616bf02f42f004bc7981f'
);

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)){
    echo 'Error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

// Output engagement details
if ($data && isset($data['id'])) {
    echo "<table style='border-collapse: collapse;'>";
    echo "<tr>";
    echo "<td style='border: 1px solid black; padding: 5px;'><b>Engagement ID:</b> <a href='https://demo.defectdojo.org/engagement/{$data['id']}' target='_blank'>" . $data['id'] . "</a></td>";
    echo "<td style='border: 1px solid black; padding: 5px;'><b>Title:</b> " . $data['name'] . "</td>";
    echo "<td style='border: 1px solid black; padding: 5px;'><b>Description:</b> " . $data['description'] . "</td>";
    echo "<td style='border: 1px solid black; padding: 5px;'><b>Start Date:</b> " . $data['target_start'] . "</td>";
    echo "<td style='border: 1px solid black; padding: 5px;'><b>End Date:</b> " . $data['target_end'] . "</td>";

    // Fetch user details for lead parameter
    if (isset($data['lead'])) {
        $leadId = $data['lead'];
        $leadDetails = getUserDetails($leadId, '856d771a223de97e8b1616bf02f42f004bc7981f');
        if ($leadDetails && isset($leadDetails['username'])) {
            echo "<td style='border: 1px solid black; padding: 5px;'><b>Assigned To:</b> " . $leadDetails['username'] . "</td>";
        } else {
            echo "<td style='border: 1px solid black; padding: 5px;'>Lead details not found.</td>";
        }
    } else {
        echo "<td style='border: 1px solid black; padding: 5px;'>Lead not specified for this engagement.</td>";
    }
    echo "</tr>";
    echo "</table>";
} else {
    echo "<h6>Engagement not found or unauthorized access.</h6>";
}




      // Assuming your URL is like: http://example.com/1.php

      // Extract the filename from the URL
      $filename = basename($_SERVER['SCRIPT_FILENAME'], '.php');

      // Construct the table name
      $tableName = 'view_' . $filename;

      // Database connection parameters
      $servername = "localhost";
      $username = "root";
      $password = "";
      $database = "vapt";

      // Create connection
      $conn = new mysqli($servername, $username, $password, $database);

      // Check connection
      if ($conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
      }

      // Specify the columns you want to display and customize their names
      $columns = array(
 	  
//'engagement_id' => 'Engagement ID',
'Total Count' => 'Total Test Cases',
'In Progress Count' => 'In Progress',
'Not Applicable Count' => 'Not Applicable',
'Completed Count' => 'Completed',
'Completion Percentage' => 'Percentage',


		  
          // Add more columns as needed
      );

      // Specify the columns you want to hide
      $hiddenColumns = array(
          'Not Started Count',
           // Add more columns to hide as needed
      );

      // Query to fetch data from the dynamically generated table
      $sql = "SELECT * FROM $tableName";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          // Output data of each row
          echo '<table class="table table-bordered">';
          // Display table headers
          echo '<thead><tr>';
          foreach ($columns as $columnName) {
              echo '<th>' . $columnName . '</th>';
          }
          echo '</tr></thead>';
          // Display table data
          echo '<tbody>';
          while($row = $result->fetch_assoc()) {
              echo '<tr>';
              // Display the details for each visible column
              foreach ($columns as $columnKey => $columnName) {
                  if (!in_array($columnKey, $hiddenColumns)) {
                      echo '<td>' . $row[$columnKey] . '</td>';
                  }
              }
              echo '</tr>';
          }
          echo '</tbody></table>';
      } else {
          echo "0 results";
      }

      // Close connection
      $conn->close();
  
    



// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vapt";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filename and lead from URL
$filename = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_FILENAME);
$lead = isset($_GET['lead']) ? $_GET['lead'] : '';

// Add prefix "V_" to tablename
$tablename = "V_" . $filename;
$viewname = "view_" . $filename;

// Fetch data from API
$engagementName = '';
if (!empty($filename)) {
    $engagementData = fetchData("https://demo.defectdojo.org/api/v2/engagements/$filename");
    if (!empty($engagementData) && isset($engagementData['name'])) {
        $engagementName = $engagementData['name'];
    } else {
     //   echo "Failed to fetch engagement data. Please try again later.";
    }
}

// Create table if it doesn't exist for the first time
if (!tableExists($tablename, $conn)) {
    createTable($tablename, $conn);
    copyDataFromTemplate($tablename, $conn);
    createView($tablename, $viewname, $conn);
    createTrigger($tablename, $viewname, $conn);
}

// Save data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['data'])) {
    $id = $_POST['id'];
    $newData = json_decode($_POST['data'], true);
    updateData($tablename, $id, $newData, $conn);
}

// Fetch data from the table
$sql_fetch = "SELECT * FROM $tablename ORDER BY id ASC";
$result = $conn->query($sql_fetch);

function tableExists($tableName, $conn) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

function createTable($tableName, $conn) {
    $sql_create_table = "CREATE TABLE $tableName (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        engagement_id INT(6) NOT NULL DEFAULT 0,
        test_case_id VARCHAR(30) UNIQUE,
        test_case TEXT,
        description TEXT,
        status ENUM('Not Started', 'InProgress', 'Completed', 'Not Applicable') DEFAULT 'Not Started',
        outcome ENUM('Select Outcome', 'Pass', 'Fail', 'Not Applicable') DEFAULT 'Select Outcome',
        notes TEXT,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($sql_create_table);
}

function copyDataFromTemplate($tableName, $conn) {
    $filename = pathinfo($GLOBALS['filename'], PATHINFO_FILENAME);
    $sql_copy = "INSERT INTO $tableName (engagement_id, test_case_id, test_case, description, status, outcome, notes)
    SELECT '$filename', test_case_id, test_case, description, status, outcome, notes FROM webapp_template";
    $conn->query($sql_copy);
}

function updateData($tableName, $id, $newData, $conn) {
    $id = intval($id);
    $status = $conn->real_escape_string($newData['status']);
    $outcome = $conn->real_escape_string($newData['outcome']);
    $notes = $conn->real_escape_string($newData['notes']);

    $sql_update = "UPDATE $tableName SET status='$status', outcome='$outcome', notes='$notes' WHERE id=$id";
    $conn->query($sql_update);
}

function fetchData($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

function createView($tableName, $viewName, $conn) {
    $sql_create_view = "CREATE VIEW $viewName AS
        SELECT engagement_id,
            COUNT(*) AS 'Total Count',
            SUM(CASE WHEN status='Not Started' THEN 1 ELSE 0 END) AS 'Not Started Count',
            SUM(CASE WHEN status='InProgress' THEN 1 ELSE 0 END) AS 'In Progress Count',
            SUM(CASE WHEN status='Not Applicable' THEN 1 ELSE 0 END) AS 'Not Applicable Count',
            SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS 'Completed Count',
            CONCAT(ROUND((SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) / (COUNT(*) - SUM(CASE WHEN status='Not Applicable' THEN 1 ELSE 0 END))) * 100, 2), '%') AS 'Completion Percentage'
        FROM $tableName
        GROUP BY engagement_id";
    $conn->query($sql_create_view);
}

function createTrigger($tableName, $viewName, $conn) {
    $triggerName = "trig_$tableName";
    $sql_create_trigger = "CREATE TRIGGER $triggerName AFTER INSERT ON $tableName FOR EACH ROW
        BEGIN
            INSERT INTO $viewName (engagement_id)
            VALUES (NEW.engagement_id)
            ON DUPLICATE KEY UPDATE
            `Total Count` = `Total Count` + 1,
            `Not Started Count` = `Not Started Count` + IF(NEW.status='Not Started', 1, 0),
            `In Progress Count` = `In Progress Count` + IF(NEW.status='InProgress', 1, 0),
            `Not Applicable Count` = `Not Applicable Count` + IF(NEW.status='Not Applicable', 1, 0),
            `Completed Count` = `Completed Count` + IF(NEW.status='Completed', 1, 0),
            `Completion Percentage` = CONCAT(ROUND((`Completed Count` / (`Total Count` - `Not Applicable Count`)) * 100, 2), '%');
        END";
    $conn->query($sql_create_trigger);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editable Table</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            padding-top: 50px;
            width: 80%;
            margin: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        select {
            width: 100%;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            box-sizing: border-box;
        }

        textarea {
            width: 100%;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            box-sizing: border-box;
            resize: vertical;
        }

        .btn-pdf {
            background-color: #28a745;
            color: #fff;
            border: none;
        }

        .btn-pdf:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <table class="table" id="dataTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Engagement ID</th>
                <th>Test Case ID</th>
                <th>Test Case</th>
                <th>Description</th>
                <th>Status</th>
                <th>Outcome</th>
                <th>Notes</th>
                <th>Last Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["id"] . "</td>";
                    echo "<td>" . $row["engagement_id"] . "</td>";
                    echo "<td>" . $row["test_case_id"] . "</td>";
                    echo "<td>" . $row["test_case"] . "</td>";
                    echo "<td>" . $row["description"] . "</td>";
                    echo "<td><select class='status' onchange='saveData(this.parentElement.parentElement)'>";
                    echo "<option value='Not Started'" . ($row["status"] == "Not Started" ? " selected" : "") . ">Not Started</option>";
                    echo "<option value='InProgress'" . ($row["status"] == "InProgress" ? " selected" : "") . ">InProgress</option>";
                    echo "<option value='Completed'" . ($row["status"] == "Completed" ? " selected" : "") . ">Completed</option>";
                    echo "<option value='Not Applicable'" . ($row["status"] == "Not Applicable" ? " selected" : "") . ">Not Applicable</option>";
                    echo "</select></td>";
                    echo "<td><select class='outcome' onchange='saveData(this.parentElement.parentElement)'>";
                    echo "<option value='Select Outcome'" . ($row["outcome"] == "Select Outcome" ? " selected" : "") . ">Select Outcome</option>";
                    echo "<option value='Pass'" . ($row["outcome"] == "Pass" ? " selected" : "") . ">Pass</option>";
                    echo "<option value='Fail'" . ($row["outcome"] == "Fail" ? " selected" : "") . ">Fail</option>";
                    echo "<option value='Not Applicable'" . ($row["outcome"] == "Not Applicable" ? " selected" : "") . ">Not Applicable</option>";
                    echo "</select></td>";
                    echo "<td><textarea class='notes' rows='2'>" . $row["notes"] . "</textarea></td>";
                    echo "<td>" . $row["last_updated"] . "</td>";
                    echo "<td><button class='btn btn-primary' onClick='saveData(this.parentElement.parentElement)'>Save</button></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No data found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
    function saveData(row) {
        var cells = row.getElementsByTagName('td');
        var data = {};
        data['id'] = cells[0].innerText;
        data['status'] = cells[5].querySelector('select.status').value;
        data['outcome'] = cells[6].querySelector('select.outcome').value;
        data['notes'] = cells[7].querySelector('textarea.notes').value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                console.log("Data saved successfully");
            }
        };

        var params = "id=" + data['id'] + "&data=" + JSON.stringify(data);
        xhr.send(params);
    }
</script>

</body>
</html>

<?php
$conn->close();
?>
