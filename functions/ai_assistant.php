<?php
/**
 * AI Assistant Functions for OLMS
 *
 * Provides the complete AI response pipeline:
 *   1. getClaudeResponse() — Main entry point (tries Cloudflare API, falls back to local)
 *   2. getWorkersAIResponse() — Cloudflare Workers AI API call
 *   3. getFallbackGeminiResponse() — Intelligent local fallback with intent detection
 *
 * Also includes data-fetching helpers used by the fallback system:
 *   - getLibraryStats(), getPopularBooks(), getBooksByCategory(),
 *   - getRecentBooks(), searchBooks(), getAvailableBooksByCategory(),
 *   - getUserBorrowedBooks()
 */

// ============================================================================
// MAIN ENTRY POINT
// ============================================================================

/**
 * Get AI response — main entry point called by all controllers.
 * Tries the Cloudflare Workers AI API first; on any failure, falls through
 * to the intelligent local fallback.
 *
 * @param string $prompt User's message
 * @param string $context Role context ('user' or 'admin')
 * @return string AI response text
 */
function getClaudeResponse($prompt, $context = 'user') {
    try {
        // Attempt the Cloudflare Workers AI API
        return getWorkersAIResponse($prompt, $context);
    } catch (Throwable $e) {
        // Log the API failure and fall through to fallback
        error_log("AI API unavailable, using fallback: " . $e->getMessage());
    }

    // Intelligent local fallback — always returns a useful response
    return getFallbackGeminiResponse($prompt, $context);
}

// ============================================================================
// CLOUDFLARE WORKERS AI API
// ============================================================================

/**
 * Get AI response from Cloudflare Workers AI API
 * @param string $prompt User message
 * @param string $context Optional context (admin/user)
 * @return string AI response
 */
function getWorkersAIResponse($prompt, $context = 'user') {
    try {
        // Get Cloudflare credentials from environment
        $account_id = getenv('CLOUDFLARE_ACCOUNT_ID');
        $api_token = getenv('CLOUDFLARE_API_TOKEN');

        // Fallback to $_ENV and $_SERVER if getenv doesn't work
        if (!$account_id) {
            $account_id = $_ENV['CLOUDFLARE_ACCOUNT_ID'] ?? null;
        }
        if (!$api_token) {
            $api_token = $_ENV['CLOUDFLARE_API_TOKEN'] ?? null;
        }

        if (!$account_id || !$api_token) {
            throw new Exception('Cloudflare Workers AI credentials not configured');
        }

        // Prepare system message based on context
        $systemMessage = '';
        if ($context === 'admin') {
            $systemMessage = "You are an AI assistant for a Library Management System. You help administrators with library operations, reports, book management, and user analytics.";
        } else {
            $systemMessage = "You are an AI assistant for a Library Management System. You help users find books, check availability, manage reservations, and answer questions about library services.";
        }

        // Get library stats for context
        $stats = getLibraryStats();

        // Enhance user message with library context
        $enhancedMessage = $prompt . "\n\nLibrary Context: ";
        $enhancedMessage .= "We currently have {$stats['total_books']} total books, {$stats['available_books']} available for checkout, ";
        $enhancedMessage .= "{$stats['total_users']} registered users, {$stats['active_loans']} active loans, and {$stats['overdue_books']} overdue books.";

        // Prepare the request data
        $data = [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemMessage
                ],
                [
                    'role' => 'user',
                    'content' => $enhancedMessage
                ]
            ]
        ];

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/accounts/{$account_id}/ai/run/@cf/meta/llama-3.1-8b-instruct");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer '.$api_token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        // Execute the request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        // Close cURL
        curl_close($ch);

        // Decode the response
        $result = json_decode($response, true);

        // Check if the request was successful
        if ($result['success'] ?? false) {
            return stripMarkdown($result['result']['response'] ?? '');
        } else {
            throw new Exception('Workers AI API error: ' . ($result['errors'][0]['message'] ?? 'Unknown error'));
        }
    } catch (Throwable $e) {
        // Log the error
        error_log("Cloudflare Workers AI Error: " . $e->getMessage());
        throw $e; // Re-throw to be caught by the calling function
    }
}

// ============================================================================
// INTELLIGENT FALLBACK SYSTEM
// ============================================================================

/**
 * Intelligent fallback response generator.
 * Detects the user's intent from their query and returns specific,
 * data-driven responses using real library data instead of generic text.
 *
 * @param string $prompt User's message
 * @param string $context Role context ('user' or 'admin')
 * @return string A helpful, data-driven response
 */
