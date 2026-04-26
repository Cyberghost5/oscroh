<div
    x-data="{ open: true }"
    x-show="open"
    class="alert-info alert relative flex justify-between items-start gap-4"
>
    <div class="flex gap-3 pr-4">
        <div class="d-flex v-align-center mb-1">
            <x-heroicon-o-information-circle class="w-5 h-5 icon text-yellow-600 mt-1" />
            <p class="font-semibold text-yellow-800 dark:text-yellow-300 ml-1 mb-0">
                Few general notes about generating themes:
            </p>
        </div>

        <div class="space-y-2">

            <ul class="list-disc list-inside text-sm space-y-1 mb-0">
                <li>The theme is generated locally on this server when you save.</li>
                <li>The selected primary and gradient colors are compiled into CSS files under <code>public/css/theme</code>.</li>
                <li>If <code>Include RTL version</code> is enabled, RTL variants are generated as well.</li>
                <li>When updating your site, back up and restore your <code>public/css/theme</code> folder to keep custom colors.</li>
            </ul>
        </div>
    </div>

    <button
        type="button"
        @click="open = false"
        class="text-blue-500 hover:text-blue-700 dark:text-blue-300 text-lg leading-none"
        aria-label="Dismiss"
    >
        &times;
    </button>
</div>
