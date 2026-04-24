<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .hint {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        .error {
            background: #ffe5e5;
            color: #b30000;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success {
            background: #e7f8ea;
            color: #1f7a1f;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .suggestion-box {
            margin-top: 10px;
            background: #eef6ff;
            border-left: 4px solid #3399ff;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            margin-top: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #0056b3;
        }

        ul {
            margin: 8px 0 0 18px;
            color: #444;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Submit Employee Ticket</h2>

    <?php
    $errors = [];
    $success = "";
    $suggestion = "";

    $reason = $_POST['reason'] ?? '';
    $category = $_POST['category'] ?? '';
    $details = $_POST['details'] ?? '';

    function isTooVague($text) {
        $vagueWords = ['help', 'problem', 'issue', 'error', 'wrong', 'not working', 'pls fix', 'urgent'];
        $textLower = strtolower(trim($text));

        if (str_word_count($textLower) < 8) {
            return true;
        }

        foreach ($vagueWords as $word) {
            if ($textLower === $word || strpos($textLower, $word) !== false && str_word_count($textLower) < 12) {
                return true;
            }
        }

        return false;
    }

    function buildSuggestion($category) {
        switch ($category) {
            case 'Payroll':
                return "Please include: payroll period, expected amount, actual amount, and when the issue started.";
            case 'Attendance':
                return "Please include: date, time, missing/incorrect logs, and device/location used for attendance.";
            case 'IT':
                return "Please include: device name, system/app affected, exact error message, and steps before the issue happened.";
            case 'HR':
                return "Please include: request type, related date, people involved if needed, and complete background of the concern.";
            default:
                return "Please explain what happened, when it happened, where it happened, and what result you expected.";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty(trim($category))) {
            $errors[] = "Please select a category.";
        }

        if (empty(trim($details))) {
            $errors[] = "Detailed description is required.";
        } elseif (strlen(trim($details)) < 30) {
            $errors[] = "Detailed description must be at least 30 characters.";
        } elseif (isTooVague($details)) {
            $errors[] = "Your description is too short or unclear. Please provide more complete details.";
            $suggestion = buildSuggestion($category);
        }

        if (empty($errors)) {
            $success = "Ticket submitted successfully.";
            // Save to database here
            // Example: INSERT INTO tickets (category, reason, details) VALUES (...)
        }
    }
    ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($suggestion)): ?>
        <div class="suggestion-box">
            <strong>Suggestion:</strong><br>
            <?= htmlspecialchars($suggestion) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="category">Ticket Category</label>
        <select name="category" id="category" required>
            <option value="">-- Select Category --</option>
            <option value="HR" <?= $category === 'HR' ? 'selected' : '' ?>>HR Concern</option>
            <option value="Payroll" <?= $category === 'Payroll' ? 'selected' : '' ?>>Payroll</option>
            <option value="Attendance" <?= $category === 'Attendance' ? 'selected' : '' ?>>Attendance</option>
            <option value="IT" <?= $category === 'IT' ? 'selected' : '' ?>>IT / System Issue</option>
        </select>

        <label for="reason">Reason Title</label>
        <input 
            type="text" 
            name="reason" 
            id="reason" 
            maxlength="100"
            value="<?= htmlspecialchars($reason) ?>" 
            placeholder="Example: Unable to log attendance on March 30"
            
        >
        <div class="hint">Use a short but clear title.</div>

        <label for="details">Detailed Description</label>
        <textarea 
            name="details" 
            id="details" 
            placeholder="Explain clearly what happened, when it happened, where it happened, and what you expected."
            required
        ><?= htmlspecialchars($details) ?></textarea>

        <div class="hint">
            Please include:
            <ul>
                <li>What happened?</li>
                <li>When did it happen?</li>
                <li>What system, department, or process is affected?</li>
                <li>What result were you expecting?</li>
            </ul>
        </div>

        <button type="submit">Submit Ticket</button>
    </form>
</div>
</body>
</html>