function getFallbackGeminiResponse($prompt, $context = 'user') {
    $query = strtolower(trim($prompt));

    if ($context === 'admin') {
        return getAdminFallbackResponse($query);
    }

    return getUserFallbackResponse($query);
}

/**
 * Generate intelligent fallback response for regular users.
 * Matches the query against known intent patterns and returns
 * real library data formatted as natural conversation.
 *
 * @param string $query Lowercased, trimmed user query
 * @return string Formatted response
 */
function getUserFallbackResponse($query) {
    // ------------------------------------------------------------------
    // 1. Greeting patterns
    // ------------------------------------------------------------------
    if (preg_match('/^(hi|hello|hey|good\s*(morning|afternoon|evening)|greetings)\b/i', $query)) {
        $stats = getLibraryStats();
        $response = "Hello! Welcome to our library! I'm your AI assistant and I'm here to help you.\n\n";
        $response .= "Here's a quick overview of our library:\n";
        $response .= "- Total books in collection: {$stats['total_books']}\n";
        $response .= "- Books available for checkout: {$stats['available_books']}\n";
        $response .= "- Active loans right now: {$stats['active_loans']}\n\n";
        $response .= "How can I help you today? You can ask me to:\n";
        $response .= "- Recommend books or show popular titles\n";
        $response .= "- Search for books by title, author, or topic\n";
        $response .= "- Check book availability\n";
        $response .= "- Show what's new in the library";
        return $response;
    }

    // ------------------------------------------------------------------
    // 2. Book recommendation patterns
    // ------------------------------------------------------------------
    if (preg_match('/(recommend|suggest|good books|what should i read|any suggestions)/i', $query)) {
        // Try to detect genre preference from the query
        $genre = extractGenreFromQuery($query);
        if ($genre) {
            return buildCategoryResponse($genre);
        }

        // General recommendations based on popularity
        $books = getPopularBooks(5, 'all');
        if (!empty($books)) {
            $response = "Here are some highly recommended books from our collection based on popularity:\n\n";
            foreach ($books as $i => $book) {
                $num = $i + 1;
                $category = !empty($book['category']) ? " ({$book['category']})" : "";
                $loans = (int)$book['loan_count'];
                $response .= "{$num}. \"{$book['title']}\" by {$book['author']}{$category} - borrowed {$loans} time" . ($loans !== 1 ? 's' : '') . "\n";
            }
            $response .= "\nWould you like recommendations for a specific genre? Just ask! For example: \"Recommend me a mystery book\"";
            return $response;
        }
        return "I'd love to recommend books for you! Unfortunately, I couldn't retrieve our catalog right now. Please try browsing the Books section directly, or ask me again in a moment.";
    }

    // ------------------------------------------------------------------
    // 3. Popular / trending books patterns
    // ------------------------------------------------------------------
    if (preg_match('/(popular|trending|most borrowed|most read|top books|best sellers|bestsellers|hot right now)/i', $query)) {
        $period = 'month';
        $periodLabel = 'this month';
        if (preg_match('/(this year|yearly|all.?time|ever)/i', $query)) {
            $period = preg_match('/(all.?time|ever)/i', $query) ? 'all' : 'year';
            $periodLabel = $period === 'all' ? 'of all time' : 'this year';
        }

        $books = getPopularBooks(5, $period);
        $stats = getLibraryStats();

        if (!empty($books)) {
            $response = "Here are the most popular books {$periodLabel}:\n\n";
            foreach ($books as $i => $book) {
                $num = $i + 1;
                $category = !empty($book['category']) ? " ({$book['category']})" : "";
                $loans = (int)$book['loan_count'];
                $response .= "{$num}. \"{$book['title']}\" by {$book['author']}{$category} - {$loans} loan" . ($loans !== 1 ? 's' : '') . "\n";
            }
            $response .= "\nWe have {$stats['total_books']} books total with {$stats['available_books']} currently available for checkout.";
            return $response;
        }
        return "I don't have enough borrowing data to show popular books right now. Our library has {$stats['total_books']} books available — try browsing the collection or asking me to search for a specific topic!";
    }

    // ------------------------------------------------------------------
    // 4. New / recently added books patterns
    // ------------------------------------------------------------------
    if (preg_match('/(new|recently added|latest|just added|new arrivals|what\'s new|whats new)/i', $query)) {
        $books = getRecentBooks(5);
        if (!empty($books)) {
            $response = "Here are the most recently added books to our library:\n\n";
            foreach ($books as $i => $book) {
                $num = $i + 1;
                $available = max(0, (int)$book['quantity'] - (int)$book['issued_count']);
                $category = !empty($book['category']) ? " ({$book['category']})" : "";
                $status = $available > 0 ? "{$available} copies available" : "Currently all checked out";
                $response .= "{$num}. \"{$book['title']}\" by {$book['author']}{$category} - {$status}\n";
            }
            $response .= "\nWant to check out any of these? Visit the Books section to reserve or borrow.";
            return $response;
        }
        return "I couldn't retrieve the latest additions right now. Please try again in a moment or browse the Books section directly.";
    }

    // ------------------------------------------------------------------
    // 5. Genre/category browsing patterns
    // ------------------------------------------------------------------
    $genre = extractGenreFromQuery($query);
    if ($genre && preg_match('/(book|genre|category|have|any|list|show|browse)/i', $query)) {
        return buildCategoryResponse($genre);
    }

    // ------------------------------------------------------------------
    // 6. Search patterns — "search for", "find", "looking for", "do you have"
    // ------------------------------------------------------------------
    if (preg_match('/(search|find|looking for|look for|do you have|is .* available|have you got|where is|where can i find)/i', $query)) {
        // Extract the search term — strip out the command words
        $searchTerm = preg_replace('/(search\s*(for)?|find|looking\s*for|look\s*for|do you have|have you got|where\s*(is|can i find)|is\s+|available\??|any\s+|books?\s*(about|on|by|called|titled|named)?)/i', '', $query);
        $searchTerm = trim($searchTerm, " \t\n\r\0\x0B?!.,");

        if (strlen($searchTerm) >= 2) {
            $books = searchBooks($searchTerm, 8);
            if (!empty($books)) {
                $response = "I found " . count($books) . " result" . (count($books) !== 1 ? 's' : '') . " for \"{$searchTerm}\":\n\n";
                foreach ($books as $i => $book) {
                    $num = $i + 1;
                    $available = max(0, (int)$book['quantity'] - (int)$book['issued_count']);
                    $category = !empty($book['category']) ? " ({$book['category']})" : "";
                    $status = $available > 0 ? "{$available} available" : "All copies checked out";
                    $response .= "{$num}. \"{$book['title']}\" by {$book['author']}{$category} - {$status}\n";
                }
                $response .= "\nTo borrow or reserve any of these, visit the Books section.";
                return $response;
            }
            return "I couldn't find any books matching \"{$searchTerm}\" in our collection. Try different keywords, or ask me to show popular books or browse by category.";
        }
    }

    // ------------------------------------------------------------------
    // 7. Availability / "can I borrow" patterns
    // ------------------------------------------------------------------
    if (preg_match('/(available|availability|can i (borrow|checkout|check out)|what.*(can|available).*borrow)/i', $query)) {
        $categories = getAvailableBooksByCategory();
        $stats = getLibraryStats();

        if (!empty($categories)) {
            $response = "Here's our current availability by category:\n\n";
            foreach ($categories as $cat => $info) {
                $response .= "- {$cat}: {$info['available']} available out of {$info['total']} total\n";
            }
            $response .= "\nOverall: {$stats['available_books']} books available out of {$stats['total_books']} total.\n";
            $response .= "To borrow a book, visit the Books section and click 'Reserve' on any available title.";
            return $response;
        }
        $response = "We currently have {$stats['available_books']} books available for checkout out of {$stats['total_books']} total.\n";
        $response .= "Browse the Books section to see what's available, or ask me to search for a specific book!";
        return $response;
    }

    // ------------------------------------------------------------------
    // 8. User's own borrowed books / "my books" patterns
    // ------------------------------------------------------------------
    if (preg_match('/(my books|my loans|my borrowed|what have i borrowed|what do i have|my account|my checkouts|books i have)/i', $query)) {
        if (isset($_SESSION['user_id'])) {
            $borrowed = getUserBorrowedBooks((int)$_SESSION['user_id']);
            if (!empty($borrowed)) {
                $response = "Here are your currently borrowed books:\n\n";
                foreach ($borrowed as $i => $loan) {
                    $num = $i + 1;
                    $dueDate = date('M j, Y', strtotime($loan['due_date']));
                    $overdue = (strtotime($loan['due_date']) < time()) ? " (OVERDUE)" : "";
                    $response .= "{$num}. \"{$loan['book_title']}\" by {$loan['book_author']} - Due: {$dueDate}{$overdue}\n";
                }
                $response .= "\nPlease return overdue books promptly to avoid fines. You can also check your full account on the Dashboard.";
                return $response;
            }
            return "You don't have any books currently checked out. Browse the Books section to find something to read, or ask me for recommendations!";
        }
        return "To view your borrowed books, please make sure you're logged in. You can check your active loans on the Dashboard page.";
    }

    // ------------------------------------------------------------------
    // 9. How-to / FAQ patterns
    // ------------------------------------------------------------------
    if (preg_match('/(how (do|can|to)|steps to|guide|help me) .*(reserve|borrow|checkout|check out|return|renew)/i', $query)) {
        if (preg_match('/reserve/i', $query)) {
            return "To reserve a book:\n\n1. Go to the Books section from the navigation menu\n2. Find the book you'd like to reserve (use the search bar if needed)\n3. Click on the book to view its details\n4. Click the 'Reserve' button if copies are available\n5. You'll receive a confirmation — pick up the book within the reservation period\n\nNote: You can only reserve books that have available copies. Need help finding a specific book? Just ask me!";
        }
        if (preg_match('/return/i', $query)) {
            return "To return a book:\n\n1. Bring the book to the library's front desk\n2. The librarian will process your return and update the system\n3. Any applicable fines for overdue books will be calculated automatically\n\nTip: Return books on time to avoid fines! You can check your due dates on the Dashboard.";
        }
        if (preg_match('/renew/i', $query)) {
            return "To renew a borrowed book:\n\n1. Contact the library staff before your due date\n2. Renewals are subject to availability — if someone has reserved the book, renewal may not be possible\n3. Each book can typically be renewed once\n\nCheck your current due dates on the Dashboard page.";
        }
        // General borrow
        return "To borrow a book:\n\n1. Browse the Books section or ask me to search for a title\n2. Find a book with available copies\n3. Click 'Reserve' to place a hold\n4. Visit the library to pick up your reserved book\n5. The book will be issued to your account by the librarian\n\nNeed help finding a book? Just tell me what you're interested in!";
    }

    // ------------------------------------------------------------------
    // 10. Library info patterns (hours, location, contact, rules)
    // ------------------------------------------------------------------
    if (preg_match('/(library hours|opening hours|when (is|are).*open|operating hours|close|closing time|location|address|where|contact|phone|email|rules|policy|policies|fine|fines|penalty)/i', $query)) {
        if (preg_match('/(fine|fines|penalty|penalt)/i', $query)) {
            return "Library Fine Policy:\n\n- Overdue books incur a fine of \$0.10 per day past the due date\n- Fines are calculated automatically when you return the book\n- The standard loan period is 14 days\n- You can check your current fines and due dates on the Dashboard\n\nTip: Return books on time to keep your account in good standing!";
        }
        return "For specific details about library hours, location, and contact information, please check the library's main website or contact the front desk.\n\nHere's what I can help you with right now:\n- Finding and searching for books\n- Checking book availability\n- Showing popular or recently added books\n- Explaining how to borrow, reserve, or return books\n\nWhat would you like to know?";
    }

    // ------------------------------------------------------------------
    // 11. Thank you / goodbye patterns
    // ------------------------------------------------------------------
    if (preg_match('/^(thanks?|thank you|thx|ty|goodbye|bye|see you|that\'s all|that\'s it)\b/i', $query)) {
        return "You're welcome! If you need any more help with finding books, checking availability, or anything else library-related, don't hesitate to ask. Happy reading!";
    }

    // ------------------------------------------------------------------
    // 12. Catch-all: genre detection without explicit "book" keyword
    //     (e.g. user just types "mystery" or "science fiction")
    // ------------------------------------------------------------------
    if ($genre = extractGenreFromQuery($query)) {
        // If the entire query is basically just a genre name
        $stripped = preg_replace('/(books?|novels?|stories|titles?)/i', '', $query);
        $stripped = trim($stripped);
        if (strlen($stripped) <= strlen($genre) + 5) {
            return buildCategoryResponse($genre);
        }
    }

    // ------------------------------------------------------------------
    // GENERAL FALLBACK — no pattern matched
    // ------------------------------------------------------------------
    $stats = getLibraryStats();
    $response = "I'd be happy to help you with our library! We currently have {$stats['total_books']} books in our collection with {$stats['available_books']} available for checkout.\n\n";
    $response .= "Here are some things you can ask me:\n";
    $response .= "- \"Recommend me a good book\" — I'll suggest popular titles\n";
    $response .= "- \"What's new in the library?\" — See recently added books\n";
    $response .= "- \"Do you have any mystery books?\" — Browse by genre\n";
    $response .= "- \"Search for books about history\" — Find specific books\n";
    $response .= "- \"What are the most popular books?\" — See trending titles\n";
    $response .= "- \"How do I reserve a book?\" — Step-by-step guidance\n\n";
    $response .= "Just type your question and I'll do my best to help!";
    return $response;
}

