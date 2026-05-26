# Project Documentation

## 1. Project Title
**Online Voting System (DSA)**

## 2. Introduction
The Online Voting System is a comprehensive, full-stack web application designed to simulate and handle the core functionalities of an official election process. The system separates user concerns from administration, ensuring a secure and structured environment. It explicitly satisfies the final course requirements by integrating fundamental Data Structures and Algorithms (DSA)—specifically Arrays and Binary Search Trees (BST)—into its core PHP processing logic to sort, process, and rank candidates dynamically without relying strictly on database-level sorting.

## 3. Objectives
* **Demonstrate DSA Application:** To practically implement Arrays and Binary Search Trees (BST) in processing, traversing, and sorting tabular data.
* **Separation of Concerns:** To provide a dedicated, user-friendly portal for voter registration/voting and a protected Admin Dashboard for system management.
* **System Integrity & Security:** To handle invalid inputs appropriately and prevent double voting through strict database transaction checks, foreign key constraints, and unique Voter IDs.
* **Real-World Election Features:** To implement official election mechanics such as multi-position ballots and "Straight-Ticket" (party-based) voting.
* **Maintainable & Responsive Architecture:** To develop a CRUD-capable application using modular PHP files and a mobile-friendly UI using Tailwind CSS.

## 4. System Features
**User Portal (`index.php` & `register.php`):**
* **Voter Registration (Create):** Users can register with a name and a unique Voter ID.
* **Multi-Position Voting (Create/Update):** Voters can cast a ballot containing multiple candidates across different positions (e.g., Governor, 2nd Year Rep).
* **Straight-Ticket Voting:** A DOM-manipulation feature allowing voters to select all candidates from a specific party with a single click.
* **Double-Vote Prevention:** Backend validation rejects duplicate submissions from the same Voter ID.

**Admin Dashboard (`admin.php` & `admin_login.php`):**
* **Secure Login:** Password-protected entry point.
* **Candidate Management (Create):** Admin can register new candidates with specified Positions and Party affiliations.
* **Real-Time Party Standings (Read/Process):** Aggregates and displays total votes per party.
* **Live Leaderboard (Read/Process):** Displays real-time candidate rankings sorted natively by the custom data structures engine.
* **System Reset (Delete):** A "Danger Zone" feature to safely truncate all tables and restart the election.

## 5. Data Structures Used
To strictly fulfill the course requirements, the application extracts raw data from the MySQL database and processes it in the application layer (PHP) using two specific structures:

1. **Arrays (Associative & Multidimensional Arrays):**
   * **Usage:** Handled primarily in `admin.php` and `index.php`. The result set retrieved from the database is stored in an iterative Array matrix. This array acts as the buffering data structure. It is also used to dynamically group candidates by position on the ballot.
2. **Trees (Binary Search Tree - BST):**
   * **Usage:** Handled in `dsa_engine.php`. Instead of relying purely on SQL `ORDER BY DESC`, the system implements a custom `CandidateNode` and `ResultBST` class. 
   * **Logic:** As candidates are fetched from the unsorted array, they are inserted as Nodes into the BST. The BST compares their total vote counts to determine left/right placement.

## 6. Algorithms Implemented
1. **Binary Tree Insertion Algorithm:** 
   * **Complexity:** $O(\log n)$ average case.
   * **Description:** Dynamically builds the tree structure. When a candidate object is inserted, the algorithm recursively compares its vote count to the current node. If it has fewer votes, it traverses to the left child; if greater or equal, it traverses to the right child.
2. **Reverse In-Order Traversal Algorithm:**
   * **Complexity:** $O(n)$ time complexity.
   * **Description:** To display the results on the leaderboard from 1st place to last place, a standard In-Order traversal (Left, Root, Right) would yield ascending order. The system implements a **Reverse In-Order Traversal (Right, Root, Left)**, appending each visited node to the final ranked array, resulting in a naturally sorted descending list based on vote count.

## 7. Flowchart / System Architecture

```text
[User View: index.php] <------------------------+
       |                                        |
       v (POST Vote Array)                      |
[Database Configuration: db.php] <---> [MySQL Database]
       ^                                        |
       | (POST Register/Reset)                  v (Fetch Unsorted Data Array)
[Admin Dashboard: admin.php]                    |
       |                                        |
       +---> [DSA Engine: dsa_engine.php] <-----+
                   |
                   +-> 1. Iterate Array & execute BST Insert Algorithm()
                   +-> 2. Execute Reverse In-Order Traversal() on Tree
                   +-> 3. Return Sorted Data Array to Admin View
