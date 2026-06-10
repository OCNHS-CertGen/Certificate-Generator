<?php
$ref = $_GET['ref'] ?? 'ERROR';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted | OCNHS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-card {
            text-align: center;
            padding: 40px;
        }

        .success-icon {
            font-size: 80px;
            color: #2ecc71;
            margin-bottom: 20px;
            animation: bounce 1s ease infinite alternate;
        }

        @keyframes bounce {
            from {
                transform: translateY(0);
            }

            to {
                transform: translateY(-10px);
            }
        }

        .ref-box {
            background: rgba(0, 45, 114, 0.05);
            border: 2px dashed var(--primary-color);
            padding: 20px;
            border-radius: var(--radius-md);
            margin: 30px 0;
            display: inline-block;
            width: 100%;
        }

        .ref-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: 2px;
            font-family: monospace;
        }

        .instructions {
            text-align: left;
            margin-top: 30px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .action-btn {
            background: var(--primary-color);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary-light);
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="watermark-bg"></div>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1 class="form-title" style="background: none; -webkit-text-fill-color: var(--primary-color);">Request
                Submitted Successfully!</h1>
            <p>Natanggap na namin ang iyong request. Pakatandaan ang iyong Reference Number sa ibaba para sa pag-track
                ng iyong certificate.</p>

            <div class="ref-box">
                <p style="margin: 0; font-weight: 600; color: var(--text-muted);">YOUR REFERENCE NUMBER:</p>
                <div class="ref-number"><?= htmlspecialchars($ref) ?></div>
            </div>

            <div class="instructions">
                <strong>Ano ang susunod na hakbang?</strong>
                <ul>
                    <li>I-screenshot o i-save ang Reference Number na ito.</li>
                    <li>Maghintay ng 3 hanggang 5 working days para sa pag-proseso.</li>
                    <li>Makatatanggap ka ng notification sa iyong email kapag handa na ang iyong dokumento.</li>
                </ul>
            </div>

            <a href="apply.php" class="action-btn">Go Back</a>
        </div>
    </div>
</body>

</html>