<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');

$eventID = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
if (!$eventID) {
    header('Location: events.php?error=invalid_event');
    exit();
}

$eventStmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking FROM Event WHERE EventID = ?');
$eventStmt->bind_param('i', $eventID);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();
if (!$event) {
    header('Location: events.php?error=not_found');
    exit();
}

$canManageTasks = in_array(currentRole(), ['admin', 'organizer'], true);
$statuses = ['Todo', 'In Progress', 'Done', 'Blocked'];
$priorities = ['Low', 'Normal', 'High', 'Urgent'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    $taskID = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);

    if ($action === 'create') {
        if (!$canManageTasks) {
            $error = 'Only admins and organizers can create tasks.';
        } else {
            $title = trim($_POST['title'] ?? '');
            $details = trim($_POST['details'] ?? '');
            $assignedTo = trim($_POST['assigned_to'] ?? '');
            $dueDate = trim($_POST['due_date'] ?? '');
            $priority = $_POST['priority'] ?? 'Normal';

            if ($title === '' || mb_strlen($title) > 180) {
                $error = 'Enter a task title up to 180 characters.';
            } elseif (!in_array($priority, $priorities, true)) {
                $error = 'Choose a valid priority.';
            } elseif ($dueDate !== '' && !DateTime::createFromFormat('!Y-m-d', $dueDate)) {
                $error = 'Choose a valid due date.';
            } else {
                $stmt = $conn->prepare('INSERT INTO event_tasks (EventID, Title, Details, Assigned_To, Due_Date, Priority, Created_By) VALUES (?, ?, ?, ?, NULLIF(?, \'\'), ?, ?)');
                $username = (string) $_SESSION['username'];
                $stmt->bind_param('issssss', $eventID, $title, $details, $assignedTo, $dueDate, $priority, $username);
                if (!$stmt->execute()) {
                    $error = 'Unable to create the task.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'status') {
        $status = $_POST['status'] ?? '';
        if (!$taskID || !in_array($status, $statuses, true)) {
            $error = 'Choose a valid task status.';
        } else {
            $stmt = $conn->prepare('UPDATE event_tasks SET Task_Status = ? WHERE Task_ID = ? AND EventID = ?');
            $stmt->bind_param('sii', $status, $taskID, $eventID);
            if (!$stmt->execute()) {
                $error = 'Unable to update the task status.';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        if (!$canManageTasks) {
            $error = 'Only admins and organizers can delete tasks.';
        } elseif (!$taskID) {
            $error = 'Invalid task.';
        } else {
            $stmt = $conn->prepare('DELETE FROM event_tasks WHERE Task_ID = ? AND EventID = ?');
            $stmt->bind_param('ii', $taskID, $eventID);
            if (!$stmt->execute()) {
                $error = 'Unable to delete the task.';
            }
            $stmt->close();
        }
    } else {
        $error = 'Unsupported task action.';
    }
}

$taskStmt = $conn->prepare('SELECT Task_ID, Title, Details, Assigned_To, Due_Date, Priority, Task_Status, Created_By FROM event_tasks WHERE EventID = ? ORDER BY FIELD(Task_Status, \'Blocked\', \'In Progress\', \'Todo\', \'Done\'), Due_Date IS NULL, Due_Date, Task_ID DESC');
$taskStmt->bind_param('i', $eventID);
$taskStmt->execute();
$tasks = $taskStmt->get_result();
$taskStmt->close();
$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tasks | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/legacy.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">
    <a class="navbar-brand" href="../dashboard.php">EventSpace</a>
    <div class="collapse navbar-collapse show">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
            <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
            <li class="nav-item"><a class="nav-link" href="../profile.php">Profile</a></li>
        </ul>
        <span class="text-white"><?= htmlspecialchars((string) $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</nav>

<main class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted mb-2">Event task board</p>
            <h3 class="mb-1"><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars($event['Date_OF_Booking'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <a href="events.php" class="btn btn-secondary">Back to events</a>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <?php if ($canManageTasks): ?>
    <form method="POST" class="card p-3 mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="event_id" value="<?= (int) $eventID ?>">
        <input type="hidden" name="action" value="create">
        <h5 class="mb-3">Add task</h5>
        <div class="row g-3">
            <div class="col-md-5"><label class="form-label" for="title">Task title</label><input class="form-control" id="title" name="title" maxlength="180" required></div>
            <div class="col-md-3"><label class="form-label" for="assigned_to">Assigned to</label><input class="form-control" id="assigned_to" name="assigned_to" maxlength="100"></div>
            <div class="col-md-2"><label class="form-label" for="due_date">Due date</label><input class="form-control" id="due_date" name="due_date" type="date"></div>
            <div class="col-md-2"><label class="form-label" for="priority">Priority</label><select class="form-select" id="priority" name="priority"><?php foreach ($priorities as $priority): ?><option value="<?= $priority ?>"><?= $priority ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label" for="details">Details</label><textarea class="form-control" id="details" name="details" rows="2"></textarea></div>
        </div>
        <div class="mt-3"><button class="btn btn-primary">Add task</button></div>
    </form>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead><tr><th>Task</th><th>Assigned to</th><th>Due</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($task['Title'], ENT_QUOTES, 'UTF-8') ?></strong><?php if ($task['Details']): ?><br><small class="text-muted"><?= nl2br(htmlspecialchars($task['Details'], ENT_QUOTES, 'UTF-8')) ?></small><?php endif; ?></td>
                    <td><?= htmlspecialchars($task['Assigned_To'] ?: 'Unassigned', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['Due_Date'] ?: 'No date', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($task['Priority'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="event_id" value="<?= (int) $eventID ?>">
                            <input type="hidden" name="task_id" value="<?= (int) $task['Task_ID'] ?>">
                            <input type="hidden" name="action" value="status">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()"><?php foreach ($statuses as $status): ?><option value="<?= $status ?>" <?= $task['Task_Status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select>
                        </form>
                    </td>
                    <td><?php if ($canManageTasks): ?><form method="POST" onsubmit="return confirm('Delete this task?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="event_id" value="<?= (int) $eventID ?>"><input type="hidden" name="task_id" value="<?= (int) $task['Task_ID'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-danger">Delete</button></form><?php else: ?><span class="text-muted">View only</span><?php endif; ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
