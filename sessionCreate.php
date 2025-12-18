<?php
require_once 'config.php';
// Faculty page for creating sessions, all students enrolled in a particular course  will have sessions added automatically
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_role'] !== 'faculty') {
    header("Location: dashboard.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$dashboard_page = ($user_role === 'faculty') ? 'faculty_dashboard.php' : 'dashboard.php';
$conn = getDBConnection();


$courses_result = $conn->query("
    SELECT courseId, courseName, courseCode 
    FROM courses 
    WHERE instructorName LIKE '%" . $_SESSION['user_name'] . "%'
   
    ORDER BY courseName
");

// If no courses found by the lecturers name,the just  get all courses
if ($courses_result->num_rows === 0) {
    $courses_result = $conn->query("
        SELECT courseId, courseName, courseCode 
        FROM courses 
        ORDER BY courseName
    ");
}

$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Session - Faculty</title>
</head>
<body>
    <h1>Create New Session</h1>
    
    <form id="sessionForm">
        <fieldset>
            <legend>Session Information</legend>
            
            <label for="courseId">Select Course:</label><br>
            <select id="courseId" name="courseId" required>
                <option value="">Choose a course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['courseId']; ?>">
                        <?php echo htmlspecialchars($course['courseCode'] . ' - ' . $course['courseName']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>
            
            <label for="sessionTitle">Session Title:</label><br>
            <input type="text" id="sessionTitle" name="sessionTitle" required placeholder="e.g., Introduction to PHP"><br><br>
            
            <label for="sessionDate">Session Date:</label><br>
            <input type="date" id="sessionDate" name="sessionDate" required><br><br>
            


            <label for="sessionTime">Session Time:</label><br>
            <input type="time" id="sessionTime" name="sessionTime" required><br><br>
            
            <label for="duration">Duration (minutes):</label><br>
            <input type="number" id="duration" name="duration" value="60" min="15" max="240"><br><br>
            
            <label for="location">Location:</label><br>
            <input type="text" id="location" name="location" placeholder="e.g., Room 101, Online"><br><br>
            
            <button type="submit">Create Session</button>
        </fieldset>
    </form>
    
    <div id="message"></div>
    
    <br>
    <form action="<?php echo $dashboard_page; ?>">
        <button type="submit">Back to Dashboard</button>
    </form>

    <script>
        

        document.getElementById('sessionDate').min = new Date().toISOString().split('T')[0];
        
        document.getElementById('sessionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                courseId: document.getElementById('courseId').value,
                sessionTitle: document.getElementById('sessionTitle').value,
                sessionDate: document.getElementById('sessionDate').value,
                sessionTime: document.getElementById('sessionTime').value,
                duration: document.getElementById('duration').value,
                location: document.getElementById('location').value
            };
            
            console.log('Creating session:', formData);
            
            // validate date 
            const sessionDateTime = new Date(formData.sessionDate + ' ' + formData.sessionTime);
            if (sessionDateTime < new Date()) {
                document.getElementById('message').innerHTML = '<p style="color: red;">Error: Session cannot be in the past</p>';
                return;
            }
            
            fetch('sessions_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                console.log('Response data:', data);
                const messageDiv = document.getElementById('message');
                if (data.success) {
                    messageDiv.innerHTML = '<p style="color: green;"> Session created successfully! Session ID: ' + data.sessionId + '</p>';
                    document.getElementById('sessionForm').reset();
                    // Reset the data picker 
                    document.getElementById('sessionDate').min = new Date().toISOString().split('T')[0];
                } else {
                    messageDiv.innerHTML = '<p style="color: red;"> Error: ' + data.error + '</p>';
                }
            })
            .catch(error => {
                console.error('Create session error:', error);
                document.getElementById('message').innerHTML = '<p style="color: red;"> Network error: ' + error.message + '</p>';
            });
        });
    </script>
</body>
</html>