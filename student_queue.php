
<?php
session_start();
require_once 'db.php';

$message = '';
$messageType = '';
$student = null;
$studentNumber = '';
$transactionType = 'Examination Payment';
$queueDetails = null;

$allowedTypes = [
    'Examination Payment',
    'Other Student Service'
];

// Preselect service when arriving from the home page.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $service = $_GET['service'] ?? '';

    if ($service === 'examination') {
        $transactionType = 'Examination Payment';
    } elseif ($service === 'other') {
        $transactionType = 'Other Student Service';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber = trim($_POST['student_number'] ?? '');
    $submittedType = $_POST['transaction_type'] ?? '';

    if (is_string($submittedType)) {
        $transactionType = $submittedType;
    }

    if (
        $studentNumber === '' ||
        !preg_match('/^[A-Za-z0-9-]{1,30}$/', $studentNumber)
    ) {
        $message = 'Please enter a valid student number.';
        $messageType = 'error';
    } elseif (
        !is_string($submittedType) ||
        !in_array($submittedType, $allowedTypes, true)
    ) {
        $message = 'Please select a valid transaction type.';
        $messageType = 'error';
        $transactionType = 'Examination Payment';
    } else {
        try {
            $pdo->beginTransaction();

            // Find the registered student.
            $stmt = $pdo->prepare(
                "SELECT student_id, student_number, first_name, last_name
                 FROM students
                 WHERE student_number = ?
                 LIMIT 1"
            );

            $stmt->execute([$studentNumber]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $pdo->rollBack();

                $message = 'Student number not found. Please check your entry.';
                $messageType = 'error';
            } else {
                // Prevent multiple active queues for the same student today.
                $stmt = $pdo->prepare(
                    "SELECT queue_number
                     FROM queue_transactions
                     WHERE student_id = ?
                       AND queue_date = CURDATE()
                       AND status IN ('Waiting', 'Serving')
                     LIMIT 1"
                );

                $stmt->execute([$student['student_id']]);
                $existingQueue = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingQueue) {
                    $pdo->rollBack();

                    $message = 'You already have an active queue today. Your queue number is '
                        . $existingQueue['queue_number'] . '.';
                    $messageType = 'error';
                } else {
                    // Find the next A-series queue number for today.
                    $stmt = $pdo->query(
                        "SELECT MAX(
                            CAST(SUBSTRING(queue_number, 2) AS UNSIGNED)
                         )
                         FROM queue_transactions
                         WHERE queue_date = CURDATE()
                           AND queue_number REGEXP '^A[0-9]+$'"
                    );

                    $lastNumber = (int) $stmt->fetchColumn();
                    $nextNumber = $lastNumber + 1;

                    $queueNumber = 'A' . str_pad(
                        (string) $nextNumber,
                        3,
                        '0',
                        STR_PAD_LEFT
                    );

                    // Save the queue request.
                    $stmt = $pdo->prepare(
                        "INSERT INTO queue_transactions
                            (student_id, queue_number, transaction_type, status, queue_date)
                         VALUES (?, ?, ?, 'Waiting', CURDATE())"
                    );

                    $stmt->execute([
                        $student['student_id'],
                        $queueNumber,
                        $transactionType
                    ]);

                    $pdo->commit();

                    // Details used by the screen and printed receipt.
                    $queueDetails = [
                        'queue_number' => $queueNumber,
                        'student_name' => $student['first_name'] . ' ' . $student['last_name'],
                        'transaction_type' => $transactionType,
                        'status' => 'Waiting',
                        'date' => date('F d, Y'),
                        'time' => date('h:i A')
                    ];

                    $message = 'Queue request created successfully!';
                    $messageType = 'success';

                    $studentNumber = '';
                    $student = null;
                    $transactionType = 'Examination Payment';
                }
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $message = 'Unable to create your queue request. Please try again.';
            $messageType = 'error';
        }
    }
}

