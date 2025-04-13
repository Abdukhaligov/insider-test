<template>
    <div id="page-container"
        class="flex flex-col mx-auto w-full min-h-screen min-w-[320px] bg-gray-100">
        <main id="page-content" class="flex flex-auto flex-col max-w-full">
            <div class="container xl:max-w-7xl mx-auto p-4 lg:p-8">
                <router-view></router-view>
            </div>
        </main>
    </div>
</template>

<script >
export default {
    name: 'App'
}
</script>
