<template>
    <div class="app-shell">
        <header class="topbar">
            <div>
                <h1>Generator oferte RCA</h1>
                <p>Demo ofertare asigurări</p>
            </div>

            <nav class="nav">
                <RouterLink to="/" class="nav-link">Calculare nouă</RouterLink>
                <RouterLink to="/history" class="nav-link">Istoric cereri</RouterLink>
            </nav>
        </header>

        <main class="page-container">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
</script>
