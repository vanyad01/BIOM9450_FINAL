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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Simple Web Page</title>
    <link rel="stylesheet" href="CSS/style.css">
    <script type="text/javascript" src="functions.js"></script>
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
                <h2>Add New Round Row</h2>
                <form method="POST" class="summary-form">
                    <div class="form-row submit-row">
                        <input type="text" id="patient_id" placeholder="Enter Patient ID" name="patient_id" required>
                    </div>
                    <div class="form-row submit-row">
                        <input type="text" id="round_id" placeholder="Round ID" name="round_id" required>
                    </div>
                    <div class="form-row submit-row">
                        <input type="submit" id="submit-form" value="Add Row">
                    </div>
                </form>
                <?php
                    // Database connection
                    $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

                    if (!$conn) {
                        die("Connection failed: " . odbc_errormsg($conn));
                    }

                    // Check if form is submitted
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $patientID = $_POST['patient_id'];  // Patient ID from the form
                        $roundID = $_POST['round_id'];  // Round ID from the form

                        // Generate the CombinedID by concatenating RoundID and PatientID
                        $combinedID = $roundID . $patientID;

                        // Check if the combination of RoundID and PatientID already exists
                        $sqlCheck = "SELECT COUNT(*) AS count FROM PatientMedicationDietRounds WHERE RoundID = '$roundID' AND PatientID = '$patientID'";
                        $resultCheck = odbc_exec($conn, $sqlCheck);

                        if ($resultCheck) {
                            $row = odbc_fetch_array($resultCheck);
                            if ($row['count'] > 0) {
                                // If the combination exists, show an error message
                                echo "<p style='color: red;'>Error: Round ID '$roundID' with Patient ID '$patientID' already exists.</p>";
                            } else {
                                // Insert query to add a new row with placeholder values for the required fields
                                $sqlInsert = "INSERT INTO PatientMedicationDietRounds 
                                            (PatientID, RoundID, MedicationID, MedicationStatus, DietID, DietStatus, CombinedID) 
                                            VALUES 
                                            ('$patientID', '$roundID', 'PleaseEdit', 'PleaseEdit', 'PleaseEdit', 'PleaseEdit', '$combinedID')";

                                $resultInsert = odbc_exec($conn, $sqlInsert);

                                if ($resultInsert) {
                                    echo "<p style='color: green;'>Row added successfully!</p>";
                                } else {
                                    echo "<p style='color: red;'>Error adding row: " . odbc_errormsg($conn) . "</p>";
                                }
                            }
                        } else {
                            echo "<p style='color: red;'>Error checking existing round: " . odbc_errormsg($conn) . "</p>";
                        }
                    }
                    odbc_close($conn);
                    ?>
            </div>
        </main>
    </div>

    <footer>
        <p>&copy; 2024 MedTrak</p>
        <p>Contact us at: <a href="mailto:w.wang213@gmail.com">info@medtrak.com.au</a></p>
        <p>Phone: +61 123 456 789</p>
        <p>Sydney Startup Hub, 11 York St, Sydney NSW 2000</p>
    </footer>
</body>
</html>
