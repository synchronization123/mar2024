<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DefectDojo API endpoint for creating engagements
    $url_create_engagement = "https://demo.defectdojo.org/api/v2/engagements/";
    // DefectDojo API endpoint for creating tests
    $url_create_test = "https://demo.defectdojo.org/api/v2/tests/";

    // Check if tags field is empty
    if (empty($_POST['tags'])) {
        echo 'Error: Tags field cannot be empty.';
        exit();
    }

    // Extract engagement ID from the tracker URL
    $tracker_url = isset($_POST['tracker']) ? $_POST['tracker'] : ''; // Get tracker from form data
    $engagement_id = basename($tracker_url, '.php');

    // Data to be sent in the request to create engagement
    $data_create_engagement = array(
        'tags' => explode(',', $_POST['tags']),
        'name' => $_POST['name'],
        'target_start' => $_POST['target_start'],
        'target_end' => $_POST['target_end'],
        'product' => $_POST['product'],
        'lead' => $_POST['lead'],
        'status' => isset($_POST['status']) ? $_POST['status'] : 'Not Started', // Check if status is set, otherwise default to 'Not Started'
        // Add tracker parameter for API call
        'tracker' => $tracker_url 
        // Engagement type is not passed in API call
    );

    // Headers for the create engagement API request
    $headers_create_engagement = array(
        'Authorization: Token YOUR_API_KEY_HERE',
        'Content-Type: application/json'
    );

    // Initialize cURL session for creating engagement
    $ch_create_engagement = curl_init($url_create_engagement);
    curl_setopt($ch_create_engagement, CURLOPT_POST, 1);
    curl_setopt($ch_create_engagement, CURLOPT_POSTFIELDS, json_encode($data_create_engagement));
    curl_setopt($ch_create_engagement, CURLOPT_HTTPHEADER, $headers_create_engagement);
    curl_setopt($ch_create_engagement, CURLOPT_RETURNTRANSFER, true);

    // Execute the create engagement API call
    $response_create_engagement = curl_exec($ch_create_engagement);

    // Check for errors in creating engagement
    if ($response_create_engagement === false) {
        echo 'Error: ' . curl_error($ch_create_engagement);
    } else {
        // Decode the API response for creating engagement
        $responseData_create_engagement = json_decode($response_create_engagement, true);
        
        // Check if the response contains any errors
        if (isset($responseData_create_engagement['message'])) {
            echo 'Error: ' . $responseData_create_engagement['message'];
        } else {
            // Output the engagement ID
            if (isset($responseData_create_engagement['id'])) {
                $engagement_id = $responseData_create_engagement['id'];

                // Execute pentest JSON after engagement creation
                $pentest_json = file_get_contents('pentest.json');
                $pentest_data = json_decode($pentest_json, true);

                // Set engagement ID in the pentest data
                $pentest_data['engagement'] = $engagement_id;

                // Convert pentest data back to JSON
                $pentest_json_updated = json_encode($pentest_data);

                // Execute the updated pentest JSON
                // Prepare cURL to POST the updated pentest JSON
                $ch_execute_pentest = curl_init("https://demo.defectdojo.org/api/v2/tests/");
                curl_setopt($ch_execute_pentest, CURLOPT_POSTFIELDS, $pentest_json_updated);
                curl_setopt($ch_execute_pentest, CURLOPT_POST, 1);
                curl_setopt($ch_execute_pentest, CURLOPT_HTTPHEADER, array(
                    'Authorization: Token YOUR_API_KEY_HERE',
                    'Content-Type: application/json'
                ));
                curl_setopt($ch_execute_pentest, CURLOPT_RETURNTRANSFER, true);

                // Execute the cURL request
                $response_execute_pentest = curl_exec($ch_execute_pentest);

                // Check for errors in executing pentest JSON
                if ($response_execute_pentest === false) {
                    echo 'Error: ' . curl_error($ch_execute_pentest);
                } else {
                    // Decode the response
                    $responseData_execute_pentest = json_decode($response_execute_pentest, true);
                    if (isset($responseData_execute_pentest['id'])) {
                        echo "Pentest executed successfully!";
                    } else {
                        echo "Error executing pentest: " . $responseData_execute_pentest['message'];
                    }
                }

                // Close cURL handle
                curl_close($ch_execute_pentest);

                // Database part
                // Insert the engagement ID into the database
                $servername = "localhost";
                $username = "your_username";
                $password = "your_password";
                $dbname = "vapt";

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Prepare and bind the INSERT statement
                $stmt = $conn->prepare("INSERT INTO engagements (engagement_id, engagement_type) VALUES (?, ?)");
                $stmt->bind_param("is", $engagement_id, $engagement_type);

                // Set the engagement type based on user selection (Web App or Mobile App)
                $engagement_type = ($_POST['engagement_type'] == 'Web App') ? 1 : 2;

                // Execute the INSERT statement
                if ($stmt->execute() === TRUE) {
                    echo "Record inserted successfully into database.";
                } else {
                    echo "Error: " . $stmt->error;
                }

                // Close statement and connection
                $stmt->close();
                $conn->close();
            } else {
                echo 'Error: Engagement ID not found in API response.';
            }
        }
    }

    // Close cURL session for creating engagement
    curl_close($ch_create_engagement);
} else {
    // If accessed via GET method, redirect back to the form page
    header("Location: create_engagements_form.php");
    exit();
}
?>
