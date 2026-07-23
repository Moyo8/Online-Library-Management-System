-- Add columns to books table
ALTER TABLE books ADD COLUMN category VARCHAR(100) NULL AFTER author;
ALTER TABLE books ADD COLUMN publisher VARCHAR(150) NULL AFTER category;
ALTER TABLE books ADD COLUMN published_year YEAR NULL AFTER publisher;

-- Update sample books with some category, publisher, year
UPDATE books SET
    category = 'Fiction',
    publisher = 'Scribner',
    published_year = 1925
WHERE title = 'The Great Gatsby';

UPDATE books SET
    category = 'Fiction',
    publisher = 'J. B. Lippincott & Co.',
    published_year = 1960
WHERE title = 'To Kill a Mockingbird';

UPDATE books SET
    category = 'Dystopian Fiction',
    publisher = 'Secker & Warburg',
    published_year = 1949
WHERE title = '1984';

UPDATE books SET
    category = 'Romance',
    publisher = 'T. Egerton, Whitehall',
    published_year = 1813
WHERE title = 'Pride and Prejudice';

UPDATE books SET
    category = 'Fiction',
    publisher = 'Little, Brown and Company',
    published_year = 1951
WHERE title = 'The Catcher in the Rye';

-- Nigerian authors books
UPDATE books SET
    category = 'African Literature',
    publisher = 'William Heinemann Ltd',
    published_year = 1958
WHERE title = 'Things Fall Apart';

UPDATE books SET
    category = 'Historical Fiction',
    publisher = 'Alfred A. Knopf',
    published_year = 2006
WHERE title = 'Half of a Yellow Sun';

UPDATE books SET
    category = 'Magical Realism',
    publisher = 'Jonathan Cape',
    published_year = 1991
WHERE title = 'The Famished Road';

UPDATE books SET
    category = 'Fiction',
    publisher = 'Algonquin Books',
    published_year = 1993
WHERE title = 'Purple Hibiscus';

UPDATE books SET
    category = 'African Literature',
    publisher = 'William Heinemann Ltd',
    published_year = 1964
WHERE title = 'Arrow of God';

UPDATE books SET
    category = 'Drama',
    publisher = 'W. W. Norton & Company',
    published_year = 1975
WHERE title = 'Death and the King\'s Horseman';

UPDATE books SET
    category = 'Fiction',
    publisher = 'George Braziller',
    published_year = 1979
WHERE title = 'The Joys of Motherhood';

UPDATE books SET
    category = 'Fiction',
    publisher = 'Heinemann',
    published_year = 1966
WHERE title = 'Efuru';

UPDATE books SET
    category = 'Fiction',
    publisher = 'Alfred A. Knopf',
    published_year = 2013
WHERE title = 'Americanah';

UPDATE books SET
    category = 'Folk Tales',
    publisher = 'Faber and Faber',
    published_year = 1952
WHERE title = 'The Palm-Wine Drinkard';