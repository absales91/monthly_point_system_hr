<x-app-layout>
    <h2>Create Product</h2>

<form method="POST" action="/admin/products">
@csrf

<label>Category</label><br>
<select name="category_id" required>
@foreach($categories as $cat)
<option value="{{ $cat->id }}">{{ $cat->name }}</option>
@endforeach
</select><br><br>

<label>Product Name</label><br>
<input type="text" name="name" required><br><br>

<label>Price</label><br>
<input type="number" name="price" step="0.01" required><br><br>

<label>GST %</label><br>
<input type="number" name="gst_percent" required><br><br>

<label>Unit</label><br>
<input type="text" name="unit" placeholder="sqft / pcs / nos" required><br><br>

<button type="submit">Save</button>
</form>

</x-app-layout>