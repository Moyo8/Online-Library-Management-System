<?php
/**
 * Check book availability
 * @param int $book_id The book ID
 * @param PDO $pdo The database connection
 * @return array Availability information
 */
function checkAvailability($book_id, $pdo) {
    $stmt = $pdo->prepare('SELECT b.*,
                          (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                          FROM books b WHERE b.id = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        return [
            'exists' => false,
            'title' => '',
            'author' => '',
            'quantity' => 0,
            'issued_count' => 0,
            'available_copies' => 0,
            'is_available' => false
        ];
    }

    $issued_count = $book['issued_count'] ?? 0;
    $available_copies = max(0, $book['quantity'] - $issued_count);

    return [
        'exists' => true,
        'title' => $book['title'],
        'author' => $book['author'],
        'isbn' => $book['isbn'],
        'quantity' => $book['quantity'],
        'issued_count' => $issued_count,
        'available_copies' => $available_copies,
        'is_available' => $available_copies > 0
    ];
}

/**
 * Check if a book is available for reservation
 * @param int $book_id The book ID
 * @param PDO $pdo The database connection
 * @return bool True if available for reservation, false otherwise
 */
function isAvailableForReservation($book_id, $pdo) {
    $availability = checkAvailability($book_id, $pdo);
    return $availability['exists'] && !$availability['is_available'];
}
?>