<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // join session started by login.php.
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $db = $_SESSION['db'];
} else {
    // Redirect to the login page if not logged in.
    header("Location: Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record</title>
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        /* Popup styling */
        .popup {
            display: none;
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            color: #155724;
        }

        .popup a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .popup a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar practitioner-navbar">
        <div class="logo">
            <img src="../Images/logo.jpg" alt="Logo">
        </div>
        <div class="cta-button">
            <a href="Logout.php" class="button">Logout</a>
        </div>
    </nav>
</header>

<div class="dashboard-container">
    <aside class="sidebar">
        <nav>
            <ul>
                <li><a href="Administration.php">Administration</a></li>
                <li><a href="Search_Edit1.php">Search Patients</a></li>
                <li><a href="Add_Patient.php">Add Patients</a></li>
                <li><a href="Medication.php">Medication Summary</a></li>
                <li><a href="Diet.php">Diet Summary</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-dashboard">
        <div class="config-summary">
            <h2>Edit Record</h2>

            <?php
            // Database connection
            $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

            if (!$conn) {
                echo "<p style='color: red;'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
            } else {
                // Make sure the CombinedID is passed in the URL
                if (!isset($_GET['CombinedID']) || empty($_GET['CombinedID'])) {
                    echo "<p style='color: red;'>Invalid ID provided. Please return to the previous page.</p>";
                    exit;
                }

                $CombinedID = $_GET['CombinedID'];

                // Handle form submission (updating the record)
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $newMedicationID = $_POST['medication_id'];
                    $newMedicationStatus = $_POST['medication_status'];
                    $newDietID = $_POST['diet_id'];
                    $newDietStatus = $_POST['diet_status'];

                    // Update query
                    $sqlUpdate = "UPDATE PatientMedicationDietRounds 
                                SET MedicationID = '$newMedicationID', 
                                    MedicationStatus = '$newMedicationStatus', 
                                    DietID = '$newDietID', 
                                    DietStatus = '$newDietStatus'
                                WHERE CombinedID = '$CombinedID'";

                    $resultUpdate = odbc_exec($conn, $sqlUpdate);

                    if ($resultUpdate) {
                        // Show success message with a link to go back to the search page
                        echo "<div class='popup' id='successPopup'>
                                <p>Record updated successfully!</p>
                                <p><a href='Search_Edit1.php?patient_id={$_GET['patient_id']}&first_name={$_GET['first_name']}&last_name={$_GET['last_name']}'>Click here to return to the search page</a></p>
                            </div>";
                    } else {
                        echo "<p style='color: red;'>Error updating record: " . odbc_errormsg($conn) . "</p>";
                    }
                }

                // Fetch record based on CombinedID
                $sql = "SELECT RoundID, PatientID, MedicationID, MedicationStatus, DietID, DietStatus 
                        FROM PatientMedicationDietRounds 
                        WHERE CombinedID = '$CombinedID'";
                $result = odbc_exec($conn, $sql);

                if ($result && $row = odbc_fetch_array($result)) {
                    // Fetch the PatientID from the same row
                    $patientID = $row['PatientID'];

                    // Display the form with the values for editing
                    echo "<form method='POST' class='summary-form'>";

                    // Hidden RoundID and PatientID
                    echo "<input type='hidden' name='round_id' value='" . $row['RoundID'] . "'>";
                    echo "<input type='hidden' name='patient_id' value='" . $patientID . "'>";

                    // Display the fields that can be edited
                    echo "<label for='medication_id'>Medication ID:</label>";
                    echo "<input type='text' name='medication_id' placeholder='Medication ID' value='" . $row['MedicationID'] . "' required>";

                    echo "<label for='medication_status'>Medication Status:</label>";
                    echo "<input type='text' name='medication_status' value='" . $row['MedicationStatus'] . "' required>";

                    echo "<label for='diet_id'>Diet ID:</label>";
                    echo "<input type='text' name='diet_id' value='" . $row['DietID'] . "' required>";

                    echo "<label for='diet_status'>Diet Status:</label>";
                    echo "<input type='text' name='diet_status' value='" . $row['DietStatus'] . "' required>";

                    echo "<div class='form-row submit-row'>";
                    echo "<input type='submit' id='submit-form' value='Update Record'>";
                    echo "</div>";
                    echo "</form>";
                } else {
                    echo "<p style='color: red;'>No record found for Combined ID $CombinedID.</p>";
                }
            }

            odbc_close($conn);
            ?>
        </div>
    </main>
</div>

<script>
    // Show the popup after the form is successfully submitted
    window.onload = function() {
        var successPopup = document.getElementById("successPopup");
        if (successPopup) {
            successPopup.style.display = "block"; // Show the success popup
        }
    };
</script>

</body>
</html>