function safe($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Queue Request | MATS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            margin: 0;
            padding: 25px 15px;
        }

        .container {
            max-width: 520px;
            margin: 35px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        h1 {
            text-align: center;
            color: #173b70;
            font-size: 25px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 18px 0 7px;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus,
        select:focus {
            outline: 2px solid #93c5fd;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            background: #173b70;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #24549a;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            overflow-wrap: anywhere;
            line-height: 1.5;
        }

        .success {
            background: #e5f7eb;
            color: #176534;
        }

        .error {
            background: #ffe8e8;
            color: #a40000;
        }

        .queue-card {
            background: #eaf2ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .queue-card h2 {
            color: #173b70;
        }

        .queue-number {
            font-size: 42px;
            font-weight: bold;
            color: #173b70;
            margin: 15px 0;
        }

        .links {
            text-align: center;
            margin-top: 22px;
            line-height: 1.5;
        }

        a {
            color: #173b70;
        }

        #print-receipt {
            display: none;
        }

        @media (max-width: 480px) {
            .container {
                padding: 22px 18px;
            }

            .queue-number {
                font-size: 36px;
            }
        }

        /* Print only the receipt, not the form or navigation. */
        @media print {
            @page {
                margin: 12mm;
            }

            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body * {
                visibility: hidden;
            }

            #print-receipt,
            #print-receipt * {
                visibility: visible;
            }

            #print-receipt {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 10px;
                margin: 0;
                background: white;
                color: black;
                font-family: Arial, sans-serif;
                font-size: 14px;
                text-align: center;
            }

            #print-receipt h2 {
                font-size: 18px;
                color: black;
                margin-bottom: 5px;
            }

            #print-receipt .receipt-number {
                font-size: 48px;
                font-weight: bold;
                margin: 20px 0;
            }

            #print-receipt p {
                overflow-wrap: anywhere;
                line-height: 1.4;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <h1>MATS Queue System</h1>
    <p class="subtitle">Student Queue Request</p>

    <?php if ($message !== ''): ?>
        <div
            class="message <?= safe($messageType) ?>"
            role="status"
            aria-live="polite"
        >
            <?= safe($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($queueDetails !== null): ?>
        <div class="queue-card">
            <h2>Your Queue Number</h2>

            <div class="queue-number">
                <?= safe($queueDetails['queue_number']) ?>
            </div>

            <p>
                <strong>Student:</strong>
                <?= safe($queueDetails['student_name']) ?>
            </p>

            <p>
                <strong>Service:</strong>
                <?= safe($queueDetails['transaction_type']) ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= safe($queueDetails['status']) ?>
            </p>

            <p>Please wait until the staff calls your queue number.</p>

            <button type="button" onclick="window.print()">
                Print Receipt Again
            </button>
        </div>

        <!-- This section is hidden on screen and shown when printing. -->
        <div id="print-receipt">
            <h2>MATS COLLEGE OF TECHNOLOGY</h2>
            <p>STUDENT QUEUE SYSTEM</p>

            <hr>

            <p>YOUR QUEUE NUMBER</p>

            <div class="receipt-number">
                <?= safe($queueDetails['queue_number']) ?>
            </div>

            <p>
                <strong>Student:</strong><br>
                <?= safe($queueDetails['student_name']) ?>
            </p>

            <p>
                <strong>Service:</strong><br>
                <?= safe($queueDetails['transaction_type']) ?>
            </p>

            <p>
                <strong>Date:</strong><br>
                <?= safe($queueDetails['date']) ?>
            </p>

            <p>
                <strong>Time:</strong><br>
                <?= safe($queueDetails['time']) ?>
            </p>

            <p><strong>Status:</strong> WAITING</p>

            <hr>

            <p>Please wait for your number to be called.</p>
            <p>Thank you!</p>
        </div>
    <?php endif; ?>

    <form method="POST" action="student_queue.php">
        <label for="student_number">Student Number</label>

        <input
            type="text"
            id="student_number"
            name="student_number"
            maxlength="30"
            pattern="[A-Za-z0-9-]+"
            value="<?= safe($studentNumber) ?>"
            placeholder="Enter your student number"
            required
        >

        <label for="transaction_type">Select Service</label>

        <select id="transaction_type" name="transaction_type" required>
            <?php foreach ($allowedTypes as $type): ?>
                <option
                    value="<?= safe($type) ?>"
                    <?= $transactionType === $type ? 'selected' : '' ?>
                >
                    <?= safe($type) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Get Queue Number</button>
    </form>

    <div class="links">
        <a href="queue_status.php">View Queue Status</a>
        <br><br>

        <a href="index.php">Back to Home</a>
        <br><br>

        <a href="staff_login.php">Staff Login</a>
    </div>
</div>

<?php if ($queueDetails !== null): ?>
<script>
    // Automatically open the browser print dialog after a successful request.
    window.addEventListener('load', function () {
        window.setTimeout(function () {
            window.print();
        }, 500);
    });
</script>
<?php endif; ?>

</body>
</html>
