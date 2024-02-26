<?php

// Initialize error_message variable
$error_message = '';
$debug_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DefectDojo API endpoint for creating engagements
    $url_create_engagement = "https://demo.defectdojo.org/api/v2/engagements/";
    // DefectDojo API endpoint for updating engagements
    $url_update_engagement = "https://demo.defectdojo.org/api/v2/engagements/{engagement_id}/";
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
    $data_create = array(
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
        'Authorization: Token 856d771a223de97e8b1616bf02f42f004bc7981f',
        'Content-Type: application/json'
    );

    // Initialize cURL session for creating engagement
    $ch_create_engagement = curl_init($url_create_engagement);
    curl_setopt($ch_create_engagement, CURLOPT_POST, 1);
    curl_setopt($ch_create_engagement, CURLOPT_POSTFIELDS, json_encode($data_create));
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

                // Insert the engagement ID and type into the database
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

                // Prepare and bind the INSERT statement
                $stmt = $conn->prepare("INSERT INTO engagements (engagement_id, engagement_type) VALUES (?, ?)");
                $stmt->bind_param("is", $engagement_id, $_POST['engagement_type']);

                // Execute the statement
                if ($stmt->execute() === TRUE) {
                    $success_message = "Record inserted successfully into database.";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }

                // Close the statement and connection
                $stmt->close();
                $conn->close();

                // Determine the template file based on user selection
                $template_file = '';
                if ($_POST['engagement_type'] === 'Web App') {
                    $template_file = 'webapp.php';
                } elseif ($_POST['engagement_type'] === 'Mobile App') {
                    $template_file = 'mobileapp.php';
                } else {
                    $error_message = "Error: Invalid engagement type.";
                }

                // Copy file functionality
                $source_file = "templates/{$template_file}"; // Adjust this path based on your actual file structure
                $destination_file = "data/{$engagement_id}.php";
                if (copy($source_file, $destination_file)) {
                    $success_message .= " Template file copied successfully as $destination_file";
                } else {
                    $error_message = "Error copying template file.";
                }

                // Data to be sent in the request to update engagement with tracker parameter
                $data_update = array(
                    'tracker' => "https://localhost/march2024/data/{$engagement_id}.php"
                );

                // URL for updating engagement
                $url_update_engagement = str_replace('{engagement_id}', $engagement_id, $url_update_engagement);

                // Initialize cURL session for updating engagement
                $ch_update_engagement = curl_init($url_update_engagement);
                curl_setopt($ch_update_engagement, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch_update_engagement, CURLOPT_POSTFIELDS, json_encode($data_update));
                curl_setopt($ch_update_engagement, CURLOPT_HTTPHEADER, $headers_create_engagement);
                curl_setopt($ch_update_engagement, CURLOPT_RETURNTRANSFER, true);

                // Execute the update engagement API call
                $response_update_engagement = curl_exec($ch_update_engagement);

                // Check for errors in updating engagement
                if ($response_update_engagement === false) {
                    $error_message .= ' Error: ' . curl_error($ch_update_engagement);
                } else {
                    // Decode the API response for updating engagement
                    $responseData_update_engagement = json_decode($response_update_engagement, true);
                    
                    // Check if the response contains any errors
                    if (isset($responseData_update_engagement['message'])) {
                        $error_message .= ' Error: ' . $responseData_update_engagement['message'];
                    } else {
                        $success_message .= " Tracker parameter updated successfully for engagement ID: $engagement_id";

                        // Data to be sent in the request to create test for the current engagement
                        $data_create_test = array(
                            'title' => 'Manual Code Review',
                            'test_type_name' => 'Manual Code Review',
                            'test_type' => 29,
                            'target_start' => $_POST['target_start'], // Fetch from engagement
                            'target_end' => $_POST['target_end'], // Fetch from engagement
                            'environment' => 4,
                            'lead' => $_POST['lead'] // Fetch from engagement
                        );

                        // Headers for the create test API request
                        $headers_create_test = array(
                            'Authorization: Token 856d771a223de97e8b1616bf02f42f004bc7981f',
                            'Content-Type: application/json'
                        );

                        // Initialize cURL session for creating test
                        $ch_create_test = curl_init($url_create_test);
                        curl_setopt($ch_create_test, CURLOPT_POST, 1);
                        curl_setopt($ch_create_test, CURLOPT_POSTFIELDS, json_encode($data_create_test));
                        curl_setopt($ch_create_test, CURLOPT_HTTPHEADER, $headers_create_test);
                        curl_setopt($ch_create_test, CURLOPT_RETURNTRANSFER, true);

                        // Execute the create test API call
                        $response_create_test = curl_exec($ch_create_test);

                        // Check for errors in creating test
                        if ($response_create_test === false) {
                            $error_message .= ' Error creating test: ' . curl_error($ch_create_test);
                        } else {
                            // Decode the API response for creating test
                            $responseData_create_test = json_decode($response_create_test, true);
                            
                            // Check if the response contains any errors
                            if (isset($responseData_create_test['message'])) {
                                $error_message .= ' Error creating test: ' . $responseData_create_test['message'];
                            } else {
                                $success_message .= " Test created successfully for engagement ID: $engagement_id";
                            }
                        }

                        // Close cURL session for creating test
                        curl_close($ch_create_test);
                    }
                }

                // Close cURL session for updating engagement
                curl_close($ch_update_engagement);

                // Display notifications using Bootstrap modal
                echo <<<HTML
                    <div id="myModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Engagement Creation Status</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-success">$success_message</div>
                                    <div class="alert alert-danger">$error_message</div>
                                    <div class="alert alert-info">$debug_message</div>
                                </div>
                                <div class="modal-footer">
                                    <a href="create_test_form.php" class="btn btn-primary">Create Test</a>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function(){
                            $("#myModal").modal('show');
                        });
                    </script>
                HTML;
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


// Redirect to Test_creation.php with engagement_id parameter
header("Location: Test_creation.php?engagement_id=$engagement_id");
exit;
?>