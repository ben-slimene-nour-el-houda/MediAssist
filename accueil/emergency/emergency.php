<?php
// Include session configuration and database connection
require_once '../session_config.php';
require_once '../../db_connect.php';

// Redirect to login page if not logged in
redirect_if_not_logged_in('login/login.php');

// Retrieve the logged-in user's information
$user_id = $_SESSION['user_id'];
$user_info = [];

$stmt = $mysqli->prepare("SELECT name, email, profile_photo FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_info = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login/login.php?error=invalid_user");
    exit();
}
$stmt->close();

// Prepare data for display `profile_name` and `profile_initials`
$profile_name = $user_info['name'];
$names = explode(' ', $profile_name);
$profile_initials = substr($names[0], 0, 1);
if (count($names) > 1) {
    $profile_initials .= substr(end($names), 0, 1);
}

// Retrieve emergency contacts
$emergency_contacts = [];
$stmt = $mysqli->prepare("SELECT id, contact_name, phone_number, relationship FROM emergency_contacts WHERE user_id = ? ORDER BY id ASC");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($contact = $result->fetch_assoc()) {
    $emergency_contacts[] = $contact;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../logo.png" type="image/png">
    <title>MediAssist - Emergency Contacts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #00c4b4;
            --primary-light: #7fffd4;
            --primary-dark: #008b8b;
            --secondary: #40c4ff;
            --accent: #00695c;
            --light: #e6f3f8;
            --dark: #004d40;
            --gray: #f0f4f8;
            --text: #2c3e50;
            --success: #28a745;
            --warning: #ffb300;
            --danger: #e91e63;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(to bottom, var(--gray), var(--light));
            color: var(--text);
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1.5rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2.5rem;
            color: var(--white);
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .logo i {
            margin-right: 0.7rem;
            font-size: 2.2rem;
            color: var(--primary-light);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-light);
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-light);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: var(--gray);
            clip-path: ellipse(150% 60% at 50% 100%);
        }

        .emergency-section {
            padding: 0 3rem 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .emergency-panel {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .emergency-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }

        .emergency-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .emergency-header h3 {
            font-size: 1.8rem;
            color: var(--text);
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .emergency-header h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
        }

        .emergency-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            transition: background 0.3s ease;
        }

        .emergency-form.editing {
            background: rgba(0, 196, 180, 0.1);
            border-radius: 10px;
            padding: 1rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group input {
            padding: 1rem 0.8rem;
            border: none;
            border-radius: 10px;
            background: var(--gray);
            font-size: 0.95rem;
            color: var(--text);
            transition: all 0.3s ease;
            width: 95%;
            line-height: 1.5;
        }

        .form-group input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 196, 180, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 196, 180, 0.4);
        }

        .action-btn i {
            font-size: 0.9rem;
        }

        .action-btn.clear-btn {
            background: linear-gradient(135deg, var(--warning), #ff8c00);
        }

        .action-btn.clear-btn:hover {
            box-shadow: 0 5px 15px rgba(255, 179, 0, 0.4);
        }

        .action-btn.call-btn {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .action-btn.call-btn:hover {
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 1.2rem;
            background: var(--white);
            border-radius: 12px;
            margin-bottom: 1.2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 1.2rem;
        }

        .contact-icon i {
            font-size: 1.4rem;
        }

        .contact-details {
            flex-grow: 1;
        }

        .contact-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: var(--text);
        }

        .contact-info {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .contact-actions {
            display: flex;
            gap: 0.8rem;
        }

        .delete-btn {
            background: linear-gradient(135deg, var(--danger), #c2185b);
        }

        .delete-btn:hover {
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.4);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0 1.5rem;
            }

            .emergency-section {
                padding: 0 1.5rem 2rem;
            }

            .emergency-form {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="navbar">
            <a href="../accueil.php" class="nav-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>MediAssist</span>
            </div>
        </div>
        <div class="wave"></div>
    </header>

    <!-- Emergency Contacts Section -->
    <section class="emergency-section">
        <div class="emergency-panel">
            <div class="emergency-header">
                <h3 id="formTitle">Manage Emergency Contacts</h3>
            </div>

            <!-- Form to create a new contact -->
            <form id="emergencyForm" class="emergency-form" action="save_emergency_contact.php" method="POST">
                <input type="hidden" id="contact_id" name="contact_id" value="">
                <div class="form-group">
                    <label for="contact_name">Contact Name</label>
                    <input type="text" id="contact_name" name="contact_name" placeholder="Enter contact name" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="Enter phone number (e.g., +216 123 456 78)" required>
                </div>
                <div class="form-group">
                    <label for="relationship">Relationship</label>
                    <input type="text" id="relationship" name="relationship" placeholder="Enter relationship" required>
                </div>
                <div class="form-actions">
                    <button type="submit" id="submitButton" class="action-btn"><i class="fas fa-plus"></i> Add Contact</button>
                    <button type="reset" id="clearButton" class="action-btn clear-btn"><i class="fas fa-eraser"></i> Clear</button>
                </div>
            </form>

            <!-- List of emergency contacts -->
            <?php if (count($emergency_contacts) > 0): ?>
                <?php foreach ($emergency_contacts as $contact): ?>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-details">
                            <div class="contact-name">
                                <?php echo htmlspecialchars($contact['contact_name']); ?>
                            </div>
                            <div class="contact-info">
                                <?php echo htmlspecialchars($contact['phone_number']); ?> - 
                                <?php echo htmlspecialchars($contact['relationship']); ?>
                            </div>
                        </div>
                        <div class="contact-actions">
                            <button class="action-btn" onclick="editContact(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars($contact['contact_name'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($contact['phone_number'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($contact['relationship'], ENT_QUOTES, 'UTF-8'); ?>')"><i class="fas fa-edit"></i> Edit</button>
                            <button class="action-btn delete-btn" onclick="deleteContact(<?php echo $contact['id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                            <a href="https://wa.me/<?php echo htmlspecialchars($contact['phone_number']); ?>" class="action-btn call-btn" target="_blank"><i class="fab fa-whatsapp"></i> Call</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #6c757d; font-style: italic;">No emergency contacts added yet. Add one now!</p>
            <?php endif; ?>
        </div>
    </section>

    <script>
// Handle form submission
document.getElementById('emergencyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    console.log('Submitting form with data:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Form submission response status:', response.status);
        console.log('Form submission content-type:', response.headers.get('Content-Type'));
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Form submission raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Form submission parsed data:', data);
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error('Form submission JSON parse error:', e);
            console.error('Form submission invalid JSON:', text);
            alert('Error: Invalid response from server. Check console for details.');
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        alert('An error occurred while processing your request: ' + error.message);
    });
});

