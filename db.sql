-- Create the 'users' table for user authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Create the 'books' table for managing library books
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    publication_year INT(4),
    isbn VARCHAR(20) UNIQUE
);

-- Create the 'members' table for library members
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20)
);

-- Create the 'loans' table for managing book loans (peminjaman)
CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    returned BOOLEAN DEFAULT 0, -- 0 for not returned, 1 for returned
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (member_id) REFERENCES members(member_id)
);

-- Create the 'returns' table for managing book returns (pengembalian)
CREATE TABLE returns (
    return_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    return_date DATE NOT NULL,
    FOREIGN KEY (loan_id) REFERENCES loans(loan_id)
);