/**
 * Generate intelligent fallback response for admin users.
 * Focuses on operational statistics and management insights.
 *
 * @param string $query Lowercased, trimmed admin query
 * @return string Formatted response
 */
function getAdminFallbackResponse($query) {
    // ------------------------------------------------------------------
    // 1. Overdue / late return patterns
    // ------------------------------------------------------------------
    if (preg_match('/(overdue|late return|outstanding|delinquent|past due)/i', $query)) {
        $stats = getLibraryStats();
        $response = "Overdue Books Report:\n\n";
        $response .= "- Currently overdue books: {$stats['overdue_books']}\n";
        $response .= "- Active loans: {$stats['active_loans']}\n";

        if ((int)$stats['overdue_books'] > 0 && (int)$stats['active_loans'] > 0) {
            $overdueRate = round(((int)$stats['overdue_books'] / (int)$stats['active_loans']) * 100, 1);
            $response .= "- Overdue rate: {$overdueRate}% of active loans\n";
        }

        $response .= "\nTo view the full list of overdue transactions and send reminders, go to the Transactions section and filter by 'Overdue' status.";
        return $response;
    }

    // ------------------------------------------------------------------
    // 2. Popular / most borrowed patterns (admin perspective)
    // ------------------------------------------------------------------
    if (preg_match('/(popular|most borrowed|most read|trending|top books|demand|high demand)/i', $query)) {
        $booksMonth = getPopularBooks(5, 'month');
        $booksAll = getPopularBooks(5, 'all');

        $response = "Most Borrowed Books Report:\n\n";

        if (!empty($booksMonth)) {
            $response .= "This Month:\n";
            foreach ($booksMonth as $i => $book) {
                $num = $i + 1;
                $category = !empty($book['category']) ? " [{$book['category']}]" : "";
                $response .= "  {$num}. \"{$book['title']}\" by {$book['author']}{$category} - {$book['loan_count']} loans\n";
            }
        }

        if (!empty($booksAll)) {
            $response .= "\nAll Time:\n";
            foreach ($booksAll as $i => $book) {
                $num = $i + 1;
                $category = !empty($book['category']) ? " [{$book['category']}]" : "";
                $response .= "  {$num}. \"{$book['title']}\" by {$book['author']}{$category} - {$book['loan_count']} loans\n";
            }
        }

        $response .= "\nConsider acquiring additional copies of high-demand titles to reduce wait times.";
        return $response;
    }

    // ------------------------------------------------------------------
    // 3. Statistics / dashboard overview patterns
    // ------------------------------------------------------------------
    if (preg_match('/(statistic|stats|dashboard|overview|summary|report|numbers|metrics|kpi)/i', $query)) {
        $stats = getLibraryStats();
        $categories = getAvailableBooksByCategory();

        $response = "Library Dashboard Summary:\n\n";
        $response .= "Collection:\n";
        $response .= "  - Total books: {$stats['total_books']}\n";
        $response .= "  - Available for checkout: {$stats['available_books']}\n";
        $response .= "  - Categories: " . count($categories) . "\n\n";
        $response .= "Circulation:\n";
        $response .= "  - Active loans: {$stats['active_loans']}\n";
        $response .= "  - Overdue books: {$stats['overdue_books']}\n\n";
        $response .= "Users:\n";
        $response .= "  - Registered members: {$stats['total_users']}\n\n";

        if (!empty($categories)) {
            $response .= "Collection by Category:\n";
            foreach ($categories as $cat => $info) {
                $response .= "  - {$cat}: {$info['total']} books ({$info['available']} available)\n";
            }
        }

        $response .= "\nFor detailed charts and trends, visit the Admin Dashboard.";
        return $response;
    }

    // ------------------------------------------------------------------
    // 4. Inventory / stock patterns
    // ------------------------------------------------------------------
    if (preg_match('/(inventory|stock|collection|catalog|how many books|book count|category breakdown)/i', $query)) {
        $categories = getAvailableBooksByCategory();
        $stats = getLibraryStats();

        $response = "Library Inventory Report:\n\n";
        $response .= "Total Collection: {$stats['total_books']} books\n";
        $response .= "Available: {$stats['available_books']} | Checked Out: " . ((int)$stats['total_books'] - (int)$stats['available_books']) . "\n\n";

        if (!empty($categories)) {
            $response .= "Breakdown by Category:\n";
            foreach ($categories as $cat => $info) {
                $utilization = $info['total'] > 0 ? round((($info['total'] - $info['available']) / $info['total']) * 100, 1) : 0;
                $response .= "  - {$cat}: {$info['total']} total, {$info['available']} available ({$utilization}% utilization)\n";
            }
        }

        $response .= "\nHigh utilization categories may need additional copies.";
        return $response;
    }

    // ------------------------------------------------------------------
    // 5. User / member management patterns
    // ------------------------------------------------------------------
    if (preg_match('/(user|member|patron|registration|sign.?up|how many (user|member|patron))/i', $query)) {
        $stats = getLibraryStats();
        $response = "Member Overview:\n\n";
        $response .= "- Total registered members: {$stats['total_users']}\n";
        $response .= "- Active loans: {$stats['active_loans']}\n";

        if ((int)$stats['total_users'] > 0) {
            $activeRate = round(((int)$stats['active_loans'] / (int)$stats['total_users']) * 100, 1);
            $response .= "- Borrowing engagement rate: {$activeRate}%\n";
        }

        $response .= "\nManage users from the Users section in the admin panel.";
        return $response;
    }

    // ------------------------------------------------------------------
    // Admin greeting
    // ------------------------------------------------------------------
    if (preg_match('/^(hi|hello|hey|good\s*(morning|afternoon|evening))\b/i', $query)) {
        $stats = getLibraryStats();
        $response = "Hello, Admin! Here's your quick library status:\n\n";
        $response .= "- Total books: {$stats['total_books']} ({$stats['available_books']} available)\n";
        $response .= "- Active loans: {$stats['active_loans']}\n";
        $response .= "- Overdue: {$stats['overdue_books']}\n";
        $response .= "- Registered members: {$stats['total_users']}\n\n";
        $response .= "What would you like to know more about? I can provide reports on popular books, overdue items, inventory, and more.";
        return $response;
    }

    // ------------------------------------------------------------------
    // Admin general fallback
    // ------------------------------------------------------------------
    $stats = getLibraryStats();
    $response = "Library Quick Stats:\n";
    $response .= "- Books: {$stats['total_books']} total, {$stats['available_books']} available\n";
    $response .= "- Loans: {$stats['active_loans']} active, {$stats['overdue_books']} overdue\n";
    $response .= "- Members: {$stats['total_users']}\n\n";
    $response .= "I can help you with:\n";
    $response .= "- \"Show overdue books\" — Overdue report\n";
    $response .= "- \"Most popular books\" — Borrowing trends\n";
    $response .= "- \"Library statistics\" — Full dashboard summary\n";
    $response .= "- \"Inventory report\" — Collection breakdown by category\n";
    $response .= "- \"User statistics\" — Member engagement overview\n\n";
    $response .= "What would you like to know?";
    return $response;
}

