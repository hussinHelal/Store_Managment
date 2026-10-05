
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('app.name')); ?> <?php echo $__env->yieldContent('title'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
           (() => {
               const savedTheme = localStorage.getItem('theme');
               const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
               const theme = savedTheme || (prefersDark ? 'dark' : 'light');
               document.documentElement.setAttribute('data-bs-theme', theme);
           })();
       </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>


<?php /**PATH C:\Users\hussin\Desktop\Store_Managment\resources\views\components\head.blade.php ENDPATH**/ ?>