// Edit contact
function editContact(id, name, phone, relationship) {
    console.log('Editing contact with ID:', id);
    const submitButton = document.getElementById('submitButton');
    const formTitle = document.getElementById('formTitle');
    const emergencyForm = document.getElementById('emergencyForm');
    const clearButton = document.getElementById('clearButton');
    
    if (!submitButton || !formTitle || !emergencyForm || !clearButton) {
        console.error('One or more form elements not found');
        alert('Error: Form elements not found');
        return;
    }

    document.getElementById('contact_id').value = id;
    document.getElementById('contact_name').value = name;
    document.getElementById('phone_number').value = phone;
    document.getElementById('relationship').value = relationship;

    submitButton.innerHTML = '<i class="fas fa-save"></i> Save Contact';
    formTitle.textContent = 'Edit Emergency Contact';
    emergencyForm.classList.add('editing');

    clearButton.innerHTML = '<i class="fas fa-times"></i> Cancel';
    clearButton.onclick = function(e) {
        e.preventDefault();
        resetForm();
    };
}

// Delete contact
function deleteContact(id) {
    if (confirm('Are you sure you want to delete this contact?')) {
        console.log('Deleting contact with ID:', id);
        fetch('delete_emergency_contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            console.log('Delete content-type:', response.headers.get('Content-Type'));
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Delete raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Delete parsed data:', data);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('Delete JSON parse error:', e);
                console.error('Delete invalid JSON:', text);
                alert('Error: Invalid response from server. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('An error occurred while deleting the contact: ' + error.message);
        });
    }
}

// Reset form to initial state
function resetForm() {
    console.log('Resetting form');
    const form = document.getElementById('emergencyForm');
    const submitButton = document.getElementById('submitButton');
    const formTitle = document.getElementById('formTitle');
    const clearButton = document.getElementById('clearButton');

    if (!form || !submitButton || !formTitle || !clearButton) {
        console.error('One or more form elements not found');
        return;
    }

    form.reset();
    form.classList.remove('editing');
    document.getElementById('contact_id').value = '';
    submitButton.innerHTML = '<i class="fas fa-plus"></i> Add Contact';
    formTitle.textContent = 'Manage Emergency Contacts';
    clearButton.innerHTML = '<i class="fas fa-eraser"></i> Clear';
    clearButton.onclick = null;
}

// Attach reset handler to clear button by default
document.getElementById('clearButton').addEventListener('click', function(e) {
    e.preventDefault();
    resetForm();
});
</script>
</body>
</html>