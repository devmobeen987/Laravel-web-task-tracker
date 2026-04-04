<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const form = useForm({
    title: '',
    work_date: new Date().toISOString().slice(0, 10),
    articles: [],
});

const articlesRaw = ref('');
const urlRows = ref([]);
const checkingUrls = ref(false);
const checkError = ref('');
const clientError = ref('');

function extractUrls(text) {
    const found = new Set();
    const raw = text || '';
    const re = /https?:\/\/[^\s<>"'[\]()]+/gi;
    let m;
    while ((m = re.exec(raw)) !== null) {
        let u = m[0].replace(/[,;.)]+$/, '');
        found.add(u);
    }
    raw.split(/[\n,;]+/).forEach((line) => {
        const t = line.trim();
        if (/^https?:\/\//i.test(t)) {
            found.add(t.replace(/[,;.)]+$/, ''));
        }
    });
    return [...found];
}

async function onArticlesBlur() {
    checkError.value = '';
    clientError.value = '';

    const urls = extractUrls(articlesRaw.value);
    if (urls.length === 0) {
        urlRows.value = [];
        return;
    }

    const prevByUrl = new Map(urlRows.value.map((r) => [r.url, r.userType]));

    try {
        checkingUrls.value = true;
        const { data } = await axios.post(route('tasks.check-urls'), { urls });
        const items = data.items || [];
        urlRows.value = items.map((item) => ({
            url: item.url,
            auto_detected_type: item.auto_detected_type,
            userType: prevByUrl.get(item.url) ?? null,
        }));
    } catch {
        checkError.value = 'Could not check URLs. Try again.';
    } finally {
        checkingUrls.value = false;
    }
}

function setRowType(row, type) {
    row.userType = type;
}

function submit() {
    clientError.value = '';
    const articles = urlRows.value
        .filter((r) => r.userType)
        .map((r) => ({ article_url: r.url, type: r.userType }));

    if (articles.length === 0) {
        clientError.value =
            'Paste at least one valid URL, leave the field, then choose New or Old for each link.';
        return;
    }

    form.articles = articles;
    form.post(route('tasks.store'));
}
</script>

<template>
    <Head title="Log Daily Update" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800"
                >
                    Log Daily Update
                </h2>
                <Link
                    :href="route('tasks.index')"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700"
                >
                    Back to list
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden border border-gray-100 bg-white shadow-sm sm:rounded-lg"
                >
                    <form class="space-y-5 p-6" @submit.prevent="submit">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Title
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <p
                                v-if="form.errors.title"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Article URLs
                            </label>
                            <p class="mt-1 text-xs text-gray-500">
                                Paste one or more links (new lines or commas).
                                When you leave this field, each URL gets a
                                system hint and New / Old buttons.
                            </p>
                            <textarea
                                v-model="articlesRaw"
                                rows="6"
                                class="mt-2 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="https://yoursite.com/2024/03/my-post/&#10;https://yoursite.com/2025/01/my-post/"
                                @blur="onArticlesBlur"
                            />
                            <p
                                v-if="checkError"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ checkError }}
                            </p>
                            <p
                                v-if="checkingUrls"
                                class="mt-1 text-xs text-gray-500"
                            >
                                Checking URLs…
                            </p>
                        </div>

                        <div
                            v-if="urlRows.length"
                            class="rounded-lg border border-gray-100 bg-gray-50 p-4"
                        >
                            <h3
                                class="text-sm font-semibold text-gray-800"
                            >
                                Confirm each link
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                The system compares URLs after removing
                                typical year/month/day path segments so small
                                URL changes still count as the same article.
                            </p>
                            <ul class="mt-3 space-y-3">
                                <li
                                    v-for="(row, idx) in urlRows"
                                    :key="idx"
                                    class="flex flex-col gap-2 rounded-md border border-gray-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="min-w-0 flex-1">
                                        <a
                                            :href="row.url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="break-all text-sm text-indigo-600 hover:underline"
                                        >
                                            {{ row.url }}
                                        </a>
                                        <p class="mt-1 text-xs text-gray-500">
                                            System hint:
                                            <span
                                                :class="
                                                    row.auto_detected_type ===
                                                    'new'
                                                        ? 'font-medium text-emerald-700'
                                                        : 'font-medium text-amber-700'
                                                "
                                            >
                                                {{
                                                    row.auto_detected_type ===
                                                    'new'
                                                        ? 'Likely new'
                                                        : 'Seen before (likely old / refurbished)'
                                                }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-semibold shadow-sm"
                                            :class="
                                                row.userType === 'new'
                                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-800'
                                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50'
                                            "
                                            @click="setRowType(row, 'new')"
                                        >
                                            New
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-semibold shadow-sm"
                                            :class="
                                                row.userType === 'refurbished'
                                                    ? 'border-amber-600 bg-amber-50 text-amber-800'
                                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50'
                                            "
                                            @click="
                                                setRowType(row, 'refurbished')
                                            "
                                        >
                                            Old
                                        </button>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <h3 class="text-sm font-semibold text-gray-800">
                                Work date
                            </h3>
                            <p class="mt-1 text-xs text-gray-500">
                                Times for this date come from your dashboard
                                daily timer when you save. Use the dashboard to
                                start and end your shift and breaks.
                            </p>
                            <input
                                v-model="form.work_date"
                                type="date"
                                class="mt-2 block w-full max-w-xs rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <p
                                v-if="form.errors.work_date"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.work_date }}
                            </p>
                        </div>

                        <p
                            v-if="clientError"
                            class="text-sm text-red-600"
                        >
                            {{ clientError }}
                        </p>
                        <p
                            v-if="form.errors.articles"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.articles }}
                        </p>

                        <div class="flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60"
                            >
                                Save daily update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
