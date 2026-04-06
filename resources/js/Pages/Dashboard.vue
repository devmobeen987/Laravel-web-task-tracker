<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    stats: Object,
    todayLog: {
        type: Object,
        default: null,
    },
    flashError: {
        type: String,
        default: null,
    },
});

const tick = ref(Date.now());
let intervalId = null;

const onShift = computed(
    () => props.todayLog?.clock_in_at && !props.todayLog?.clock_out_at,
);

watch(
    onShift,
    (active) => {
        if (active) {
            if (!intervalId) {
                intervalId = setInterval(() => {
                    tick.value = Date.now();
                }, 1000);
            }
        } else if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    },
    { immediate: true },
);

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

function parseTs(iso) {
    if (!iso) {
        return null;
    }
    return new Date(iso).getTime();
}

const elapsedLabel = computed(() => {
    const log = props.todayLog;
    if (!log?.clock_in_at) {
        return '';
    }
    const start = parseTs(log.clock_in_at);
    const end = log.clock_out_at ? parseTs(log.clock_out_at) : tick.value;
    const sec = Math.max(0, Math.floor((end - start) / 1000));
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

const onBreak = computed(
    () => props.todayLog?.break_start_at && !props.todayLog?.break_end_at,
);

function postDailyTime(name) {
    router.post(route(name), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <p
                    v-if="flashError"
                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                >
                    {{ flashError }}
                </p>

                <div
                    class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <h3
                        class="text-base font-bold uppercase tracking-wide text-gray-700"
                    >
                        Today's time
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Start and end your work day here. Break is optional.
                        When you log a daily update, times for that work date
                        are copied from this record.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            :disabled="!!todayLog?.clock_in_at"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="postDailyTime('dashboard.daily-time.start-shift')"
                        >
                            Start day
                        </button>
                        <button
                            type="button"
                            :disabled="
                                !todayLog?.clock_in_at
                                    || !!todayLog?.clock_out_at
                                    || onBreak
                            "
                            class="rounded-md bg-gray-800 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="postDailyTime('dashboard.daily-time.end-shift')"
                        >
                            End day
                        </button>
                        <button
                            type="button"
                            :disabled="
                                !onShift
                                    || onBreak
                                    || !!todayLog?.break_start_at
                            "
                            class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="postDailyTime('dashboard.daily-time.start-break')"
                        >
                            Start break
                        </button>
                        <button
                            type="button"
                            :disabled="!onBreak"
                            class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="postDailyTime('dashboard.daily-time.end-break')"
                        >
                            End break
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            @click="postDailyTime('dashboard.daily-time.reset')"
                        >
                            Reset today
                        </button>
                    </div>

                    <div
                        v-if="todayLog?.clock_in_at && elapsedLabel"
                        class="mt-5 rounded-xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-indigo-600"
                        >
                            {{
                                todayLog.clock_out_at
                                    ? 'Total time today'
                                    : 'Running timer'
                            }}
                        </p>
                        <p
                            class="mt-2 font-mono text-3xl font-bold tabular-nums tracking-tight text-indigo-950 sm:text-4xl"
                        >
                            {{ elapsedLabel }}
                        </p>
                    </div>

                    <div
                        v-if="todayLog?.clock_in_at"
                        class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-700"
                    >
                        <p>
                            <span class="font-semibold text-gray-800">Clock in:</span>
                            <span class="ml-1 font-mono font-semibold text-gray-900">{{
                                new Date(todayLog.clock_in_at).toLocaleString()
                            }}</span>
                        </p>
                        <p v-if="todayLog.clock_out_at">
                            <span class="font-semibold text-gray-800">Clock out:</span>
                            <span class="ml-1 font-mono font-semibold text-gray-900">{{
                                new Date(todayLog.clock_out_at).toLocaleString()
                            }}</span>
                        </p>
                        <p v-if="todayLog.break_start_at">
                            <span class="font-semibold text-gray-800">Break start:</span>
                            <span class="ml-1 font-mono font-semibold text-gray-900">{{
                                new Date(todayLog.break_start_at).toLocaleString()
                            }}</span>
                        </p>
                        <p v-if="todayLog.break_end_at">
                            <span class="font-semibold text-gray-800">Break end:</span>
                            <span class="ml-1 font-mono font-semibold text-gray-900">{{
                                new Date(todayLog.break_end_at).toLocaleString()
                            }}</span>
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            This month – New
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-emerald-700">
                            {{ stats.currentUser.new }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            New articles you created this month.
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            This month – Refurbished
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-amber-700">
                            {{ stats.currentUser.refurbished }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Old articles you updated this month.
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Total tasks this month
                        </p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">
                            {{ stats.currentUser.new + stats.currentUser.refurbished }}
                        </p>
                        <div class="mt-3">
                            <Link
                                :href="route('tasks.create')"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                Log daily update
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
