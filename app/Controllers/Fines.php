<?php
namespace App\Controllers;

use App\Core\Controller;

/**
 * Fines Controller (Admin/Librarian only)
 */
class Fines extends Controller
{
    /**
     * Check if user is admin or librarian
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'librarian'])) {
            $this->redirect('/home/login');
        }
    }

    /**
     * Show fines management page
     */
    public function index()
    {
        $error = '';
        $success = '';

        // Handle fine payment
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transaction_id = (int)($_POST['transaction_id'] ?? 0);
            $amount_paid = (float)($_POST['amount_paid'] ?? 0);

            if ($transaction_id <= 0) {
                $error = 'Please select a valid transaction';
            } elseif ($amount_paid <= 0) {
                $error = 'Please enter a valid payment amount';
            } else {
                // Get transaction details using model
                $transactionModel = $this->loadModel('Transaction');
                $transaction = $transactionModel->getById($transaction_id);

                if (!$transaction) {
                    $error = 'Transaction not found';
                } else {
                    $remaining_fine = max(0, $transaction['fine'] - $amount_paid);

                    // Update transaction with payment using model
                    $transactionModel->query(
                        'UPDATE transactions SET fine_paid = fine_paid + ? WHERE id = ?',
                        [$amount_paid, $transaction_id]
                    );

                    if ($remaining_fine <= 0) {
                        $success = 'Fine paid in full';
                    } else {
                        $success = 'Partial payment received. Remaining balance: $' . number_format($remaining_fine, 2);
                    }
                }
            }
        }

        // Get transactions with fines using model
        $transactionModel = $this->loadModel('Transaction');
        $fines = $transactionModel->findAll(
            'SELECT t.id, u.name as user_name, b.title as book_title,
                     t.fine, COALESCE(t.fine_paid, 0) as fine_paid,
                     (t.fine - COALESCE(t.fine_paid, 0)) as balance_due,
                     t.issue_date, t.due_date, t.return_date
                     FROM transactions t
                     JOIN users u ON t.user_id = u.id
                     JOIN books b ON t.book_id = b.id
                     WHERE t.fine > 0 AND t.return_date IS NOT NULL
                     ORDER BY t.return_date DESC'
        );

        $this->view->assign('fines', $fines);
        $this->view->assign('error', $error);
        $this->view->assign('success', $success);
        $this->view->render('fines/index', 'layouts/admin');
    }
}