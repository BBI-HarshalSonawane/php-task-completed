<?php
    session_start();

    // Initialize tasks array in session
    if (!isset($_SESSION['tasks'])) {
        $_SESSION['tasks'] = [];
    }

    // Handle Theme Persistence
    $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
        $theme = $_POST['theme'];
        setcookie('theme', $theme, time() + 86400); // 1 day
    }

    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add' || $action === 'edit') {
            $taskName = filter_var($_POST['task_name'], FILTER_SANITIZE_STRING);
            $priority = filter_var($_POST['priority'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['task_description'], FILTER_SANITIZE_STRING);

            if (empty($taskName)) {
                $error = "Task Name is required.";
            } else {
                // Save last task name in cookie
                setcookie('last_task_name', $taskName, time() + 86400);

                if ($action === 'add') {
                    $taskId = uniqid(); // Generate unique task ID
                    $_SESSION['tasks'][$taskId] = [
                        'id' => $taskId,
                        'name' => $taskName,
                        'description' => $description,
                        'priority' => $priority
                    ];
                } elseif ($action === 'edit' && isset($_POST['task_id'])) {
                    $taskId = $_POST['task_id'];
                    if (isset($_SESSION['tasks'][$taskId])) {
                        $_SESSION['tasks'][$taskId] = [
                            'id' => $taskId,
                            'name' => $taskName,
                            'description' => $description,
                            'priority' => $priority
                        ];
                    }
                }
            }
        } elseif ($action === 'delete' && isset($_POST['task_id'])) {
            $taskId = $_POST['task_id'];
            unset($_SESSION['tasks'][$taskId]);
        }
    }

    // Retrieve Last Task Name from Cookie
    $lastTaskName = isset($_COOKIE['last_task_name']) ? $_COOKIE['last_task_name'] : '';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            background-color: <?= $theme === 'dark' ? '#333' : '#fff'; ?>;
            color: <?= $theme === 'dark' ? '#fff' : '#000'; ?>;
        }

        .btn {
            background-color: <?= $theme === 'dark' ? '#555' : '#ddd'; ?>;
            color: <?= $theme === 'dark' ? '#fff' : '#000'; ?>;
        }
    </style>
</head>

<body>


    <div class="container">
        <h1>Task Management System</h1>

        <!-- Theme Switcher -->
        <form method="post">
            <label>
                <input type="radio" name="theme" value="light" <?= $theme === 'light' ? 'checked' : ''; ?>> Light Theme
            </label>
            <label>
                <input type="radio" name="theme" value="dark" <?= $theme === 'dark' ? 'checked' : ''; ?>> Dark Theme
            </label>
            <button type="submit">Save Theme</button>
        </form>

        <!-- Display Last Task Added -->
        <?php if ($lastTaskName): ?>
            <p>Last Task Added: <?= htmlspecialchars($lastTaskName); ?></p>
        <?php endif; ?>

        <!-- Task Form -->
        <form method="post">
            <h2>Add/Edit Task</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?= htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="task_id" id="task_id">
            <label>
                Task Name:
                <input type="text" name="task_name" id="task_name" required>
            </label>
            <label>
                Task Description:
                <textarea name="task_description" id="task_description"></textarea>
            </label>
            <label>
                Priority:
                <select name="priority" id="priority" required>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </label>
            <button type="submit">Save Task</button>
        </form>

        <!-- Task Table -->
        <h2>Task List</h2>
        <table>
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['tasks'] as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['name']); ?></td>
                        <td><?= htmlspecialchars($task['description']); ?></td>
                        <td><?= htmlspecialchars($task['priority']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                <button type="button" onclick="editTask(<?= htmlspecialchars(json_encode($task)); ?>)">Edit</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <script>
        function editTask(task) {
            document.getElementById('task_id').value = task.id;
            document.getElementById('task_name').value = task.name;
            document.getElementById('task_description').value = task.description;
            document.getElementById('priority').value = task.priority;
            document.querySelector('[name="action"]').value = 'edit';
        }
    </script>
    
</body>

</html>