<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title><?php echo $page_title ?? 'TechFit'; ?></title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <?php if (isset($page_css)): ?>
    <link rel="stylesheet" href="css/<?php echo $page_css; ?>">
  <?php endif; ?>
</head>
<body class="bg-gray-900 text-white min-h-screen">