// ============================================================================
// INTENT DETECTION HELPERS
// ============================================================================

/**
 * Extract a book genre/category from the user's query.
 * Returns the matched genre name (title-cased) or null if none found.
 *
 * @param string $query Lowercased query text
 * @return string|null Matched genre or null
 */
function extractGenreFromQuery($query) {
    // Map of patterns to canonical category names
    // The keys are regex fragments; the values are how the category
    // is likely stored in the database (title case)
    $genreMap = [
        'fiction'           => 'Fiction',
        'non.?fiction'      => 'Non-Fiction',
        'mystery'           => 'Mystery',
        'thriller'          => 'Thriller',
        'romance'           => 'Romance',
        'science\s*fiction'  => 'Science Fiction',
        'sci.?fi'           => 'Science Fiction',
        'fantasy'           => 'Fantasy',
        'horror'            => 'Horror',
        'biography'         => 'Biography',
        'biographies'       => 'Biography',
        'history'           => 'History',
        'historical'        => 'History',
        'science'           => 'Science',
        'technology'        => 'Technology',
        'programming'       => 'Technology',
        'computer'          => 'Technology',
        'self.?help'        => 'Self-Help',
        'philosophy'        => 'Philosophy',
        'poetry'            => 'Poetry',
        'drama'             => 'Drama',
        'children'          => 'Children',
        'kids'              => 'Children',
        'young\s*adult'     => 'Young Adult',
        'comic'             => 'Comics',
        'graphic\s*novel'   => 'Comics',
        'cook(book|ing)'    => 'Cooking',
        'travel'            => 'Travel',
        'art'               => 'Art',
        'music'             => 'Music',
        'religion'          => 'Religion',
        'spiritual'         => 'Religion',
        'business'          => 'Business',
        'economics'         => 'Economics',
        'psychology'        => 'Psychology',
        'education'         => 'Education',
        'health'            => 'Health',
        'medical'           => 'Health',
        'sports'            => 'Sports',
        'adventure'         => 'Adventure',
        'classic'           => 'Classics',
    ];

    foreach ($genreMap as $pattern => $genre) {
        if (preg_match('/\b' . $pattern . '\b/i', $query)) {
            return $genre;
        }
    }

    return null;
}

