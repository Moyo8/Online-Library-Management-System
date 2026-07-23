<?php
/**
 * Application routes
 */
$router = new App\Core\Router();

// ========== WEB ROUTES ==========

// Home routes
$router->add('GET', '/', 'Home@index');

// User registration
$router->add('GET', '/home/register', 'Home@register');
$router->add('POST', '/home/register', 'Home@registerPost');
$router->add('GET', '/register', 'Home@register'); // Legacy alias

// Fines management
$router->add('GET', '/fines', 'Fines@index');
$router->add('POST', '/fines', 'Fines@index');
$router->add('GET', '/home/login', 'Home@login');
$router->add('POST', '/home/login', 'Home@loginPost');
$router->add('GET', '/home/logout', 'Home@logout');

// Admin routes
$router->add('GET', '/admin/dashboard', 'Admin@dashboard');

// User routes
$router->add('GET', '/user/dashboard', 'User@dashboard');
$router->add('GET', '/user/borrow', 'User@borrow');
$router->add('GET', '/user/fines', 'User@fines');

// My Books route
$router->add('GET', '/my/books', 'MyBooks@index');
$router->add('GET', '/my_books', 'MyBooks@index');
$router->add('POST', '/my-books/return', 'MyBooks@returnBook');

// User Reservation route
$router->add('GET', '/user/reservation/create', 'UserReservation@create');
$router->add('POST', '/user/reservation/create', 'UserReservation@create');
$router->add('GET', '/user/reservation/cancel/([0-9]+)', 'UserReservation@cancel');

// User AI Assistant route
$router->add('GET', '/user/ai', 'UserAi@index');
$router->add('POST', '/user/ai', 'UserAi@index');
$router->add('GET', '/user/ai/new', 'UserAi@newSession');
$router->add('POST', '/user/ai/delete', 'UserAi@deleteSession');

// Search route
$router->add('GET', '/search', 'Search@index');

// Book routes (Staff only)
$router->add('GET', '/books', 'Book@index');
$router->add('GET', '/books/create', 'Book@create');
$router->add('POST', '/books/store', 'Book@store');
$router->add('GET', '/books/edit/([0-9]+)', 'Book@edit');
$router->add('POST', '/books/update/([0-9]+)', 'Book@update');
$router->add('POST', '/books/delete/([0-9]+)', 'Book@delete');
$router->add('GET', '/books/view/([0-9]+)', 'Book@view');

// User Management routes (Admin only)
$router->add('GET', '/users', 'UserManagement@index');
$router->add('GET', '/users/create', 'UserManagement@create');
$router->add('POST', '/users/store', 'UserManagement@store');
$router->add('GET', '/users/edit/([0-9]+)', 'UserManagement@edit');
$router->add('POST', '/users/update/([0-9]+)', 'UserManagement@update');
$router->add('POST', '/users/delete/([0-9]+)', 'UserManagement@delete');
$router->add('GET', '/users/view/([0-9]+)', 'UserManagement@view');

// Transaction routes (Staff only)
$router->add('GET', '/transactions', 'Transaction@index');
$router->add('GET', '/transactions/issue', 'Transaction@issue');
$router->add('POST', '/transactions/issue-book', 'Transaction@issueBook');
$router->add('GET', '/transactions/return/([0-9]+)', 'Transaction@returnForm');
$router->add('POST', '/transactions/return-book/([0-9]+)', 'Transaction@returnBook');
$router->add('GET', '/transactions/view/([0-9]+)', 'Transaction@view');
$router->add('GET', '/transactions/overdue', 'Transaction@overdue');

// Reservation routes (Staff only)
$router->add('GET', '/reservations', 'Reservation@index');
$router->add('GET', '/reservations/create', 'Reservation@create');
$router->add('POST', '/reservations/store', 'Reservation@createReservation');
$router->add('GET', '/reservations/fulfill/([0-9]+)', 'Reservation@fulfill');
$router->add('POST', '/reservations/fulfill/([0-9]+)', 'Reservation@fulfillReservation');
$router->add('GET', '/reservations/cancel/([0-9]+)', 'Reservation@cancel');
$router->add('POST', '/reservations/cancel/([0-9]+)', 'Reservation@cancelReservation');
$router->add('GET', '/reservations/view/([0-9]+)', 'Reservation@view');
$router->add('GET', '/reservations/pending-for-book/([0-9]+)', 'Reservation@pendingForBook');

// Report routes (Admin only)
$router->add('GET', '/reports', 'Report@index');
$router->add('GET', '/reports/book-circulation', 'Report@bookCirculation');
$router->add('GET', '/reports/user-activity', 'Report@userActivity');
$router->add('GET', '/reports/fines', 'Report@fines');
$router->add('GET', '/reports/overdue', 'Report@overdue');
$router->add('GET', '/reports/reservation', 'Report@reservation');
$router->add('GET', '/reports/export-csv', 'Report@exportCSV');
$router->add('GET', '/reports/export-json', 'Report@exportJSON');

// AI Insights route
$router->add('GET', '/ai/insights', 'AiInsights@index');
$router->add('POST', '/ai/insights', 'AiInsights@index');
$router->add('GET', '/ai/insights/new', 'AiInsights@newSession');
$router->add('POST', '/ai/insights/delete', 'AiInsights@deleteSession');

// ========== API ROUTES ==========
// All API routes under /api/v1/
$router->add('GET', '/api/v1/books', 'API@getBooks');
$router->add('GET', '/api/v1/books/([0-9]+)', 'API@getBook');
$router->add('POST', '/api/v1/books', 'API@createBook');
$router->add('PUT', '/api/v1/books/([0-9]+)', 'API@updateBook');
$router->add('DELETE', '/api/v1/books/([0-9]+)', 'API@deleteBook');

$router->add('GET', '/api/v1/users', 'API@getUsers');
$router->add('GET', '/api/v1/users/([0-9]+)', 'API@getUser');

$router->add('GET', '/api/v1/transactions', 'API@getTransactions');
$router->add('POST', '/api/v1/transactions/issue-book', 'API@issueBook');
$router->add('PUT', '/api/v1/transactions/return-book/([0-9]+)', 'API@returnBook');
$router->add('GET', '/api/v1/transactions/overdue', 'API@getOverdueBooks');

$router->add('GET', '/api/v1/reservations', 'API@getReservations');
$router->add('POST', '/api/v1/reservations', 'API@createReservation');
$router->add('PUT', '/api/v1/reservations/fulfill/([0-9]+)', 'API@fulfillReservation');
$router->add('PUT', '/api/v1/reservations/cancel/([0-9]+)', 'API@cancelReservation');

$router->add('POST', '/api/v1/auth/login', 'API@login');
$router->add('GET', '/api/v1/auth/profile', 'API@profile');

// ========== LEGACY ROUTE REDIRECTS ==========
$router->add('GET', '/login', 'Home@login');
$router->add('POST', '/login', 'Home@loginPost');
$router->add('GET', '/admin/manage_books', 'Book@index');
$router->add('GET', '/admin/manage_users', 'UserManagement@index');
$router->add('GET', '/admin/issue_book', 'Transaction@issue');
$router->add('GET', '/admin/return_book', 'Transaction@return');
?>