<?php
require_once '../session_config.php';
require_once '../../db_connect.php';

redirect_if_not_logged_in('../../login/login.php');

$user_id = $_SESSION['user_id'];

$message = '';
$message_type = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

$medications = [];
$stmt = $mysqli->prepare("SELECT id, name, dosage, frequency, time_of_day, start_date, end_date, instructions, is_active FROM medications WHERE user_id = ? ORDER BY is_active DESC, name ASC");
if (!$stmt) {
    error_log("Prepare failed: " . $mysqli->error);
    die("An error occurred. Please try again later.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($medication = $result->fetch_assoc()) {
    $medications[] = $medication;
}
$stmt->close();

if (isset($_POST['delete_medication']) && isset($_POST['medication_id'])) {
    $medication_id = $_POST['medication_id'];
    
    $stmt = $mysqli->prepare("SELECT id FROM medications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $medication_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $delete_stmt = $mysqli->prepare("DELETE FROM medications WHERE id = ?");
        $delete_stmt->bind_param("i", $medication_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['message'] = "Medication successfully deleted.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting medication. Please try again.";
            $_SESSION['message_type'] = "error";
        }
        $delete_stmt->close();
    } else {
        $_SESSION['message'] = "You don't have permission to delete this medication.";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    
    header("Location: medication.php");
    exit();
}

if (isset($_POST['save_medication'])) {
    $medication_id = isset($_POST['medication_id']) ? $_POST['medication_id'] : null;
    $name = $_POST['name'];
    $dosage = $_POST['dosage'];
    $frequency = $_POST['frequency'];
    $time_of_day = $_POST['time_of_day'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $instructions = $_POST['instructions'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($medication_id) {
        $stmt = $mysqli->prepare("UPDATE medications SET name = ?, dosage = ?, frequency = ?, time_of_day = ?, start_date = ?, end_date = ?, instructions = ?, is_active = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssssssiis", $name, $dosage, $frequency, $time_of_day, $start_date, $end_date, $instructions, $is_active, $medication_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Medication successfully updated.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating medication. Please try again.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $stmt = $mysqli->prepare("INSERT INTO medications (user_id, name, dosage, frequency, time_of_day, start_date, end_date, instructions, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssssssi", $user_id, $name, $dosage, $frequency, $time_of_day, $start_date, $end_date, $instructions, $is_active);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Medication successfully added.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding medication. Please try again.";
            $_SESSION['message_type'] = "error";
        }
    }
    $stmt->close();
    
    header("Location: medication.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../logo.png" type="image/png">
    <title>MediAssist - Medication Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #26a69a;
            --primary-light: #64d8cb;
            --primary-dark: #00766c;
            --secondary: #80cbc4;
            --accent: #004d40;
            --light: #e0f2f1;
            --dark: #00352c;
            --gray: #f5f7fa;
            --text: #333;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--gray);
            color: var(--text);
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1rem 2rem;
            color: white;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo i {
            margin-right: 0.5rem;
            color: var(--light);
            font-size: 2rem;
            animation: pulse 2s infinite; /* Added pulse animation */
        }

        .back-button {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-button:hover {
            color: var(--light);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .add-medication-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.7rem 1.5rem;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .add-medication-btn:hover {
            background-color: var(--primary-dark);
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }

        .message.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .message.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid var(--light);
            margin-bottom: 2rem;
        }

        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab.active {
            border-bottom: 3px solid var(--primary);
            color: var(--primary);
            font-weight: bold;
        }

        .medications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .medication-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            position: relative;
        }

        .medication-inactive {
            opacity: 0.6;
            background-color: #f9f9f9;
        }

        .medication-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .action-icon {
            background-color: var(--light);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .edit-icon {
            color: var(--primary);
        }

        .delete-icon {
            color: var(--danger);
        }

        .action-icon:hover {
            transform: scale(1.1);
        }

        .medication-header {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: flex-start;
        }

        .medication-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(38, 166, 154, 0.1);
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .medication-title {
            flex-grow: 1;
        }

        .medication-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.3rem;
        }

        .medication-dosage {
            color: #666;
            font-size: 0.9rem;
        }

        .medication-badge {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .medication-details {
            margin-top: 1rem;
        }

        .detail-item {
            display: flex;
            margin-bottom: 0.8rem;
        }

        .detail-icon {
            width: 20px;
            margin-right: 1rem;
            color: var(--primary);
        }

        .detail-text {
            flex-grow: 1;
            font-size: 0.95rem;
        }

        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: flex-start;
            padding: 1rem;
            overflow-y: auto;
        }

        .modal {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-group input {
            width: auto;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .cancel-btn {
            background-color: #ddd;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .save-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .cancel-btn:hover {
            background-color: #ccc;
        }

        .save-btn:hover {
            background-color: var(--primary-dark);
        }

        .delete-confirm {
            text-align: center;
        }

        .delete-confirm p {
            margin-bottom: 2rem;
        }

        .delete-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .delete-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 0;
        }

        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty-text {
            color: #666;
            margin-bottom: 1.5rem;
        }
        /* Pulse Animation */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .medications-grid {
                grid-template-columns: 1fr;
            }

            .modal {
                width: 95%;
                max-height: calc(100vh - 4rem);
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="../accueil.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>MediAssist</span>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Medication Management</h1>
            <button class="add-medication-btn" id="openAddModal">
                <i class="fas fa-plus"></i>
                <span>Add Medication</span>
            </button>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" data-tab="all">All Medications</div>
            <div class="tab" data-tab="active">Active Medications</div>
            <div class="tab" data-tab="inactive">Inactive Medications</div>
        </div>

        <?php if (count($medications) > 0): ?>
            <div class="medications-grid">
                <?php foreach ($medications as $medication): ?>
                    <div class="medication-card <?php echo $medication['is_active'] ? '' : 'medication-inactive'; ?>" data-status="<?php echo $medication['is_active'] ? 'active' : 'inactive'; ?>">
    <div class="medication-actions">
        <div class="action-icon edit-icon" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($medication)); ?>)">
            <i class="fas fa-edit"></i>
        </div>
        <div class="action-icon delete-icon" onclick="openDeleteModal(<?php echo $medication['id']; ?>, '<?php echo htmlspecialchars($medication['name']); ?>')">
            <i class="fas fa-trash"></i>
        </div>
    </div>
    <div class="medication-header">
        <div class="medication-icon">
            <i class="fas fa-pills"></i>
        </div>
        <div class="medication-title-wrapper">
            <!-- Place the badge above the medication name -->
            <?php if ($medication['is_active']): ?>
                <div class="medication-badge">Active</div>
            <?php else: ?>
                <div class="medication-badge" style="background-color: #f5f5f5; color: #999;">Inactive</div>
            <?php endif; ?>
            <div class="medication-title">
                <div class="medication-name"><?php echo htmlspecialchars($medication['name']); ?></div>
                <div class="medication-dosage"><?php echo htmlspecialchars($medication['dosage']); ?></div>
            </div>
        </div>
    </div>
    <div class="medication-details">
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="detail-text">
                <?php echo htmlspecialchars($medication['frequency']); ?> - 
                <?php echo htmlspecialchars($medication['time_of_day']); ?>
            </div>
        </div>
        <div class="detail-item">
            <div class="detail-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="detail-text">
                From <?php echo date('M d, Y', strtotime($medication['start_date'])); ?>
                <?php if (!empty($medication['end_date'])): ?>
                    to <?php echo date('M d, Y', strtotime($medication['end_date'])); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($medication['instructions'])): ?>
            <div class="detail-item">
                <div class="detail-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="detail-text">
                    <?php echo htmlspecialchars($medication['instructions']); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="empty-text">You don't have any medications yet.</div>
                <button class="add-medication-btn" id="emptyStateAddBtn">
                    <i class="fas fa-plus"></i>
                    <span>Add Your First Medication</span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="modal-backdrop" id="medicationModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Medication</h2>
                <button class="close-btn" id="closeModal">×</button>
            </div>
            <form id="medicationForm" method="POST" action="medication.php">
                <input type="hidden" id="medication_id" name="medication_id">
                
                <div class="form-group">
                    <label for="name">Medication Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="dosage">Dosage</label>
                    <input type="text" id="dosage" name="dosage" placeholder="e.g., 500mg" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="frequency">Frequency</label>
                        <select id="frequency" name="frequency" required>
                            <option value="">Select Frequency</option>
                            <option value="Once daily">Once daily</option>
                            <option value="Twice daily">Twice daily</option>
                            <option value="Three times daily">Three times daily</option>
                            <option value="Four times daily">Four times daily</option>
                            <option value="Every other day">Every other day</option>
                            <option value="Weekly">Weekly</option>
                            <option value="As needed">As needed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_of_day">Time of Day</label>
                        <input type="time" id="time_of_day" name="time_of_day" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date (optional)</label>
                        <input type="date" id="end_date" name="end_date">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="instructions">Special Instructions</label>
                    <textarea id="instructions" name="instructions" placeholder="Any special instructions or notes"></textarea>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        <label for="is_active">Active Medication</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelModal">Cancel</button>
                    <button type="submit" class="save-btn" name="save_medication">Save Medication</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="deleteModal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Delete Medication</h2>
                <button class="close-btn" id="closeDeleteModal">×</button>
            </div>
            <div class="delete-confirm">
                <p>Are you sure you want to delete <span id="deleteMedicationName"></span>? This action cannot be undone.</p>
                <div class="delete-actions">
                    <button class="cancel-btn" id="cancelDeleteModal">Cancel</button>
                    <form method="POST" action="medication.php" id="deleteForm">
                        <input type="hidden" id="delete_medication_id" name="medication_id">
                        <button type="submit" class="delete-btn" name="delete_medication">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const medicationModal = document.getElementById('medicationModal');
        const deleteModal = document.getElementById('deleteModal');
        const openAddModalBtn = document.getElementById('openAddModal');
        const emptyStateAddBtn = document.getElementById('emptyStateAddBtn');
        const closeModalBtn = document.getElementById('closeModal');
        const cancelModalBtn = document.getElementById('cancelModal');
        const closeDeleteModalBtn = document.getElementById('closeDeleteModal');
        const cancelDeleteModalBtn = document.getElementById('cancelDeleteModal');
        const medicationForm = document.getElementById('medicationForm');
        const modalTitle = document.getElementById('modalTitle');
        
        const tabs = document.querySelectorAll('.tab');
        const medicationCards = document.querySelectorAll('.medication-card');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                const tabValue = tab.dataset.tab;
                
                medicationCards.forEach(card => {
                    if (tabValue === 'all' || card.dataset.status === tabValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
        
        if (openAddModalBtn) {
            openAddModalBtn.addEventListener('click', () => {
                modalTitle.textContent = 'Add New Medication';
                medicationForm.reset();
                document.getElementById('medication_id').value = '';
                medicationModal.style.display = 'flex';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        
        if (emptyStateAddBtn) {
            emptyStateAddBtn.addEventListener('click', () => {
                modalTitle.textContent = 'Add New Medication';
                medicationForm.reset();
                document.getElementById('medication_id').value = '';
                medicationModal.style.display = 'flex';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        
        closeModalBtn.addEventListener('click', () => {
            medicationModal.style.display = 'none';
        });
        
        cancelModalBtn.addEventListener('click', () => {
            medicationModal.style.display = 'none';
        });
        
        closeDeleteModalBtn.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
        
        cancelDeleteModalBtn.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
        
        window.addEventListener('click', (e) => {
            if (e.target === medicationModal) {
                medicationModal.style.display = 'none';
            }
            if (e.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
        
        function openEditModal(medication) {
            modalTitle.textContent = 'Edit Medication';
            document.getElementById('medication_id').value = medication.id;
            document.getElementById('name').value = medication.name;
            document.getElementById('dosage').value = medication.dosage;
            document.getElementById('frequency').value = medication.frequency;
            document.getElementById('time_of_day').value = medication.time_of_day;
            document.getElementById('start_date').value = medication.start_date;
            document.getElementById('end_date').value = medication.end_date || '';
            document.getElementById('instructions').value = medication.instructions || '';
            document.getElementById('is_active').checked = medication.is_active == 1;
            medicationModal.style.display = 'flex';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function openDeleteModal(id, name) {
            document.getElementById('delete_medication_id').value = id;
            document.getElementById('deleteMedicationName').textContent = name;
            deleteModal.style.display = 'flex';
        }

        medicationForm.addEventListener('submit', (e) => {
            const name = document.getElementById('name').value.trim();
            const dosage = document.getElementById('dosage').value.trim();
            const frequency = document.getElementById('frequency').value;
            const timeOfDay = document.getElementById('time_of_day').value;
            const startDate = document.getElementById('start_date').value;

            if (!name || !dosage || !frequency || !timeOfDay || !startDate) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }

            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(startDate)) {
                e.preventDefault();
                alert('Please enter a valid start date.');
                return;
            }

            const endDate = document.getElementById('end_date').value;
            if (endDate && !dateRegex.test(endDate)) {
                e.preventDefault();
                alert('Please enter a valid end date.');
                return;
            }

            if (endDate && new Date(endDate) < new Date(startDate)) {
                e.preventDefault();
                alert('End date cannot be before start date.');
                return;
            }
        });

        const messageDiv = document.querySelector('.message');
        if (messageDiv) {
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        document.getElementById('start_date').value = new Date().toISOString().split('T')[0];
        medicationForm.addEventListener('submit', (e) => {
    const name = document.getElementById('name').value.trim();
    const dosage = document.getElementById('dosage').value.trim();
    const frequency = document.getElementById('frequency').value;
    const timeOfDay = document.getElementById('time_of_day').value;
    const startDate = document.getElementById('start_date').value;

    // Validation des champs requis
    if (!name || !dosage || !frequency || !timeOfDay || !startDate) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return;
    }

    // Validation du nom (lettres et espaces uniquement, minimum 2 caractères)
    const nameRegex = /^[a-zA-Z\s]{2,}$/;
    if (!nameRegex.test(name)) {
        e.preventDefault();
        alert('Medication name must contain only letters and spaces, and be at least 2 characters long.');
        return;
    }

    // Validation de la posologie (doit être un nombre entre 1 et 1000)
    const dosageValue = parseFloat(dosage);
    if (isNaN(dosageValue) || dosageValue <= 0 || dosageValue > 1000) {
        e.preventDefault();
        alert('Dosage must be a number between 1 and 1000.');
        return;
    }

    // Validation des dates
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(startDate)) {
        e.preventDefault();
        alert('Please enter a valid start date.');
        return;
    }

    const endDate = document.getElementById('end_date').value;
    if (endDate && !dateRegex.test(endDate)) {
        e.preventDefault();
        alert('Please enter a valid end date.');
        return;
    }

    if (endDate && new Date(endDate) < new Date(startDate)) {
        e.preventDefault();
        alert('End date cannot be before start date.');
        return;
    }
});
    </script>
</body>
</html>