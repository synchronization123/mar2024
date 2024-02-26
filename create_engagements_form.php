<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Engagements Form</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

</head>
<body>
    <div class="container mt-5">
        <h2>Create Engagements</h2>
        <form id="myForm" action="create_engagements.php" method="post">
            <div class="form-group">
                <label for="tags">Tags:</label>
                <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags">
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
            </div>
            <div class="form-group">
                <label for="target_start">Target Start:</label>
                <input type="date" class="form-control" id="target_start" name="target_start">
            </div>
            <div class="form-group">
                <label for="target_end">Target End:</label>
                <input type="date" class="form-control" id="target_end" name="target_end">
            </div>
            <div class="form-group">
                <label for="product">Product:</label>
                <input type="text" class="form-control" id="product" name="product" placeholder="Enter product">
            </div>
            <div class="form-group">
                <label for="lead">Lead:</label>
                <input type="text" class="form-control" id="lead" name="lead" placeholder="Enter lead">
            </div>
            <div class="form-group">
                <label for="status">Status:</label>
                <select class="form-control" id="status" name="status">
                    <option value="Not Started">Not Started</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label>Engagement Type:</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="engagement_type" id="web_app" value="Web App">
                    <label class="form-check-label" for="web_app">Web App</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="engagement_type" id="mobile_app" value="Mobile App">
                    <label class="form-check-label" for="mobile_app">Mobile App</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
		
		<script>
$(document).ready(function(){
    $('#myForm').submit(function(e){
        		// AJAX call to submit form data without page refresh
        $.ajax({
            type: 'POST',
            url: 'create_engagements.php',
            data: $('#myForm').serialize(), // Serialize form data
            success: function(response){
                // Optional: handle response if needed
                console.log(response);
            },
            error: function(xhr, status, error){
                // Optional: handle error if needed
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<?php
// Check if notification is provided in the URL parameter
if(isset($_GET['notification'])) {
    // Retrieve notification from URL parameter
    $notification = $_GET['notification'];

    // Display the notification message at the bottom of the page
    echo "<div style='margin-bottom: 20px;'>$notification</div>";
}
?>
</body>
</html>
