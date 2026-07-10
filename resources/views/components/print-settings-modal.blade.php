@props([
    'title' => 'Print Settings',
    'receipts' => collect(),
])

<div
    x-data="printSettings({
        receipts: @js($receipts)
    })"
    x-show="showModal"
    x-on:open-print-settings.window="showModal = true; receipts = $event.detail.receipts; await $nextTick();"
    x-on:keydown.escape.window="showModal = false"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 transition-opacity"
    x-cloak
>
    <div @click.away="showModal = false" class="relative w-full max-w-4xl max-h-[90vh] bg-white rounded-2xl shadow-xl flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">{{ $title }}</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                <x-app-icon name="close" class="w-6 h-6" />
            </button>
        </div>

        <div class="flex-grow p-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Settings Panel -->
                <div class="md:col-span-1 space-y-4">
                    <div>
                        <label for="itemsPerPage" class="block text-sm font-bold text-slate-700">Items per Page</label>
                            <div class="flex gap-2">
                                <select id="itemsPerPage" x-model.number="itemsPerPage" class="mt-1 block w-1/2 theme-input">
                                    <option value="1">1 per page</option>
                                    <option value="2">2 per page</option>
                                    <option value="4">4 per page</option>
                                    <option value="6">6 per page</option>
                                    <option value="8">8 per page</option>
                                    <option value="9">9 per page</option>
                                </select>
                                <input type="number" min="1" id="itemsPerPageNumber" x-model.number="itemsPerPage" class="mt-1 block w-1/2 theme-input" placeholder="Custom" />
                            </div>
                            <div class="text-xs text-slate-400 mt-1">Set how many copies/items should appear on each A4 page.</div>
                    </div>

                    <div class="hidden">
                        <label for="paperSize" class="block text-sm font-bold text-slate-700">Paper Size</label>
                        <select id="paperSize" x-model="paperSize" class="mt-1 block w-full theme-input">
                            <option value="A4">A4</option>
                        </select>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-sm text-slate-600">
                            Showing a preview for <strong x-text="receipts.length"></strong> receipt(s).
                        </p>
                    </div>
                    <div class="mt-3">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" x-model="duplicateToFill" class="rounded border-slate-300" />
                            <span class="text-sm text-slate-600">Duplicate selection to fill each A4 page</span>
                        </label>
                        <p class="text-xs text-slate-400 mt-1">When enabled, the selected receipt(s) will be repeated on the A4 page to reach the chosen items-per-page count.</p>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="md:col-span-2 bg-slate-100 border border-slate-200 rounded-lg p-4 min-h-[400px]">
                    <div id="print-preview-container" x-ref="previewContainer" class="bg-white shadow-sm">
                        <div
                            id="print-preview"
                            :class="`print-grid print-grid-${itemsPerPage}`"
                        >
                            <template x-for="(receipt, index) in previewReceipts" :key="index">
                                <div class="print-receipt-item" x-html="receipt.html"></div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end p-6 border-t border-slate-200 bg-slate-50 rounded-b-2xl">
            <div class="flex gap-3">
                <button @click="showModal = false" type="button" class="btn btn-secondary">Cancel</button>
                <button @click.prevent="printAction()" type="button" class="btn btn-primary">Print</button>
            </div>
        </div>
    </div>
</div>
