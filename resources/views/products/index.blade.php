<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product CRUD</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .page-shell {
            min-height: 100vh;
        }

        .page-inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 3rem 1.25rem;
        }

        .hero,
        .panel,
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 32px;
            box-shadow: 0 20px 70px rgba(15, 23, 42, .08);
        }

        .hero {
            padding: 2.5rem;
        }

        .hero h1 {
            margin: 0 0 .75rem;
            font-size: 2.75rem;
            line-height: 1.05;
        }

        .hero p {
            margin: 0;
            color: #475569;
            font-size: 1rem;
            line-height: 1.8;
        }

        .grid-lg {
            display: grid;
            gap: 1.75rem;
        }

        @media (min-width:1024px) {
            .grid-lg {
                grid-template-columns: 1.35fr .65fr;
            }
        }

        .panel {
            padding: 2rem;
        }

        .section-title {
            margin: 0 0 1rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }

        .product-list {
            display: grid;
            gap: 1rem;
        }

        .product-item {
            padding: 1.2rem 1.25rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
        }

        .product-item h3 {
            margin: 0 0 .35rem;
            font-size: 1.15rem;
        }

        .product-item p {
            margin: 0;
            color: #64748b;
            line-height: 1.7;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            align-items: center;
            margin-top: 1rem;
        }

        .badge {
            display: inline-flex;
            padding: .45rem .85rem;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #334155;
            font-size: .85rem;
            font-weight: 600;
        }

        .button,
        .button-secondary {
            border: none;
            cursor: pointer;
            border-radius: 18px;
            font-weight: 700;
            transition: background .2s ease, transform .2s ease;
        }

        .button {
            background: #4338ca;
            color: #ffffff;
            padding: .85rem 1.2rem;
        }

        .button:hover {
            background: #3730a3;
        }

        .button-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: .85rem 1.15rem;
        }

        .button-secondary:hover {
            background: #f8fafc;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .form-group {
            margin-bottom: 1.15rem;
        }

        .form-label {
            display: block;
            margin-bottom: .5rem;
            font-size: .95rem;
            font-weight: 600;
            color: #334155;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 18px;
            padding: .95rem 1rem;
            font-size: 1rem;
            color: #0f172a;
            background: #ffffff;
            box-sizing: border-box;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .12);
        }

        .form-textarea {
            min-height: 140px;
            resize: vertical;
        }

        .success-banner {
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: #065f46;
            border-radius: 24px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="page-shell">
        <div class="page-inner">
            <div class="hero">

                <h1>Product CRUD</h1>

                <p>
                    Manage your products and maintain your project dependencies.
                </p>

                <div style="margin-top: 1.5rem;">

                    <a
                        href="{{ route('dependencies.index') }}"
                        class="button">
                        React Dependency Manager
                    </a>

                </div>

            </div>

            @if (session('success'))
            <div class="success-banner">
                {{ session('success') }}
            </div>
            @endif

            <div class="grid-lg">
                <div class="panel">
                    <h2 class="section-title">Product List</h2>
                    <div class="product-list">
                        @forelse ($products as $productItem)
                        <div class="product-item">
                            <div>
                                <h3>{{ $productItem->name }}</h3>
                                <p>{{ $productItem->description ?? 'No description provided.' }}</p>
                            </div>

                            <div class="meta-row">
                                <span class="badge">${{ number_format($productItem->price, 2) }}</span>
                                <a href="{{ route('products.edit', $productItem) }}" class="button-secondary">Edit</a>
                                <form action="{{ route('products.destroy', $productItem) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-secondary" onclick="return confirm('Delete this product?')">Delete</button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="product-item" style="background:#ffffff; border-style:dashed; color:#64748b; text-align:center;">
                            No products added yet.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="panel">
                    <h2 class="section-title">{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h2>

                    <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" method="POST" class="mt-6">
                        @csrf
                        @if ($product->exists)
                        @method('PUT')
                        @endif

                        <div class="form-group">
                            <label class="form-label" for="name">Name</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" class="form-input" placeholder="Product name">
                            @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" class="form-textarea" placeholder="Product description">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="price">Price</label>
                            <input id="price" type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" class="form-input" placeholder="Product price">
                            @error('price')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="actions">
                            <button type="submit" class="button">{{ $product->exists ? 'Update Product' : 'Create Product' }}</button>
                            @if ($product->exists)
                            <a href="{{ route('products.index') }}" class="button-secondary">Cancel</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>