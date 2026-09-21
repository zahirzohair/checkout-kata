<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { ScannedLine, Sku } from '@/types/checkout';

const props = defineProps<{
    skus: Sku[];
    scanned: ScannedLine[];
    totalCents: number;
}>();

const scanningSku = ref<string | null>(null);
const scanError = ref<string | null>(null);

const formattedTotal = computed(
    () => `$${(props.totalCents / 100).toFixed(2)}`,
);

function scan(sku: string): void {
    scanningSku.value = sku;
    scanError.value = null;

    router.post(
        route('checkout.scan'),
        { sku },
        {
            preserveScroll: true,
            preserveState: true,
            onError: (errors) => {
                scanError.value = errors.sku ?? 'Could not scan that item.';
            },
            onFinish: () => {
                scanningSku.value = null;
            },
        },
    );
}

function reset(): void {
    router.post(route('checkout.reset'), {}, { preserveScroll: true });
}
</script>

<template>

    <div class="mx-auto flex min-h-screen max-w-2xl flex-col gap-6 p-6">
        <Head title="Checkout" />
        <header>
            <h1 class="text-2xl font-semibold text-gray-800">
                Supermarket Checkout
            </h1>
            <p class="text-sm text-gray-600">
                Scan items to build up the basket total.
            </p>
        </header>

        <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-medium text-gray-900">
                Scan an item
            </h2>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <button
                    v-for="item in skus"
                    :key="item.sku"
                    type="button"
                    :disabled="scanningSku === item.sku"
                    class="rounded-md border border-transparent bg-gray-800 px-4 py-3 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="scan(item.sku)"
                >
                    {{ item.sku }}
                    <span v-if="item.label" class="ml-1 text-xs opacity-75">{{
                        item.label
                    }}</span>
                </button>
            </div>

            <p v-if="scanError" class="mt-3 text-sm text-red-600">
                {{ scanError }}
            </p>
        </div>

        <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-medium text-gray-900">Basket</h2>

            <p v-if="scanned.length === 0" class="text-sm text-gray-600">
                Nothing scanned yet.
            </p>
            <ul v-else class="divide-y divide-gray-200">
                <li
                    v-for="line in scanned"
                    :key="line.sku"
                    class="flex items-center justify-between py-2"
                >
                    <span class="font-medium text-gray-900">{{
                        line.sku
                    }}</span>
                    <span
                        class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700"
                        >x{{ line.quantity }}</span
                    >
                </li>
            </ul>

            <div
                class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4"
            >
                <span class="text-lg font-medium text-gray-900">Total</span>
                <span class="text-3xl font-bold text-gray-900">{{
                    formattedTotal
                }}</span>
            </div>

            <button
                type="button"
                :disabled="scanned.length === 0"
                class="mt-4 w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                @click="reset"
            >
                Reset
            </button>
        </div>
    </div>
</template>
