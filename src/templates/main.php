<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $this->getTitle(); ?></title>

    <?php foreach ($this->getCssFiles() as $cssFile): ?>
        <link rel="stylesheet" href="/assets/css/<?= $cssFile ?>">
    <?php endforeach; ?>
</head>
<body>
    <header>
        <h1>Projet GROLOTO</h1>
        <nav>
            <a href="index.php?action=home">Accueil</a>
            <a href="index.php?action=benevoles">Bénévoles</a>
            <a href="index.php?action=mecenes">Mécènes</a>
            <a href="index.php?action=stocks">Stocks</a>
            <a href="index.php?action=com">Communication</a>
            <a href="index.php?action=public">Public</a>
        </nav>
    </header>

    <main>
        <?= $this->getContent(); ?>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> - GROLOTO</p>
    </footer>
</body>
</html>
