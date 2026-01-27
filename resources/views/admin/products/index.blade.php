<x-app-layout>
    <h2>Products</h2>
<a href="/admin/products/create">+ Add Product</a>

<table border="1" cellpadding="10">
<tr>
    <th>Name</th>
    <th>Category</th>
    <th>Price</th>
    <th>GST %</th>
    <th>Unit</th>
</tr>
@foreach($products as $p)
<tr>
    <td>{{ $p->name }}</td>
    <td>{{ $p->category->name }}</td>
    <td>₹{{ $p->price }}</td>
    <td>{{ $p->gst_percent }}%</td>
    <td>{{ $p->unit }}</td>
</tr>
@endforeach
</table>

</x-app-layout>