/**
 * Build a response showing books for a specific category.
 * Includes book listing and total count.
 *
 * @param string $category Category name
 * @return string Formatted response
 */
function buildCategoryResponse($category) {
    $books = getBooksByCategory($category, 8);

    if (!empty($books)) {
        $response = "Here are the {$category} books available in our library:\n\n";
        foreach ($books as $i => $book) {
            $num = $i + 1;
            $available = max(0, (int)$book['quantity'] - (int)$book['issued_count']);
            $status = $available > 0 ? "{$available} copies available" : "Currently all checked out";
            $response .= "{$num}. \"{$book['title']}\" by {$book['author']} - {$status}\n";
        }
        $total = count($books);
        $categories = getAvailableBooksByCategory();
        if (isset($categories[$category])) {
            $catInfo = $categories[$category];
            $response .= "\nWe have {$catInfo['total']} {$category} books total, with {$catInfo['available']} currently available.";
        }
        if ($total >= 8) {
            $response .= "\nShowing the first 8 results. Browse the Books section for the full list.";
        }
        return $response;
    }

    // No exact match — try a fuzzy search using the category as a search term
    $searchResults = searchBooks($category, 5);
    if (!empty($searchResults)) {
        $response = "I didn't find a specific \"{$category}\" category, but here are some related books:\n\n";
        foreach ($searchResults as $i => $book) {
            $num = $i + 1;
            $available = max(0, (int)$book['quantity'] - (int)$book['issued_count']);
            $catLabel = !empty($book['category']) ? " ({$book['category']})" : "";
            $status = $available > 0 ? "{$available} available" : "All checked out";
            $response .= "{$num}. \"{$book['title']}\" by {$book['author']}{$catLabel} - {$status}\n";
        }
        return $response;
    }

    return "I couldn't find any {$category} books in our collection right now. Our categories might use different naming — try browsing the Books section, or ask me to search for a specific title or author.";
}

