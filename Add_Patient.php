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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Patient</title>
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
        <div class="config-summary">
            <h2>Add New Patient</h2>
            <?php
            // Database connection
            $conn = @odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};dbq=$db", '', '', SQL_CUR_USE_ODBC);

            if (!$conn) {
                echo "<p class='error'>Connection Failed: " . odbc_errormsg($conn) . "</p>";
            }

            // Check if the form is submitted
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Get form data
                $patientID = $_POST['patient_id'];
                $firstName = $_POST['first_name'];
                $lastName = $_POST['last_name'];
                $dob = $_POST['dob'];
                $gender = $_POST['gender'];
                $age = $_POST['age'];
                $roomNumber = $_POST['room_number'];

                // Photo upload
                $target_dir = "E:/Final945094509450/PHPWebProject1/BIOM9450_FINAL/Images/";
                $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Check if image file is a valid image type
                if ($_FILES["fileToUpload"]["size"] > 5000000) {
                    echo "<p class='error'>Sorry, your file is too large.</p>";
                    $uploadOk = 0;
                }

                // Allow certain file formats (webp only)
                if ($imageFileType != "webp") {
                    echo "<p class='error'>Sorry, only WEBP files are allowed.</p>";
                    $uploadOk = 0;
                }

                // If file is valid, upload it
                if ($uploadOk == 1 && move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                    echo "<p>Photo uploaded successfully!</p>";
                } else {
                    echo "<p class='error'>Sorry, there was an error uploading your photo.</p>";
                }

                // Check if the PatientID already exists
                $sqlCheck = "SELECT COUNT(*) AS count FROM Patients WHERE PatientID = '$patientID'";
                $resultCheck = @odbc_exec($conn, $sqlCheck); // Suppress error
                if (!$resultCheck) {
                    echo "<p class='error'>Error checking Patient ID: " . odbc_errormsg($conn) . "</p>";
                } else {
                    $row = odbc_fetch_array($resultCheck);
                    $patientExists = $row['count'] > 0;

                    if ($patientExists) {
                        echo "<p class='error'>Patient ID '$patientID' is already taken. Please choose another one.</p>";
                    } else {
                        // Insert query to add patient into Patients table
                        $sqlInsertPatient = "INSERT INTO Patients (PatientID, FirstName, LastName, DateOfBirth, Gender, Age, RoomNumber)
                                              VALUES ('$patientID', '$firstName', '$lastName', '$dob', '$gender', '$age', '$roomNumber')";

                        $resultInsertPatient = @odbc_exec($conn, $sqlInsertPatient); // Suppress error
            
                        if (!$resultInsertPatient) {
                            echo "<p class='error'>Error adding patient: " . odbc_errormsg($conn) . "</p>";
                        } else {
                            // After inserting into Patients table, insert a new row into PatientMedicationDietRounds
                            $roundID = "RD001"; // Fixed RoundID
                            $combinedID = $roundID . $patientID; // Format CombinedID
            
                            // Use default values for MedicationID, MedicationStatus, DietID, and DietStatus
                            $medicationID = "ME001"; // Default MedicationID
                            $medicationStatus = "Not scheduled yet"; // Default MedicationStatus
                            $dietID = "DI001"; // Default DietID
                            $dietStatus = "Not scheduled yet"; // Default DietStatus
            
                            $sqlInsertRound = "INSERT INTO PatientMedicationDietRounds (RoundID, PatientID, MedicationID, MedicationStatus, DietID, DietStatus, CombinedID) 
                                               VALUES ('$roundID', '$patientID', '$medicationID', '$medicationStatus', '$dietID', '$dietStatus', '$combinedID')";

                            $resultInsertRound = @odbc_exec($conn, $sqlInsertRound); // Suppress error
            
                            if ($resultInsertRound) {
                                echo "<div class='popup' id='successPopup'>
                                          <p>New patient added successfully!</p>
                                          <p><a href='Search_Edit1.php'>Click here to return to the search page</a></p>
                                      </div>";
                            } else {
                                echo "<p class='error'>Error adding medication/diet round: " . odbc_errormsg($conn) . "</p>";
                            }
                        }
                    }
                }
            }

            odbc_close($conn);
            ?>

            <!-- Patient information form -->
            <form method="POST" class="summary-form" enctype="multipart/form-data">
                <div class="form-row">
                    <input type="text" name="patient_id" placeholder="Enter Patient ID" id="patient_id" required>
                </div>
                <div class="form-row">
                    <input type="text" name="first_name" placeholder="Patient First Name" id="first_name" required>
                </div>
                <div class="form-row">
                    <input type="text" name="last_name" placeholder="Patient Last Name" id="last_name" required>
                </div>
                <div class="form-row">
                    <input type="text" name="dob" id="dob" class="date-placeholder" placeholder="dd/mm/yyyy" onfocus="enableDateInput(this)" onblur="showPlaceholder(this)" />
                </div>

                <div class="form-row">
                    <select name="gender" id="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-row">
                    <input type="number" placeholder="Age" name="age" id="age" required>
                </div>

                <div class="form-row">
                    <input type="text" name="room_number" placeholder="Room Number" id="room_number" required>
                </div>

                <!-- Photo Upload -->
                <div class="form-row">
                    <label for="fileToUpload">Upload Photo (WEBP only) <br>Please name the photo as the patient's ID:</label>
                    <input type="file" name="fileToUpload" id="fileToUpload" accept="image/webp" required>
                </div>
                <div class="form-row submit-row">
                    <input type="submit" id="submit-form" value="Add Patient">
                </div>

                <!-- Link to delete patient -->
                <div class="form-row">
                    <a href="Delete_Patient.php" class="delete-link">Click here to delete a patient</a>
                </div>
            </form>
        </div>
    </main>
</div>

<footer>
    <p>&copy; 2024 MedTrak</p>
    <p>Contact us at: <a href="mailto:w.wang213@gmail.com">info@medtrak.com.au</a></p>
    <p>Phone: +61 123 456 789</p>
    <p>Sydney Startup Hub, 11 York St, Sydney NSW 2000</p>
</footer>

<script>
    // Show the popup after the form is successfully submitted
    window.onload = function() {
        var successPopup = document.getElementById("successPopup");
        if (successPopup) {
            successPopup.style.display = "block"; // Show the success popup
        }
    };

    // Transform the text field into a date picker on focus
    function enableDateInput(input) {
        input.type = 'date';
        input.classList.remove('date-placeholder');
        input.placeholder = ''; // Remove placeholder when focused
    }

    // Revert back to text input with placeholder when unfocused and empty
    function showPlaceholder(input) {
        if (!input.value) {
            input.type = 'text';
            input.classList.add('date-placeholder');
            input.placeholder = 'Date of Birth dd/mm/yyyy'; // Re-add placeholder
        }
    }

    // Initialize field as text input with placeholder on page load
    document.addEventListener('DOMContentLoaded', function () {
        const dateInput = document.getElementById('dob');
        if (!dateInput.value) {
            dateInput.type = 'text';
            dateInput.classList.add('date-placeholder');
            dateInput.placeholder = 'Date of Birth dd/mm/yyyy';
        }
    });
</script>

</body>
</html>
