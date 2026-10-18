
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MATS Smart Queue System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        header {
            background: #123b70;
            color: white;
            padding: 24px 20px;
            text-align: center;
        }

        header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        header p {
            margin: 0;
            color: #dbeafe;
        }

        main {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome h2 {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #64748b;
            line-height: 1.6;
        }

        .services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .service-card {
            background: white;
            padding: 28px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .service-card h3 {
            color: #123b70;
            margin-top: 0;
        }

        .service-card p {
            color: #64748b;
            line-height: 1.6;
            min-height: 48px;
        }

        
.service-card button,
.service-button {
    display: block;
    width: 100%;
    padding: 12px;
    background: #123b70;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    text-decoration: none;
    text-align: center;
}

.service-card button {
    cursor: not-allowed;
    opacity: 0.7;
}

.service-button {
    cursor: pointer;
}

.service-button:hover {
    background: #1d4f91;
}

    </style>
</head>

<body>

<header>
    <h1>MATS College of Technology</h1>
    <p>IoT-Based Smart Queue Management and Payment Monitoring System</p>
</header>

<main>
    <section class="welcome">
        <h2>Welcome to the Student Kiosk</h2>
        <p>
            Please select the service you need.
            The system will guide you through the process
            of requesting a queue number.
        </p>
    </section>

    <section class="services">

        <div class="service-card">
            <h3>Student Registration</h3>
            <p>
                Enter your student information
                to register in the system.
            </p>
            
<a class="service-button" href="register.php">
    Register Student
</a>

        </div>

        
<div class="service-card">
    <h3>Examination Payment</h3>
    <p>
        Request a queue number for
        examination payment transactions.
    </p>

    <a class="service-button" href="student_queue.php">
        Request a Queue Number
    </a>
</div>

            
        </div>

        <div class="service-card">
            <h3>Queue Information</h3>
            <p>
                View queue information and
                learn about the service process.
            </p>
            <a class="service-button" href="queue_status.php">
     Queue Status
</a>
        </div>

    </section>

    <p class="notice">
        This is the initial interface.
        Service functions will be added in the next steps.
    </p>
</main>

<footer>
    &copy; 2026 MATS College of Technology
</footer>

</body>
</html>