// ============================================================================
// DATA-FETCHING HELPER FUNCTIONS
// ============================================================================

/**
 * Get library-wide statistics.
 * Provides total books, available books, total users, active loans,
 * and overdue book counts.
 *
 * @return array Associative array with keys: total_books, available_books,
 *               total_users, active_loans, overdue_books
 */
function getLibraryStats() {
    try {
        $db = \Database::getInstance();
        $stats = [];

        // Total books
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM books');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['total_books'] = $row ? (int)$row['cnt'] : 0;

        // Available books (quantity > active loans for that book)
        $stmt = $db->prepare('
            SELECT COUNT(*) as cnt FROM books b
            WHERE b.quantity > (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL)
        ');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['available_books'] = $row ? (int)$row['cnt'] : 0;

        // Total users
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM users');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['total_users'] = $row ? (int)$row['cnt'] : 0;

        // Active loans
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM transactions WHERE return_date IS NULL');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['active_loans'] = $row ? (int)$row['cnt'] : 0;

        // Overdue books
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM transactions WHERE return_date IS NULL AND due_date < CURDATE()');
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['overdue_books'] = $row ? (int)$row['cnt'] : 0;

        return $stats;
    } catch (Throwable $e) {
        error_log("Error getting library stats: " . $e->getMessage());
        return [
            'total_books' => 0,
            'available_books' => 0,
            'total_users' => 0,
            'active_loans' => 0,
            'overdue_books' => 0
        ];
    }
}

/**
 * Get popular books based on loan frequency
 * @param int $limit Number of books to return
 * @param string $period Time period for popularity (month, year, all) - default: month
 * @return array List of popular books with loan counts
 */
function getPopularBooks($limit = 5, $period = 'month') {
    try {
        $db = \Database::getInstance();

        $where = "";
        if ($period === 'month') {
            $where = "WHERE t.issue_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        } elseif ($period === 'year') {
            $where = "WHERE t.issue_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        }
        // For 'all', no WHERE clause (all time)

        $sql = "SELECT b.id, b.title, b.author, b.category, COUNT(t.id) as loan_count
                FROM books b
                LEFT JOIN transactions t ON b.id = t.book_id {$where}
                GROUP BY b.id, b.title, b.author, b.category
                ORDER BY loan_count DESC
                LIMIT ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("Error getting popular books: " . $e->getMessage());
        return [];
    }
}

/**
 * Get books by category/genre
 * @param string $category Category to filter by
 * @param int $limit Number of books to return
 * @return array List of books in the category
 */
function getBooksByCategory($category, $limit = 10) {
    try {
        $db = \Database::getInstance();

        $sql = "SELECT b.id, b.title, b.author, b.category, b.quantity,
                       (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                FROM books b
                WHERE LOWER(b.category) = LOWER(?)
                ORDER BY b.title
                LIMIT ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$category, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("Error getting books by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recently added books
 * @param int $limit Number of books to return
 * @return array List of recently added books
 */
function getRecentBooks($limit = 5) {
    try {
        $db = \Database::getInstance();

        $sql = "SELECT b.id, b.title, b.author, b.category, b.quantity,
                       (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                FROM books b
                ORDER BY b.id DESC
                LIMIT ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("Error getting recent books: " . $e->getMessage());
        return [];
    }
}

/**
 * Search books by title or author
 * @param string $searchTerm Search term
 * @param int $limit Number of results to return
 * @return array List of matching books
 */
function searchBooks($searchTerm, $limit = 10) {
    try {
        $db = \Database::getInstance();

        $searchLike = "%{$searchTerm}%";
        $sql = "SELECT b.id, b.title, b.author, b.category, b.quantity,
                       (SELECT COUNT(*) FROM transactions t WHERE t.book_id = b.id AND t.return_date IS NULL) as issued_count
                FROM books b
                WHERE b.title LIKE ? OR b.author LIKE ? OR b.category LIKE ?
                ORDER BY b.title
                LIMIT ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$searchLike, $searchLike, $searchLike, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("Error searching books: " . $e->getMessage());
        return [];
    }
}

/**
 * Get available books count by category
 * @return array Associative array of category => ['total' => int, 'available' => int]
 */
function getAvailableBooksByCategory() {
    try {
        $db = \Database::getInstance();

        $sql = "SELECT b.category,
                       COUNT(b.id) as total_books,
                       SUM(CASE WHEN b.quantity > (SELECT COUNT(*) FROM transactions t2 WHERE t2.book_id = b.id AND t2.return_date IS NULL)
                              THEN 1 ELSE 0 END) as available_count
                FROM books b
                WHERE b.category IS NOT NULL AND b.category != ''
                GROUP BY b.category
                ORDER BY b.category";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        $categories = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $categories[$row['category']] = [
                'total' => (int)$row['total_books'],
                'available' => (int)$row['available_count']
            ];
        }

        return $categories;
    } catch (Throwable $e) {
        error_log("Error getting available books by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a user's currently borrowed (active loan) books.
 *
 * @param int $userId The user's ID
 * @param int $limit Maximum results to return
 * @return array List of active loans with book details
 */
function getUserBorrowedBooks($userId, $limit = 20) {
    try {
        $db = \Database::getInstance();

        $sql = "SELECT t.id as transaction_id, t.issue_date, t.due_date,
                       b.title as book_title, b.author as book_author, b.category as book_category
                FROM transactions t
                JOIN books b ON t.book_id = b.id
                WHERE t.user_id = ? AND t.return_date IS NULL
                ORDER BY t.due_date ASC
                LIMIT ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("Error getting user borrowed books: " . $e->getMessage());
        return [];
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Strip markdown formatting from AI API responses so they render
 * cleanly in the plain-text chat interface.
 *
 * Removes: bold (**), italic (*), headers (#), code blocks (```),
 * inline code (`), links [text](url), and bullet markers (- / *).
 *
 * @param string $text Text with possible markdown formatting
 * @return string Cleaned text
 */
function stripMarkdown($text) {
    if (empty($text)) {
        return '';
    }

    // Remove code blocks (``` ... ```)
    $text = preg_replace('/```[\s\S]*?```/', '', $text);

    // Remove inline code (`...`)
    $text = preg_replace('/`([^`]+)`/', '$1', $text);

    // Remove bold (**text** or __text__)
    $text = preg_replace('/\*\*(.+?)\*\*/', '$1', $text);
    $text = preg_replace('/__(.+?)__/', '$1', $text);

    // Remove italic (*text* or _text_) — but not bullet points
    $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '$1', $text);
    $text = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/', '$1', $text);

    // Remove headers (# text)
    $text = preg_replace('/^#{1,6}\s+/m', '', $text);

    // Remove markdown links [text](url) -> text
    $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);

    // Remove markdown images ![alt](url)
    $text = preg_replace('/!\[([^\]]*)\]\([^)]+\)/', '$1', $text);

    // Clean up excessive whitespace
    $text = preg_replace('/\n{3,}/', "\n\n", $text);

    return trim($text);
}
?>