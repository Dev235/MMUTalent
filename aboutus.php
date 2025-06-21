<?php
$page_title = "About Us";
require 'header.php';
require 'navbar.php';

// Array of team members' information
$members = [
    [
        'name'       => 'Hong Yoong Shem',
        'student_id' => '1201103427',
        'section'    => 'TC1L',
        'photo'      => 'images/Shem.jpeg', // Make sure this path is correct
        'email'      => '1201103427@student.mmu.edu.my',
    ],
    [
        'name'       => 'Kalla Deveshwara Rao A/L Rama Rao',
        'student_id' => '1211103169',
        'section'    => 'TC1L',
        'photo'      => 'images/dev.jpeg', // Replace with the correct path
        'email'      => '1211103169@student.mmu.edu.my',
    ],
    [
        'name'       => 'Darwin A/L Radhakrishnan',
        'student_id' => '1211104430',
        'section'    => 'TC1L',
        'photo'      => 'images/darwin.jpeg', // Replace with the correct path
        'email'      => '1211104430@student.mmu.edu.my',
    ],
];
?>

<style>
    /* These styles are specific to the About Us page and are designed to work with your main stylesheet. */
    .about-us-wrapper {
        max-width: 1100px;
        margin: 30px auto; /* Use consistent margin */
        padding: 0 20px;
    }
    .about-us-card {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px_10px rgba(0,0,0,.08);
    }
    .about-us-card .team-title {
        color: var(--color-title);
        border-bottom: 2px solid var(--color-surface);
        padding-bottom: 15px;
        margin-bottom: 25px;
        margin-top: 0;
    }
    .about-us-table {
        width: 100%;
        border-collapse: collapse;
    }
    .about-us-table thead {
        background-color: #f8f9fa; /* A light, neutral header color */
    }
    .about-us-table th,
    .about-us-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #dee2e6; /* Adds subtle lines between rows */
        vertical-align: middle;
    }
    .about-us-table tbody tr:nth-child(odd) {
        background-color: #fdfdff;
    }
    .about-us-table tbody tr:hover {
        background-color: #f1f5f9;
        transition: background .2s;
    }

    .about-us-table figure {
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .about-us-table figure img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid var(--color-primary);
        transition: transform .25s;
    }
    .about-us-table figure img:hover {
        transform: scale(1.05);
    }
    .about-us-table figcaption {
        font-size: .85rem;
        color: #555;
        margin-top: .5rem;
        display: none; /* Hidden because name is in a separate column */
    }
    .about-us-table .email-link {
        color: var(--color-primary);
        font-weight: 600;
        text-decoration: none;
    }
    .about-us-table .email-link:hover {
        text-decoration: underline;
    }

    /* Responsive adjustments for smaller screens */
    @media (max-width: 768px) {
        .about-us-table thead {
            display: none;
        }
        .about-us-table, .about-us-table tbody, .about-us-table tr, .about-us-table td {
            display: block;
            width: 100%;
        }
        .about-us-table tr {
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
        }
        .about-us-table td {
            padding: 0.75rem 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        .about-us-table td:last-child {
            border-bottom: none;
        }
        .about-us-table td[data-label="Photo"] {
            justify-content: center;
            padding-bottom: 1.5rem;
        }
        .about-us-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #555;
            padding-right: 1rem;
        }
        .about-us-table td[data-label="Photo"]::before {
           display: none; /* Hide the "Photo:" label on mobile */
        }
    }
</style>

<div id="main-content">
    <div class="title-container">
        <h1>About Us</h1>
    </div>
    
    <main class="about-us-wrapper">
        <section class="about-us-card">
            <h2 class="team-title">Our Project Team</h2>

            <table class="about-us-table">
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Name</th>
                        <th scope="col">Student&nbsp;ID</th>
                        <th scope="col">Section</th>
                        <th scope="col">Contact</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td data-label="Photo">
                            <figure>
                                <img src="<?= htmlspecialchars($m['photo']) ?>"
                                     alt="Portrait of <?= htmlspecialchars($m['name']) ?>">
                                <figcaption><?= htmlspecialchars($m['name']) ?></figcaption>
                            </figure>
                        </td>
                        <td data-label="Name"><?= htmlspecialchars($m['name']) ?></td>
                        <td data-label="Student ID"><?= htmlspecialchars($m['student_id']) ?></td>
                        <td data-label="Section"><?= htmlspecialchars($m['section']) ?></td>
                        <td data-label="Contact">
                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>"
                               class="email-link"><?= htmlspecialchars($m['email']) ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php require 'footer.php'; ?>
