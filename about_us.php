<?php


$page_title = "About Us";
require 'header.php';  
require 'navbar.php';   


$members = [
    [
        'name'       => 'Hong Yoong Shem',
        'student_id' => '1201103427',
        'section'    => 'TC1L',
        'photo'      => 'images/Shem.jpeg',
        'email'      => '1201103427@student.mmu.edu.my',
    ],
    [
        'name'       => 'Kalla Deveshwara Rao A/L Rama Rao',
        'student_id' => '1211103169',
        'section'    => 'TC1L',
        'photo'      => 'images/xyz.jpg',
        'email'      => '1211103169@student.mmu.edu.my',
    ],
    [
        'name'       => 'Darwin A/L Radhakrishnan',
        'student_id' => '1211104430',
        'section'    => 'TC1L',
        'photo'      => 'images/xyz.jpg',
        'email'      => '1211104430@student.mmu.edu.my',
    ],
];
?>

<style>
    :root {
        --bg: #F4F7F9;
        --card: #FFF;
        --accent: #0077B6;
        --text: #222;
        --text-light: #555;
    }
    body { background: var(--bg); }

    .member-wrapper {
        max-width: 960px;
        margin: 2.5rem auto;
        padding: 0 1rem;
    }
    .member-card {
        background: var(--card);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,.08);
    }
    table { width: 100%; border-collapse: collapse; }
    thead { background: #E3F2FD; }
    th, td { padding: .75rem 1rem; text-align: left; }
    tbody tr:nth-child(odd) { background: #FAFCFF; }
    tbody tr:hover { background: #F1F9FF; transition: background .2s; }

    figure { margin: 0; display: flex; flex-direction: column; align-items: center; }
    figure img {
        width: 120px; height: 120px; object-fit: cover;
        border-radius: 50%; border: 3px solid var(--accent);
        transition: transform .25s;
    }
    figure img:hover { transform: scale(1.05); }
    figcaption { font-size: .85rem; color: var(--text-light); margin-top: .4rem; }

    .email-link { color: var(--accent); font-weight: 600; text-decoration: none; }
    .email-link:hover { text-decoration: underline; }

    /* Responsive table for ≤600 px */
    @media (max-width: 600px) {
        table, thead, tbody, th, td, tr { display: block; }
        thead { display: none; }
        tr { margin-bottom: 1.2rem; background: #FAFCFF; padding: 1rem; border-radius: 8px; }
        td { padding: .4rem 0; }
        td::before {
            content: attr(data-label);
            font-weight: 600; color: var(--text-light); display: block; margin-bottom: .2rem;
        }
        figure img { width: 100px; height: 100px; }
    }
</style>

<main class="member-wrapper">
    <section class="member-card" aria-labelledby="members-title">
        <h2 id="members-title" style="margin-top:0;">Our Project Team</h2>

        <table>
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

<?php require 'footer.php'; ?>
