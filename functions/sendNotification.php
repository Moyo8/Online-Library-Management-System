<?php
/**
 * Notification system for OLMS
 * Handles sending emails, SMS, or other notifications
 */

/**
 * Send a notification (placeholder implementation)
 * In a real implementation, this would integrate with email/SMS services
 *
 * @param string $recipient Email or phone number
 * @param string $subject Notification subject
 * @param string $body Notification body
 * @param string $type Notification type: 'email', 'sms', etc.
 * @return bool True if sent successfully, false otherwise
 */
function sendNotification($recipient, $subject, $body, $type = 'email') {
    // For now, we'll log notifications to a file
    // In production, this would use PHPMailer, Twilio, or similar services

    $log_entry = sprintf(
        "[%s] Notification sent via %s to %s: %s - %s\n",
        date('Y-m-d H:i:s'),
        $type,
        $recipient,
        $subject,
        $body
    );

    // Log to notifications file
    $log_file = __DIR__ . '/../logs/notifications.log';
    $log_dir = dirname($log_file);

    // Create logs directory if it doesn't exist
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    // Append to log file
    return file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Send overdue book notification to user
 * @param int $user_id User ID
 * @param array $book Book information (title, author, etc.)
 * @param int $days_overdue Number of days overdue
 * @param float $fine_amount Fine amount in dollars
 * @return bool True if notification sent, false otherwise
 */
function sendOverdueNotification($user_id, $book, $days_overdue, $fine_amount, $pdo) {
    // Get user information
    $stmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $subject = "Overdue Book Notice - OLMS";
    $body = sprintf(
        "Hello %s,\n\nThis is a reminder that the following book is overdue:\n\nTitle: %s\nAuthor: %s\nDays Overdue: %d\nFine Amount: $%.2f\n\nPlease return the book as soon as possible to avoid additional fines.\n\nThank you,\nOLMS Library",
        $user['name'],
        $book['title'],
        $book['author'],
        $days_overdue,
        $fine_amount
    );

    return sendNotification($user['email'], $subject, $body, 'email');
}

/**
 * Send reservation availability notification to user
 * @param int $user_id User ID
 * @param array $book Book information (title, author, etc.)
 * @return bool True if notification sent, false otherwise
 */
function sendReservationAvailableNotification($user_id, $book, $pdo) {
    // Get user information
    $stmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $subject = "Book Available for Pickup - OLMS";
    $body = sprintf(
        "Hello %s,\n\nGood news! The book you reserved is now available for pickup:\n\nTitle: %s\nAuthor: %s\n\nPlease visit the library to collect your book within 3 days.\n\nThank you,\nOLMS Library",
        $user['name'],
        $book['title'],
        $book['author']
    );

    return sendNotification($user['email'], $subject, $body, 'email');
}

/**
 * Send welcome notification to new user
 * @param int $user_id User ID
 * @return bool True if notification sent, false otherwise
 */
function sendWelcomeNotification($user_id, $pdo) {
    // Get user information
    $stmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $subject = "Welcome to OLMS Library!";
    $body = sprintf(
        "Hello %s,\n\nWelcome to the Online Library Management System! You can now:\n- Search for books\n- Reserve books that are currently checked out\n- Borrow books (if you have appropriate permissions)\n- Manage your account\n\nIf you have any questions, please don't hesitate to ask.\n\nHappy reading!\nOLMS Library Team",
        $user['name']
    );

    return sendNotification($user['email'], $subject, $body, 'email');
}
?>