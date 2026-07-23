<?php
/**
 * Calculate fine for overdue books
 * @param string $due_date The due date in YYYY-MM-DD format
 * @param string $return_date The return date in YYYY-MM-DD format (optional, defaults to today)
 * @return array Fine calculation results
 */
function calculateFine($due_date, $return_date = null) {
    // Set return date to today if not provided
    if ($return_date === null) {
        $return_date = date('Y-m-d');
    }

    // Convert to DateTime objects
    $dueDateObj = new DateTime($due_date);
    $returnDateObj = new DateTime($return_date);

    // Calculate difference
    $interval = $returnDateObj->diff($dueDateObj);
    $daysOverdue = (int)$interval->format('%r%a');

    // Fine is $5 per day overdue (only if positive)
    $fineAmount = max(0, $daysOverdue) * 5;

    return [
        'days_overdue' => max(0, $daysOverdue),
        'fine_amount' => $fineAmount,
        'is_overdue' => $daysOverdue > 0,
        'due_date' => $due_date,
        'return_date' => $return_date
    ];
}

/**
 * Get fine for a specific transaction
 * @param int $transaction_id The transaction ID
 * @param PDO $pdo The database connection
 * @return array Fine calculation results or null if transaction not found
 */
function getTransactionFine($transaction_id, $pdo) {
    $stmt = $pdo->prepare('SELECT due_date, return_date FROM transactions WHERE id = ?');
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch();

    if (!$transaction) {
        return null;
    }

    return calculateFine($transaction['due_date'], $transaction['return_date']);
}
?>