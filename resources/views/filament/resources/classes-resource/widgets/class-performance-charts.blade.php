<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-filament::card>
            <x-filament::chart :chart="$charts['bar_chart']" style="height: 300px;" />
        </x-filament::card>
    </div>
    <div>
        <x-filament::card>
            <x-filament::chart :chart="$charts['pie_chart']" style="height: 300px;" />
        </x-filament::card>
    </div>
</div>