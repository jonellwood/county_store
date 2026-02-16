<?php
/*
Created: 2024/06/24
Last modified: 2026/02/16 09:13:20
Organization: Berkeley County IT Department
Purpose: Display the application changelog with consistent styling
*/
include "./components/viewHead.php";
?>

<div class="container">
    <div class="changelog-wrapper">
        <?php
        $changelogHtml = file_get_contents('./changelog.html');
        echo $changelogHtml;
        ?>
    </div>
</div>

<?php include "footer.php" ?>

<style>
    .changelog-wrapper {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        background: var(--bg-surface, #ffffff);
        border-radius: var(--radius-lg, 12px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .changelog-wrapper h1 {
        color: var(--color-primary, #2563eb);
        font-size: 2.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid var(--color-primary, #2563eb);
    }

    .changelog-wrapper h2 {
        color: var(--text-primary, #1f2937);
        font-size: 1.75rem;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, var(--color-primary, #2563eb) 0%, #7c3aed 100%);
        color: white;
        border-radius: var(--radius-md, 8px);
    }

    .changelog-wrapper h3 {
        color: var(--text-primary, #374151);
        font-size: 1.25rem;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }

    .changelog-wrapper ul {
        list-style: none;
        padding-left: 0;
        margin: 1rem 0;
    }

    .changelog-wrapper ul li {
        padding: 0.5rem 0 0.5rem 1.5rem;
        margin: 0.25rem 0;
        position: relative;
        line-height: 1.6;
        color: var(--text-secondary, #4b5563);
    }

    .changelog-wrapper ul li:before {
        content: "•";
        position: absolute;
        left: 0.5rem;
        color: var(--color-primary, #2563eb);
        font-weight: bold;
        font-size: 1.2em;
    }

    .changelog-wrapper code {
        background: rgba(37, 99, 235, 0.1);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
        color: var(--color-primary, #2563eb);
    }

    .changelog-wrapper mark {
        background: #fef3c7;
        padding: 0.1rem 0.3rem;
        border-radius: 3px;
    }

    .changelog-wrapper a {
        color: var(--color-primary, #2563eb);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .changelog-wrapper a:hover {
        color: var(--color-primary-dark, #1d4ed8);
        text-decoration: underline;
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .changelog-wrapper {
            background: var(--bg-surface, #1f2937);
        }

        .changelog-wrapper h1 {
            color: #60a5fa;
            border-bottom-color: #60a5fa;
        }

        .changelog-wrapper h2 {
            color: white;
        }

        .changelog-wrapper h3 {
            color: #e5e7eb;
        }

        .changelog-wrapper ul li {
            color: #d1d5db;
        }

        .changelog-wrapper code {
            background: rgba(96, 165, 250, 0.2);
            color: #93c5fd;
        }
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .changelog-wrapper {
            padding: 1rem;
            margin: 1rem;
        }

        .changelog-wrapper h1 {
            font-size: 1.75rem;
        }

        .changelog-wrapper h2 {
            font-size: 1.25rem;
        }

        .changelog-wrapper h3 {
            font-size: 1.1rem;
        }
    }
</style>

</body>

</html>