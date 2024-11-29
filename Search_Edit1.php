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
    <title>Search and Edit Records</title>
    <link rel="stylesheet" href="CSS/style.css">
    
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
    <!-- Form Section -->
    <div class="config-summary">
        <h2>Patient Medication/Diet Records</h2>

        <!-- Search Form -->
        <form method="POST" class="summary-form">

            <div class="form-row">
                <input type="text" id="patient_id" name="patient_id" placeholder="Enter Patient ID">
            </div>
            <div class="form-row">
                <input type="text" id="first_name" name="first_name" placeholder="Enter First Name">
            </div>
            <div class="form-row">
                <input type="text" id="last_name" name="last_name" placeholder="Enter Last Name">
            </div>
            <div class="form-row">
                <input type="text" id="round_id" name="round_id" placeholder="Please enter in the format RD001 to RD999" pattern="^RD[0-9]{3}$" title="Round ID should be in the format RD001 to RD999">
            </div>
            <div class="form-row submit-row">
                <input type="submit" id="submit-form" value="Search Patient" />
            </div>
        </form>
    </div>

    <!-- Report Table Section -->
    <?php
    // Database connection
    $conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

    if (!$conn) {
        echo "<p style='color: red;'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
    } else {
        $searchSQL = "";

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputID = trim($_POST['patient_id']);
            $firstName = trim($_POST['first_name']);
            $lastName = trim($_POST['last_name']);
            $roundID = trim($_POST['round_id']);

            $conditions = [];
            if (!empty($inputID)) {
                $conditions[] = "Patients.PatientID = '$inputID'";
            }
            if (!empty($firstName)) {
                $conditions[] = "Patients.FirstName LIKE '%$firstName%'";
            }
            if (!empty($lastName)) {
                $conditions[] = "Patients.LastName LIKE '%$lastName%'";
            }
            if (!empty($roundID)) {
                $conditions[] = "PatientMedicationDietRounds.RoundID = '$roundID'";
            }

            if (!empty($conditions)) {
                $searchSQL = " WHERE " . implode(" AND ", $conditions);
            }
        }

        // Fetch data based on search criteria
        $sql = "SELECT 
                            Patients.PatientID, 
                            Patients.FirstName, 
                            Patients.LastName, 
                            Patients.DateOfBirth, 
                            Patients.Gender, 
                            Patients.Age, 
                            Patients.RoomNumber,
                            PatientMedicationDietRounds.RoundID, 
                            PatientMedicationDietRounds.CombinedID, 
                            PatientMedicationDietRounds.MedicationID, 
                            PatientMedicationDietRounds.MedicationStatus, 
                            PatientMedicationDietRounds.DietID, 
                            PatientMedicationDietRounds.DietStatus
                        FROM 
                            Patients
                        LEFT JOIN 
                            PatientMedicationDietRounds
                        ON 
                            Patients.PatientID = PatientMedicationDietRounds.PatientID
                        $searchSQL
                        ORDER BY 
                            Val(Mid(PatientMedicationDietRounds.RoundID, 3))";  // Orders by the 3 digits after 'RD'

        $result = odbc_exec($conn, $sql);
        if (!$result) {
            echo "<p style='color: red;'>Error fetching records: " . odbc_errormsg($conn) . "</p>";
        } else {
            echo "<table class='report-summary'>";
            echo "<tr>
                    <th>Patient ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Round ID</th>
                    <th>Medication ID</th>
                    <th>Medication Status</th>
                    <th>Diet ID</th>
                    <th>Diet Status</th>
                    <th>Actions</th>
                </tr>";

            while ($row = odbc_fetch_array($result)) {
                $medicationStatus = empty($row['MedicationStatus']) ? "Not scheduled yet" : htmlspecialchars($row['MedicationStatus']);
                $dietStatus = empty($row['DietStatus']) ? "Not scheduled yet" : htmlspecialchars($row['DietStatus']);

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['PatientID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['FirstName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['LastName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['RoundID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['MedicationID']) . "</td>";
                echo "<td>" . $medicationStatus . "</td>";
                echo "<td>" . htmlspecialchars($row['DietID']) . "</td>";
                echo "<td>" . $dietStatus . "</td>";
                echo "<td>
                        <form method='GET' action='Search_Edit2.php'>
                            <input type='hidden' name='CombinedID' value='" . htmlspecialchars($row['CombinedID']) . "'>
                            <button class='edit-button' type='submit'>Edit</button>
                        </form>
                    </td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    odbc_close($conn);
    ?>
    <!-- Add New Row Button -->
    <div class="add-new-row-button">
        <a href="AddNewRow.php"><button type="button">Add New Row</button></a>
    </div>
</main>

</body>
</html>
