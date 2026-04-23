<template>
    <div class="app-shell">
        <header class="topbar">
            <div class="topbar-inner">
                <div class="brand">
                    <div class="brand-badge">RCA</div>
                    <div>
                        <h1 class="brand-title">Comparator RCA</h1>
                        <p class="brand-subtitle">Ofertare rapidă pentru persoane fizice și juridice</p>
                    </div>
                </div>

                <nav class="nav">
                    <RouterLink to="/" class="nav-link">Ofertare</RouterLink>
                    <RouterLink to="/history" class="nav-link">Istoric</RouterLink>
                </nav>
            </div>
        </header>

        <main class="page">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
</script>
