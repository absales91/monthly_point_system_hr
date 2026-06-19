<div>
    
    <button 
        wire:click="$set('showModal', true)"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg">
        Generate Salary Slip
    </button>
@if($showModal)
<div class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center">
<div class="bg-white rounded-xl p-6 w-96 shadow-lg">

<div class="flex justify-between items-center mb-4">
<h3 class="text-lg font-semibold">Generate Salary Slip</h3>
<button wire:click="$set('showModal', false)">✕</button>
</div>

<div class="space-y-4">

<div>
<label class="block text-sm font-medium">Year</label>
<select wire:model="year" class="w-full border rounded-lg p-2">
<option value="">Choose Year</option>
@foreach($years as $y)
<option value="{{ $y }}">{{ $y }}</option>
@endforeach
</select>
@error('year') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
</div>

<div>
<label class="block text-sm font-medium">Month</label>
<select wire:model="month" class="w-full border rounded-lg p-2">
<option value="">Choose Month</option>
@foreach($months as $m)
<option value="{{ $m }}">{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
@endforeach
</select>
@error('month') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
</div>

<div>
<label class="block text-sm font-medium mb-2">Salary Slip Type</label>
<div class="flex gap-4">
<label>
<input type="radio" wire:model="slipType" value="half">
Half Page
</label>
<label>
<input type="radio" wire:model="slipType" value="full">
Full Page
</label>
</div>
</div>

<button 
wire:click="generate"
class="w-full bg-gray-300 hover:bg-blue-600 hover:text-white py-2 rounded-lg">
Generate
</button>

</div>
</div>
</div>
@endif
</div>
